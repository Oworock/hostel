<?php

namespace App\Services;

use App\Models\ReferralAgent;
use App\Models\ReferralCommission;
use App\Models\ReferralPayoutRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ReferralNotificationService
{
    public function notifyStudentRegistered(ReferralAgent $agent, User $student): void
    {
        $replacements = [
            '{agent_name}' => (string) $agent->name,
            '{student_name}' => (string) $student->name,
            '{student_email}' => (string) $student->email,
            '{student_phone}' => (string) ($student->phone ?? ''),
        ];

        $subject = (string) get_setting('referral_notify_student_registered_email_subject', 'New referral registration');
        $emailMessage = $this->interpolate(
            (string) get_setting('referral_notify_student_registered_email_template', 'A new student registered with your referral link: {student_name} ({student_email}).'),
            $replacements
        );
        $smsMessage = $this->interpolate(
            (string) get_setting('referral_notify_student_registered_sms_template', 'New referral signup: {student_name} ({student_email}).'),
            $replacements
        );

        $this->send($agent, $subject, $emailMessage, $smsMessage);
    }

    public function notifyCommissionEarned(ReferralAgent $agent, ReferralCommission $commission): void
    {
        $replacements = [
            '{agent_name}' => (string) $agent->name,
            '{commission_amount}' => formatCurrency((float) $commission->amount, false),
            '{booking_id}' => (string) $commission->booking_id,
            '{student_name}' => (string) ($commission->student?->name ?? ''),
        ];

        $subject = (string) get_setting('referral_notify_commission_email_subject', 'Referral commission earned');
        $emailMessage = $this->interpolate(
            (string) get_setting('referral_notify_commission_email_template', 'A referred student completed payment. Commission earned: {commission_amount}. Booking #{booking_id}.'),
            $replacements
        );
        $smsMessage = $this->interpolate(
            (string) get_setting('referral_notify_commission_sms_template', 'Commission earned: {commission_amount} for booking #{booking_id}.'),
            $replacements
        );

        $this->send($agent, $subject, $emailMessage, $smsMessage);
    }

    public function notifyPayoutStatus(ReferralPayoutRequest $request): void
    {
        $agent = $request->agent;
        if (!$agent) {
            return;
        }

        $replacements = [
            '{agent_name}' => (string) $agent->name,
            '{payout_amount}' => formatCurrency((float) $request->amount, false),
            '{payout_status}' => strtoupper((string) $request->status),
        ];

        $subject = (string) get_setting('referral_notify_payout_email_subject', 'Referral payout update');
        $emailMessage = $this->interpolate(
            (string) get_setting('referral_notify_payout_email_template', 'Your payout request for {payout_amount} is now {payout_status}.'),
            $replacements
        );
        $smsMessage = $this->interpolate(
            (string) get_setting('referral_notify_payout_sms_template', 'Payout {payout_amount} status: {payout_status}.'),
            $replacements
        );

        $this->send($agent, $subject, $emailMessage, $smsMessage);
    }

    private function send(ReferralAgent $agent, string $subject, string $emailMessage, string $smsMessage): void
    {
        $emailEnabled = filter_var(get_setting('referral_notify_email', true), FILTER_VALIDATE_BOOL);
        $smsEnabled = filter_var(get_setting('referral_notify_sms', false), FILTER_VALIDATE_BOOL);

        if ($emailEnabled && filled($agent->email)) {
            try {
                Mail::raw($emailMessage, function ($mail) use ($agent, $subject): void {
                    $mail->to((string) $agent->email)->subject($subject);
                });
            } catch (Throwable $e) {
                // Do not block the main flow on notification failures.
            }
        }

        if ($smsEnabled && filled($agent->phone)) {
            try {
                app(SmsGatewayService::class)->send((string) $agent->phone, $smsMessage);
            } catch (Throwable $e) {
                // Do not block the main flow on notification failures.
            }
        }
    }

    /**
     * @param array<string, string> $replacements
     */
    private function interpolate(string $template, array $replacements): string
    {
        return strtr($template, $replacements);
    }
}
