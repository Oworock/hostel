<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemEventNotification extends Notification
{
    use Queueable;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $event,
        public string $title,
        public string $message,
        public array $payload = [],
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => 'system_event',
            'event' => $this->event,
            'title' => $this->title,
            'message' => $this->message,
        ], $this->payload);
    }
}

