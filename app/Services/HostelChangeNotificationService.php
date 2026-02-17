<?php

namespace App\Services;

use App\Models\HostelChangeRequest;
use App\Models\User;
use App\Notifications\HostelChangeStatusNotification;
use App\Notifications\SystemEventNotification;
use Throwable;

class HostelChangeNotificationService
{
    public function __construct(
        private readonly SmsGatewayService $sms,
        private readonly OutboundWebhookService $webhooks,
        private readonly NotificationTemplateService $templates,
    )
    {
    }

    public function submitted(HostelChangeRequest $request): void
    {
        $request->loadMissing(['student', 'currentHostel', 'requestedHostel.managers']);
        $this->webhooks->dispatch('hostel_change.submitted', $this->payload($request, null));

        $student = $request->student;
        $managers = $request->requestedHostel?->managers ?? collect();
        $admins = User::where('role', 'admin')->get();

        if ($student) {
            $this->notifyUser($student, 'submitted', $request, null);
        }

        foreach ($managers as $manager) {
            $this->notifyUser($manager, 'submitted', $request, null);
        }

        foreach ($admins as $admin) {
            $this->notifyUser($admin, 'submitted', $request, null);
        }
    }

    public function managerApproved(HostelChangeRequest $request, User $manager): void
    {
        $request->loadMissing(['student', 'requestedHostel', 'currentHostel']);
        $this->webhooks->dispatch('hostel_change.manager_approved', $this->payload($request, $manager));

        $student = $request->student;
        $admins = User::where('role', 'admin')->get();

        if ($student) {
            $this->notifyUser($student, 'manager_approved', $request, $manager->name);
        }

        foreach ($admins as $admin) {
            $this->notifyUser($admin, 'manager_approved', $request, $manager->name);
        }
    }

    public function managerRejected(HostelChangeRequest $request, User $manager): void
    {
        $request->loadMissing(['student', 'requestedHostel', 'currentHostel']);
        $this->webhooks->dispatch('hostel_change.manager_rejected', $this->payload($request, $manager));

        if ($request->student) {
            $this->notifyUser($request->student, 'manager_rejected', $request, $manager->name);
        }
    }

    public function adminApproved(HostelChangeRequest $request, User $admin): void
    {
        $request->loadMissing(['student', 'requestedHostel.managers', 'currentHostel']);
        $this->webhooks->dispatch('hostel_change.admin_approved', $this->payload($request, $admin));

        if ($request->student) {
            $this->notifyUser($request->student, 'admin_approved', $request, $admin->name);
        }

        foreach (($request->requestedHostel?->managers ?? collect()) as $manager) {
            $this->notifyUser($manager, 'admin_approved', $request, $admin->name);
        }
    }

    public function adminRejected(HostelChangeRequest $request, User $admin): void
    {
        $request->loadMissing(['student', 'requestedHostel', 'currentHostel']);
        $this->webhooks->dispatch('hostel_change.admin_rejected', $this->payload($request, $admin));

        if ($request->student) {
            $this->notifyUser($request->student, 'admin_rejected', $request, $admin->name);
        }
    }

    private function notifyUser(User $user, string $event, HostelChangeRequest $request, ?string $actorName): void
    {
        $eventKey = 'hostel_change.' . $event;
        $copy = $this->templates->render($eventKey, [
            'student_name' => $request->student?->name ?? 'Student',
            'current_hostel' => $request->currentHostel?->name ?? 'N/A',
            'requested_hostel' => $request->requestedHostel?->name ?? 'N/A',
            'actor_name' => $actorName ?? 'System',
            'status' => $request->status,
            'reason' => $request->reason ?? '',
        ]);

        try {
            $user->notify(new SystemEventNotification($eventKey, $copy['title'], $copy['message'], [
                'type' => 'hostel_change',
                'request_id' => $request->id,
                'status' => $request->status,
                'student_name' => $request->student?->name,
                'current_hostel' => $request->currentHostel?->name,
                'requested_hostel' => $request->requestedHostel?->name,
                'actor' => $actorName,
            ]));
        } catch (Throwable $e) {
            report($e);
        }

        try {
            $user->notify(new HostelChangeStatusNotification($event, $request, $actorName));
        } catch (Throwable $e) {
            report($e);
        }

        if (!empty($user->phone)) {
            try {
                $this->sms->send((string) $user->phone, $copy['message']);
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    private function payload(HostelChangeRequest $request, ?User $actor): array
    {
        return [
            'id' => $request->id,
            'status' => $request->status,
            'reason' => $request->reason,
            'student' => [
                'id' => $request->student?->id,
                'name' => $request->student?->name,
                'email' => $request->student?->email,
            ],
            'current_hostel' => [
                'id' => $request->currentHostel?->id,
                'name' => $request->currentHostel?->name,
            ],
            'requested_hostel' => [
                'id' => $request->requestedHostel?->id,
                'name' => $request->requestedHostel?->name,
            ],
            'actor' => $actor ? [
                'id' => $actor->id,
                'name' => $actor->name,
                'role' => $actor->role,
            ] : null,
        ];
    }
}
