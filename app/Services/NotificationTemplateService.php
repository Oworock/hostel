<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\SystemSetting;

class NotificationTemplateService
{
    /**
     * @return array<string, array{title: string, message: string}>
     */
    public function defaults(): array
    {
        $base = [
            'booking.created' => [
                'title' => 'Booking Submitted',
                'message' => '{student_name} submitted a booking request.',
            ],
            'booking.cancelled' => [
                'title' => 'Booking Cancelled',
                'message' => '{student_name} cancelled a booking.',
            ],
            'booking.manager_approved' => [
                'title' => 'Booking Approved',
                'message' => '{actor_name} approved booking for {student_name}.',
            ],
            'booking.manager_rejected' => [
                'title' => 'Booking Rejected',
                'message' => '{actor_name} rejected booking for {student_name}.',
            ],
            'booking.manager_cancelled' => [
                'title' => 'Booking Cancelled by Manager',
                'message' => '{actor_name} cancelled booking for {student_name}.',
            ],
            'payment.completed' => [
                'title' => 'Payment Completed',
                'message' => 'Payment was completed by {student_name}.',
            ],
            'complaint.created' => [
                'title' => 'Complaint Submitted',
                'message' => '{student_name} submitted a complaint.',
            ],
            'complaint.responded' => [
                'title' => 'Complaint Responded',
                'message' => '{actor_name} responded to complaint from {student_name}.',
            ],
            'hostel_change.submitted' => [
                'title' => 'Hostel Change Request Submitted',
                'message' => 'A hostel change request by {student_name} was submitted and is awaiting manager review.',
            ],
            'hostel_change.manager_approved' => [
                'title' => 'Hostel Change Manager Approved',
                'message' => '{actor_name} approved a hostel change request. Awaiting admin approval.',
            ],
            'hostel_change.manager_rejected' => [
                'title' => 'Hostel Change Rejected by Manager',
                'message' => '{actor_name} rejected a hostel change request.',
            ],
            'hostel_change.admin_approved' => [
                'title' => 'Hostel Change Approved',
                'message' => '{actor_name} approved the hostel change request.',
            ],
            'hostel_change.admin_rejected' => [
                'title' => 'Hostel Change Rejected by Admin',
                'message' => '{actor_name} rejected the hostel change request.',
            ],
            'room_change.submitted' => [
                'title' => 'Room Change Request Submitted',
                'message' => '{student_name} requested to move from room {current_room} to room {requested_room}.',
            ],
            'room_change.approved' => [
                'title' => 'Room Change Approved',
                'message' => '{actor_name} approved room change to {requested_room}.',
            ],
            'room_change.rejected' => [
                'title' => 'Room Change Rejected',
                'message' => '{actor_name} rejected room change request to {requested_room}.',
            ],
        ];

        $addon = [
            'asset.created' => [
                'title' => 'Asset Added',
                'message' => 'A new asset {asset_name} was added to {hostel_name}.',
            ],
            'asset.issue_reported' => [
                'title' => 'Asset Issue Reported',
                'message' => '{actor_name} reported an issue for asset {asset_name}.',
            ],
            'asset.movement_requested' => [
                'title' => 'Asset Movement Requested',
                'message' => '{actor_name} requested movement for asset {asset_name}.',
            ],
            'asset.movement_receiving_decision' => [
                'title' => 'Receiving Manager Decision',
                'message' => '{actor_name} responded to a movement request for {asset_name}.',
            ],
            'asset.movement_approved' => [
                'title' => 'Asset Movement Approved',
                'message' => 'Admin approved movement for asset {asset_name}.',
            ],
            'asset.movement_rejected' => [
                'title' => 'Asset Movement Rejected',
                'message' => 'Admin rejected movement for asset {asset_name}.',
            ],
            'asset.subscription.created' => [
                'title' => 'Subscription Added',
                'message' => 'Subscription {subscription_name} was added for {hostel_name}.',
            ],
            'asset.subscription.updated' => [
                'title' => 'Subscription Updated',
                'message' => 'Subscription {subscription_name} was updated.',
            ],
            'asset.subscription.deleted' => [
                'title' => 'Subscription Removed',
                'message' => 'Subscription {subscription_name} was removed.',
            ],
            'asset.subscription.expiry_alert' => [
                'title' => 'Subscription Expiry Alert',
                'message' => '{subscription_name} for {hostel_name} expires in {days_left} day(s).',
            ],
        ];

        if (Addon::isActive('asset-management')) {
            return array_merge($base, $addon);
        }

        return $base;
    }

    /**
     * @return array<string, array{title: string, message: string}>
     */
    public function all(): array
    {
        $defaults = $this->defaults();
        $stored = json_decode((string) SystemSetting::getSetting('notification_templates_json', '[]'), true);

        if (!is_array($stored)) {
            return $defaults;
        }

        foreach ($stored as $key => $template) {
            if (!is_string($key) || !isset($defaults[$key]) || !is_array($template)) {
                continue;
            }

            $title = trim((string) ($template['title'] ?? ''));
            $message = trim((string) ($template['message'] ?? ''));

            if ($title !== '') {
                $defaults[$key]['title'] = $title;
            }
            if ($message !== '') {
                $defaults[$key]['message'] = $message;
            }
        }

        return $defaults;
    }

    /**
     * @param array<string, mixed> $context
     * @return array{title: string, message: string}
     */
    public function render(string $eventKey, array $context = []): array
    {
        $template = $this->all()[$eventKey] ?? [
            'title' => 'System Notification',
            'message' => 'There is an update in the system.',
        ];

        $replacements = [];
        foreach ($context as $key => $value) {
            $replacements['{' . $key . '}'] = (string) $value;
        }

        return [
            'title' => strtr($template['title'], $replacements),
            'message' => strtr($template['message'], $replacements),
        ];
    }

    /**
     * @return array<int, array{event: string, title: string, message: string}>
     */
    public function forRepeater(): array
    {
        return collect($this->all())
            ->map(fn (array $row, string $event) => [
                'event' => $event,
                'title' => $row['title'],
                'message' => $row['message'],
            ])
            ->values()
            ->all();
    }
}
