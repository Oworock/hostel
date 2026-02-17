<?php

namespace App\Notifications;

use App\Models\HostelChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HostelChangeStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $event,
        public HostelChangeRequest $request,
        public ?string $actorName = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $copy = $this->messageCopy();

        return (new MailMessage)
            ->subject($copy['title'])
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line($copy['line'])
            ->line('Student: ' . ($this->request->student?->name ?? 'N/A'))
            ->line('From: ' . ($this->request->currentHostel?->name ?? 'Not Assigned'))
            ->line('To: ' . ($this->request->requestedHostel?->name ?? 'N/A'))
            ->line('Status: ' . ucwords(str_replace('_', ' ', $this->request->status)))
            ->action('Open Dashboard', url('/dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        $copy = $this->messageCopy();

        return [
            'type' => 'hostel_change',
            'event' => $this->event,
            'title' => $copy['title'],
            'message' => $copy['line'],
            'request_id' => $this->request->id,
            'student_name' => $this->request->student?->name,
            'current_hostel' => $this->request->currentHostel?->name,
            'requested_hostel' => $this->request->requestedHostel?->name,
            'status' => $this->request->status,
            'actor' => $this->actorName,
        ];
    }

    private function messageCopy(): array
    {
        return match ($this->event) {
            'submitted' => [
                'title' => 'Hostel Change Request Submitted',
                'line' => 'A hostel change request has been submitted and is awaiting manager review.',
            ],
            'manager_approved' => [
                'title' => 'Hostel Change Request Manager Approved',
                'line' => 'The target hostel manager approved this request. Awaiting admin approval.',
            ],
            'manager_rejected' => [
                'title' => 'Hostel Change Request Rejected by Manager',
                'line' => 'The target hostel manager rejected this hostel change request.',
            ],
            'admin_approved' => [
                'title' => 'Hostel Change Request Approved',
                'line' => 'The admin approved this hostel change request.',
            ],
            'admin_rejected' => [
                'title' => 'Hostel Change Request Rejected by Admin',
                'line' => 'The admin rejected this hostel change request.',
            ],
            default => [
                'title' => 'Hostel Change Update',
                'line' => 'There is an update on a hostel change request.',
            ],
        };
    }
}
