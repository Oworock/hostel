<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\SalaryPayment;
use App\Models\StaffMember;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class StaffPayrollNotificationService
{
    public function notifySalaryPaid(SalaryPayment $payment): void
    {
        if (!Addon::isActive('staff-payroll')) {
            return;
        }

        $payment->loadMissing('staffMember');
        $staff = $payment->staffMember;
        if (!$staff) {
            return;
        }

        $replacements = $this->baseReplacements($staff) + [
            '{amount}' => formatCurrency((float) $payment->amount, compact: false),
            '{month}' => $this->monthName($payment->payment_month),
            '{year}' => (string) ($payment->payment_year ?: '-'),
            '{reference}' => (string) ($payment->reference ?: 'N/A'),
            '{payslip_link}' => $this->payslipLink($payment),
        ];

        $emailTemplate = (string) get_setting(
            'staff_payroll_salary_paid_email_template',
            'Hello {name}, your salary of {amount} for {month} {year} has been paid. Reference: {reference}. View payslip: {payslip_link}'
        );
        $smsTemplate = (string) get_setting(
            'staff_payroll_salary_paid_sms_template',
            'Salary paid: {amount} for {month} {year}. Ref: {reference}. Payslip: {payslip_link}'
        );

        $this->sendEmailAndSms($staff, $emailTemplate, $smsTemplate, $replacements, 'Salary Paid Notification');
    }

    public function notifyStatusChanged(StaffMember $staff, string $status): void
    {
        if (!Addon::isActive('staff-payroll')) {
            return;
        }

        $statusKey = strtolower(trim($status));
        if (!in_array($statusKey, ['suspended', 'sacked', 'active', 'inactive', 'pending'], true)) {
            return;
        }

        $replacements = $this->baseReplacements($staff) + [
            '{status}' => ucfirst($statusKey),
        ];

        $emailTemplate = (string) get_setting(
            "staff_payroll_{$statusKey}_email_template",
            'Hello {name}, your staff profile status is now {status}.'
        );
        $smsTemplate = (string) get_setting(
            "staff_payroll_{$statusKey}_sms_template",
            'Staff status update: {status}.'
        );

        $subject = ucfirst($statusKey) . ' Staff Status Notification';
        $this->sendEmailAndSms($staff, $emailTemplate, $smsTemplate, $replacements, $subject);
    }

    public function sendIdCard(StaffMember $staff): void
    {
        if (!Addon::isActive('staff-payroll') || empty($staff->email) || empty($staff->id_card_path)) {
            return;
        }
        if (!$this->isEmailChannelConfigured()) {
            throw new \RuntimeException('SMTP is not configured. Update mail settings before sending ID cards.');
        }

        $subject = __('Your Staff ID Card');
        $bodyTemplate = (string) get_setting(
            'staff_payroll_id_card_email_template',
            'Hello {name}, attached is your staff ID card.'
        );
        $body = $this->applyTemplate($bodyTemplate, $this->baseReplacements($staff));

        $path = \Illuminate\Support\Facades\Storage::disk('public')->path((string) $staff->id_card_path);
        if (!is_file($path)) {
            throw new \RuntimeException('ID card file was not found. Regenerate the card and try again.');
        }

        Mail::send([], [], function ($message) use ($staff, $subject, $body, $path): void {
            $message
                ->to((string) $staff->email)
                ->subject($subject)
                ->html(nl2br(e($body)))
                ->attach($path, ['as' => 'staff-id-card.' . pathinfo($path, PATHINFO_EXTENSION)]);
        });
    }

    private function sendEmailAndSms(StaffMember $staff, string $emailTemplate, string $smsTemplate, array $replacements, string $subject): void
    {
        $emailEnabled = filter_var(get_setting('staff_payroll_email_notifications_enabled', false), FILTER_VALIDATE_BOOL);
        $smsEnabled = filter_var(get_setting('staff_payroll_sms_notifications_enabled', false), FILTER_VALIDATE_BOOL);

        if ($emailEnabled && !empty($staff->email)) {
            if ($this->isEmailChannelConfigured()) {
                $body = $this->applyTemplate($emailTemplate, $replacements);
                Mail::send([], [], function ($message) use ($staff, $subject, $body): void {
                    $message
                        ->to((string) $staff->email)
                        ->subject($subject)
                        ->html(nl2br(e($body)));
                });
            } else {
                Log::warning('Staff payroll email notification skipped: SMTP not configured.', ['staff_id' => $staff->id]);
            }
        }

        if ($smsEnabled && !empty($staff->phone)) {
            $sms = app(SmsGatewayService::class);
            if ($sms->isConfigured()) {
                $text = $this->applyTemplate($smsTemplate, $replacements);
                $sms->send((string) $staff->phone, $text);
            } else {
                Log::warning('Staff payroll SMS notification skipped: SMS gateway not configured.', ['staff_id' => $staff->id]);
            }
        }
    }

    private function applyTemplate(string $template, array $replacements): string
    {
        return strtr($template, $replacements);
    }

    private function baseReplacements(StaffMember $staff): array
    {
        return [
            '{name}' => (string) $staff->full_name,
            '{email}' => (string) $staff->email,
            '{phone}' => (string) ($staff->phone ?: 'N/A'),
            '{department}' => (string) ($staff->department ?: 'N/A'),
            '{job_title}' => (string) ($staff->job_title ?: 'N/A'),
            '{app_name}' => (string) get_setting('app_name', config('app.name', 'Hostel System')),
        ];
    }

    private function monthName(?int $month): string
    {
        if (!$month || $month < 1 || $month > 12) {
            return '-';
        }

        return now()->startOfYear()->month($month)->format('F');
    }

    private function payslipLink(SalaryPayment $payment): string
    {
        if ($payment->id === null) {
            return '';
        }

        return URL::temporarySignedRoute(
            'staff.payslips.show',
            now()->addDays(30),
            ['salaryPayment' => $payment->id]
        );
    }

    public function isEmailChannelConfigured(): bool
    {
        $mailer = strtolower((string) config('mail.default', env('MAIL_MAILER', 'smtp')));
        if ($mailer !== 'smtp') {
            return true;
        }

        $host = trim((string) config('mail.mailers.smtp.host'));
        $port = trim((string) config('mail.mailers.smtp.port'));
        $from = trim((string) config('mail.from.address'));

        return $host !== '' && $port !== '' && $from !== '';
    }
}
