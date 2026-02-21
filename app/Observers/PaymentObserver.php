<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\StudentIdCardNotificationService;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        if ($payment->isPaid()) {
            $this->processPaidPayment($payment);
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        if ($payment->isDirty('status') && $payment->isPaid()) {
            $this->processPaidPayment($payment);
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }

    private function processPaidPayment(Payment $payment): void
    {
        $booking = $payment->booking;
        if (!$booking) {
            return;
        }

        if ($booking->isPending() && $booking->isFullyPaid()) {
            $booking->update(['status' => 'approved']);

            if ($booking->bed) {
                $booking->bed->update([
                    'is_occupied' => true,
                    'user_id' => $booking->user_id,
                    'occupied_from' => now(),
                ]);
            }
        }

        app(StudentIdCardNotificationService::class)->sendActiveBookingCard($booking, $payment);
    }
}
