<?php

namespace App\Notifications;

use App\Models\SalaryPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffSalaryPaidNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly SalaryPayment $payment
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->payment->loadMissing('staffMember');
        $staffName = (string) ($this->payment->staffMember?->full_name ?: 'Staff');
        $period = ($this->payment->payment_month && $this->payment->payment_year)
            ? $this->payment->payment_month . '/' . $this->payment->payment_year
            : 'N/A';

        return (new MailMessage)
            ->subject(__('Salary Payment Confirmation'))
            ->greeting(__('Hello :name,', ['name' => $staffName]))
            ->line(__('Your salary has been paid successfully.'))
            ->line(__('Amount: :amount', ['amount' => formatCurrency((float) $this->payment->amount, compact: false)]))
            ->line(__('Period: :period', ['period' => $period]))
            ->line(__('Reference: :reference', ['reference' => (string) ($this->payment->reference ?: 'N/A')]))
            ->line(__('Thank you.'));
    }
}

