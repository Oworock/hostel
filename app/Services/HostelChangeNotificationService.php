<?php

namespace App\Services;

use App\Models\HostelChangeRequest;
use App\Models\User;
use App\Notifications\HostelChangeStatusNotification;
use Throwable;

class HostelChangeNotificationService
{
    public function __construct(
        private readonly SmsGatewayService $sms,
        private readonly OutboundWebhookService $webhooks,
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
            $this->notifyUser($student, 'submitted', $request, null, 'Your hostel change request was submitted successfully.');
        }

        foreach ($managers as $manager) {
            $this->notifyUser($manager, 'submitted', $request, null, 'A new hostel change request requires your approval.');
        }

        foreach ($admins as $admin) {
            $this->notifyUser($admin, 'submitted', $request, null, 'A new hostel change request has been submitted.');
        }
    }

    public function managerApproved(HostelChangeRequest $request, User $manager): void
    {
        $request->loadMissing(['student', 'requestedHostel', 'currentHostel']);
        $this->webhooks->dispatch('hostel_change.manager_approved', $this->payload($request, $manager));

        $student = $request->student;
        $admins = User::where('role', 'admin')->get();

        if ($student) {
            $this->notifyUser($student, 'manager_approved', $request, $manager->name, 'Your hostel change request was approved by the hostel manager and is pending admin approval.');
        }

        foreach ($admins as $admin) {
            $this->notifyUser($admin, 'manager_approved', $request, $manager->name, 'A hostel change request now requires admin approval.');
        }
    }

    public function managerRejected(HostelChangeRequest $request, User $manager): void
    {
        $request->loadMissing(['student', 'requestedHostel', 'currentHostel']);
        $this->webhooks->dispatch('hostel_change.manager_rejected', $this->payload($request, $manager));

        if ($request->student) {
            $this->notifyUser($request->student, 'manager_rejected', $request, $manager->name, 'Your hostel change request was rejected by the target hostel manager.');
        }
    }

    public function adminApproved(HostelChangeRequest $request, User $admin): void
    {
        $request->loadMissing(['student', 'requestedHostel.managers', 'currentHostel']);
        $this->webhooks->dispatch('hostel_change.admin_approved', $this->payload($request, $admin));

        if ($request->student) {
            $this->notifyUser($request->student, 'admin_approved', $request, $admin->name, 'Your hostel change request has been approved by admin.');
        }

        foreach (($request->requestedHostel?->managers ?? collect()) as $manager) {
            $this->notifyUser($manager, 'admin_approved', $request, $admin->name, 'Admin approved a hostel change request into your hostel.');
        }
    }

    public function adminRejected(HostelChangeRequest $request, User $admin): void
    {
        $request->loadMissing(['student', 'requestedHostel', 'currentHostel']);
        $this->webhooks->dispatch('hostel_change.admin_rejected', $this->payload($request, $admin));

        if ($request->student) {
            $this->notifyUser($request->student, 'admin_rejected', $request, $admin->name, 'Your hostel change request was rejected by admin.');
        }
    }

    private function notifyUser(User $user, string $event, HostelChangeRequest $request, ?string $actorName, string $smsText): void
    {
        try {
            $user->notify(new HostelChangeStatusNotification($event, $request, $actorName));
        } catch (Throwable $e) {
            report($e);
        }

        if (!empty($user->phone)) {
            try {
                $this->sms->send((string) $user->phone, $smsText);
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
