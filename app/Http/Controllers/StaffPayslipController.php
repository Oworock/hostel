<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\SalaryPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class StaffPayslipController extends Controller
{
    public function show(Request $request, SalaryPayment $salaryPayment)
    {
        abort_unless(Addon::isActive('staff-payroll'), 404);
        abort_unless($salaryPayment->status === 'paid', 404);

        $salaryPayment->loadMissing('staffMember');

        return view('public.staff-payslip', [
            'payment' => $salaryPayment,
            'printMode' => $request->boolean('print'),
            'pdfUrl' => URL::temporarySignedRoute('staff.payslips.pdf', now()->addDays(30), ['salaryPayment' => $salaryPayment->id]),
            'imageUrl' => URL::temporarySignedRoute('staff.payslips.image', now()->addDays(30), ['salaryPayment' => $salaryPayment->id]),
            'printUrl' => URL::temporarySignedRoute('staff.payslips.show', now()->addDays(30), ['salaryPayment' => $salaryPayment->id, 'print' => 1]),
        ]);
    }

    public function pdf(SalaryPayment $salaryPayment)
    {
        abort_unless(Addon::isActive('staff-payroll'), 404);
        abort_unless($salaryPayment->status === 'paid', 404);

        $salaryPayment->loadMissing('staffMember');
        $pdf = Pdf::loadView('public.staff-payslip-pdf', ['payment' => $salaryPayment]);

        return $pdf->download('payslip-' . $salaryPayment->id . '.pdf');
    }

    public function image(SalaryPayment $salaryPayment)
    {
        abort_unless(Addon::isActive('staff-payroll'), 404);
        abort_unless($salaryPayment->status === 'paid', 404);
        abort_unless(extension_loaded('gd'), 503, 'GD extension is required for payslip image export.');

        $salaryPayment->loadMissing('staffMember');
        $payload = $this->buildLines($salaryPayment);

        $width = 1100;
        $height = 680;
        $image = imagecreatetruecolor($width, $height);
        imageantialias($image, true);

        $white = imagecolorallocate($image, 255, 255, 255);
        $ink = imagecolorallocate($image, 15, 23, 42);
        $muted = imagecolorallocate($image, 71, 85, 105);
        $accent = imagecolorallocate($image, 37, 99, 235);
        $success = imagecolorallocate($image, 5, 150, 105);
        $border = imagecolorallocate($image, 226, 232, 240);

        imagefilledrectangle($image, 0, 0, $width, $height, $white);
        imagerectangle($image, 30, 30, $width - 30, $height - 30, $border);

        imagestring($image, 5, 55, 55, $this->truncate((string) get_setting('app_name', config('app.name', 'Hostel System')), 72), $ink);
        imagestring($image, 4, 55, 85, 'Salary Payslip', $accent);
        imagestring($image, 5, 760, 70, $this->truncate(formatCurrency((float) $salaryPayment->amount, compact: false), 18), $success);

        $y = 140;
        foreach ($payload as $row) {
            imagestring($image, 4, 55, $y, $row[0], $muted);
            imagestring($image, 4, 280, $y, $this->truncate($row[1], 90), $ink);
            $y += 38;
        }

        imagestring($image, 3, 55, $height - 70, 'Generated: ' . now()->format('Y-m-d H:i:s'), $muted);

        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        return response($binary, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="payslip-' . $salaryPayment->id . '.png"',
        ]);
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function buildLines(SalaryPayment $payment): array
    {
        $staff = $payment->staffMember;

        return [
            ['Staff Name', (string) ($staff?->full_name ?? '-')],
            ['Staff ID', (string) ($staff?->employee_code ?: '-')],
            ['Department', (string) ($staff?->department ?: '-')],
            ['Category', (string) ($staff?->category ?: '-')],
            ['Email', (string) ($staff?->email ?: '-')],
            ['Phone', (string) ($staff?->phone ?: '-')],
            ['Amount', formatCurrency((float) $payment->amount, compact: false)],
            ['Month', $this->monthName($payment->payment_month)],
            ['Year', (string) ($payment->payment_year ?: '-')],
            ['Payment Method', (string) ($payment->payment_method ?: '-')],
            ['Reference', (string) ($payment->reference ?: '-')],
            ['Paid At', (string) ($payment->paid_at?->format('Y-m-d H:i') ?: '-')],
        ];
    }

    private function monthName(?int $month): string
    {
        if (!$month || $month < 1 || $month > 12) {
            return '-';
        }

        return now()->startOfYear()->month($month)->format('F');
    }

    private function truncate(string $value, int $limit): string
    {
        return \Illuminate\Support\Str::limit($value, $limit, '...');
    }
}
