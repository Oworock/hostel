<?php

namespace App\Services;

use App\Models\RoomChangeRequest;
use App\Models\User;
use App\Notifications\SystemEventNotification;
use Throwable;

class RoomChangeNotificationService
{
    public function __construct(
        private readonly NotificationTemplateService $templates,
    ) {
    }

    public function submitted(RoomChangeRequest $request): void
    {
        $request->loadMissing(['student', 'currentRoom.hostel', 'requestedRoom.hostel.managers']);

        $student = $request->student;
        $managers = $request->requestedRoom?->hostel?->managers ?? collect();
        $admins = User::where('role', 'admin')->get();

        foreach ($managers as $manager) {
            $this->notify($manager, 'room_change.submitted', $request, null);
        }

        foreach ($admins as $admin) {
            $this->notify($admin, 'room_change.submitted', $request, null);
        }

        if ($student) {
            $this->notify($student, 'room_change.submitted', $request, null);
        }
    }

    public function approved(RoomChangeRequest $request, User $manager): void
    {
        $request->loadMissing(['student', 'currentRoom.hostel', 'requestedRoom.hostel']);

        if ($request->student) {
            $this->notify($request->student, 'room_change.approved', $request, $manager->name);
        }

        foreach (User::where('role', 'admin')->get() as $admin) {
            $this->notify($admin, 'room_change.approved', $request, $manager->name);
        }
    }

    public function rejected(RoomChangeRequest $request, User $manager): void
    {
        $request->loadMissing(['student', 'currentRoom.hostel', 'requestedRoom.hostel']);

        if ($request->student) {
            $this->notify($request->student, 'room_change.rejected', $request, $manager->name);
        }

        foreach (User::where('role', 'admin')->get() as $admin) {
            $this->notify($admin, 'room_change.rejected', $request, $manager->name);
        }
    }

    private function notify(User $user, string $event, RoomChangeRequest $request, ?string $actorName): void
    {
        $context = [
            'student_name' => $request->student?->name ?? 'Student',
            'current_room' => $request->currentRoom?->room_number ?? 'N/A',
            'requested_room' => $request->requestedRoom?->room_number ?? 'N/A',
            'current_hostel' => $request->currentRoom?->hostel?->name ?? 'N/A',
            'requested_hostel' => $request->requestedRoom?->hostel?->name ?? 'N/A',
            'actor_name' => $actorName ?? 'System',
            'status' => $request->status,
            'reason' => $request->reason ?? '',
        ];

        $copy = $this->templates->render($event, $context);

        try {
            $user->notify(new SystemEventNotification($event, $copy['title'], $copy['message'], [
                'room_change_request_id' => $request->id,
                'status' => $request->status,
                'student_name' => $request->student?->name,
                'current_room' => $request->currentRoom?->room_number,
                'requested_room' => $request->requestedRoom?->room_number,
                'actor' => $actorName,
            ]));
        } catch (Throwable $e) {
            report($e);
        }
    }
}

