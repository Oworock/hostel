<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;
use Throwable;

class StudentIdCardNotificationService
{
    public function sendActiveBookingCard(Booking $booking, ?Payment $payment = null): void
    {
        $student = $booking->user;
        if (!$student || !$student->isStudent() || !filled($student->email)) {
            return;
        }

        $idCardService = app(StudentIdCardService::class);
        $activeBooking = $idCardService->resolveActiveBooking($student);
        if (!$activeBooking || (int) $activeBooking->id !== (int) $booking->id) {
            return;
        }

        $alreadySent = collect($student->extra_data['student_id_card_emailed_bookings'] ?? [])
            ->contains(fn ($bookingId) => (int) $bookingId === (int) $booking->id);
        if ($alreadySent) {
            return;
        }

        $svg = $idCardService->buildSvg($student, $booking);
        $imageDataUri = $idCardService->buildPngDataUri($svg, 2);
        $pdfBinary = \PDF::loadView('student.id-card.pdf', [
            'imageDataUri' => $imageDataUri,
            'student' => $student,
            'booking' => $booking,
        ])->output();

        $subject = 'Your student ID card is ready';
        $template = (string) get_setting(
            'student_id_card_email_template',
            'Hello {name}, your booking payment is confirmed and your student ID card is attached.'
        );
        $body = str_replace(
            ['{name}', '{booking_id}', '{check_in_date}', '{check_out_date}', '{payment_reference}'],
            [
                (string) $student->name,
                (string) $booking->id,
                (string) optional($booking->check_in_date)->format('Y-m-d'),
                (string) optional($booking->check_out_date)->format('Y-m-d'),
                (string) ($payment?->transaction_id ?: ''),
            ],
            $template
        );

        try {
            Mail::raw($body, function ($message) use ($student, $subject, $pdfBinary, $svg, $idCardService): void {
                $message->to((string) $student->email)
                    ->subject($subject)
                    ->attachData($pdfBinary, $idCardService->buildDownloadFileName($student, 'pdf'), ['mime' => 'application/pdf'])
                    ->attachData($svg, $idCardService->buildDownloadFileName($student, 'svg'), ['mime' => 'image/svg+xml']);
            });

            $extra = $student->extra_data ?? [];
            $emailed = collect($extra['student_id_card_emailed_bookings'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->push((int) $booking->id)
                ->unique()
                ->values()
                ->all();
            $extra['student_id_card_emailed_bookings'] = $emailed;
            $student->forceFill(['extra_data' => $extra])->save();
        } catch (Throwable $e) {
            // Do not block payment flow when notification sending fails.
        }
    }
}
