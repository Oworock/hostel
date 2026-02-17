<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomChangeRequest;
use App\Services\RoomChangeNotificationService;
use Illuminate\Http\Request;

class RoomChangeRequestController extends Controller
{
    public function __construct(private readonly RoomChangeNotificationService $notifier)
    {
    }

    public function index()
    {
        $student = auth()->user();
        $activeBooking = $student->bookings()
            ->where('status', 'approved')
            ->with(['room.hostel', 'bed'])
            ->latest()
            ->first();

        $availableRooms = collect();
        if ($activeBooking) {
            $availableRooms = Room::query()
                ->where('hostel_id', $activeBooking->room->hostel_id)
                ->where('id', '!=', $activeBooking->room_id)
                ->where('is_available', true)
                ->with(['hostel', 'beds'])
                ->get()
                ->filter(fn (Room $room) => $room->availableBeds()->exists())
                ->values();
        }

        $requests = RoomChangeRequest::query()
            ->where('student_id', $student->id)
            ->with(['currentRoom.hostel', 'requestedRoom.hostel', 'managerApprover'])
            ->latest()
            ->paginate(10);

        return view('student.room-change.index', compact('activeBooking', 'availableRooms', 'requests'));
    }

    public function store(Request $request)
    {
        $student = auth()->user();
        $activeBooking = $student->bookings()
            ->where('status', 'approved')
            ->with('room')
            ->latest()
            ->first();

        if (!$activeBooking) {
            return back()->with('error', 'You need an active approved booking to request room change.');
        }

        $validated = $request->validate([
            'requested_room_id' => ['required', 'exists:rooms,id'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $requestedRoom = Room::query()
            ->where('id', $validated['requested_room_id'])
            ->where('hostel_id', $activeBooking->room->hostel_id)
            ->where('is_available', true)
            ->first();

        if (!$requestedRoom) {
            return back()->with('error', 'Requested room is invalid or not available in your current hostel.');
        }

        if (!$requestedRoom->availableBeds()->exists()) {
            return back()->with('error', 'Requested room has no available approved bed.');
        }

        $pendingExists = RoomChangeRequest::query()
            ->where('student_id', $student->id)
            ->where('status', 'pending_manager_approval')
            ->exists();

        if ($pendingExists) {
            return back()->with('error', 'You already have a pending room change request.');
        }

        $requestModel = RoomChangeRequest::create([
            'student_id' => $student->id,
            'current_booking_id' => $activeBooking->id,
            'current_room_id' => $activeBooking->room_id,
            'requested_room_id' => $requestedRoom->id,
            'status' => 'pending_manager_approval',
            'reason' => $validated['reason'] ?? null,
        ]);

        $this->notifier->submitted($requestModel);

        return back()->with('success', 'Room change request submitted for manager approval.');
    }
}
