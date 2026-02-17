<?php

namespace App\Notifications;

use App\Models\HostelChangeRequest;
use App\Services\NotificationTemplateService;
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
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $copy = $this->messageCopy();

        return (new MailMessage)
            ->subject($copy['title'])
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line($copy['message'])
            ->line('Student: ' . ($this->request->student?->name ?? 'N/A'))
            ->line('From: ' . ($this->request->currentHostel?->name ?? 'Not Assigned'))
            ->line('To: ' . ($this->request->requestedHostel?->name ?? 'N/A'))
            ->line('Status: ' . ucwords(str_replace('_', ' ', $this->request->status)))
            ->action('Open Dashboard', url('/dashboard'));
    }

    private function messageCopy(): array
    {
        $eventKey = 'hostel_change.' . $this->event;

        return app(NotificationTemplateService::class)->render($eventKey, [
            'student_name' => $this->request->student?->name ?? 'Student',
            'current_hostel' => $this->request->currentHostel?->name ?? 'N/A',
            'requested_hostel' => $this->request->requestedHostel?->name ?? 'N/A',
            'actor_name' => $this->actorName ?? 'System',
            'status' => $this->request->status,
            'reason' => $this->request->reason ?? '',
        ]);
    }
}
