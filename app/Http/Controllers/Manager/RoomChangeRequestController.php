<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\RoomChangeRequest;
use App\Services\OutboundWebhookService;
use App\Services\RoomChangeNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomChangeRequestController extends Controller
{
    public function __construct(private readonly RoomChangeNotificationService $notifier)
    {
    }

    public function index()
    {
        $manager = auth()->user();
        $hostelIds = $manager->managedHostelIds();

        $requests = RoomChangeRequest::query()
            ->whereHas('requestedRoom', fn ($q) => $q->whereIn('hostel_id', $hostelIds))
            ->with(['student', 'currentBooking', 'currentRoom.hostel', 'requestedRoom.hostel', 'managerApprover'])
            ->latest()
            ->paginate(12);

        return view('manager.room-change.index', compact('requests'));
    }

    public function approve(Request $request, RoomChangeRequest $roomChangeRequest)
    {
        $manager = auth()->user();
        $hostelIds = $manager->managedHostelIds();
        $request->validate(['manager_note' => ['nullable', 'string', 'max:1000']]);

        abort_unless(
            $hostelIds->contains($roomChangeRequest->requestedRoom?->hostel_id),
            403
        );

        if ($roomChangeRequest->status !== 'pending_manager_approval') {
            return back()->with('error', 'Only pending room change requests can be approved.');
        }

        $booking = $roomChangeRequest->currentBooking()->with(['bed', 'room'])->first();
        if (!$booking || $booking->status !== 'approved') {
            return back()->with('error', 'Student no longer has an active booking for this request.');
        }

        $newBed = $roomChangeRequest->requestedRoom()
            ->first()
            ?->availableBeds()
            ->first();

        if (!$newBed) {
            return back()->with('error', 'No approved bed is currently available in requested room.');
        }

        DB::transaction(function () use ($booking, $newBed, $roomChangeRequest, $manager, $request): void {
            if ($booking->bed) {
                $booking->bed->update([
                    'is_occupied' => false,
                    'user_id' => null,
                    'occupied_from' => null,
                ]);
            }

            $newBed->update([
                'is_occupied' => true,
                'user_id' => $booking->user_id,
                'occupied_from' => now(),
            ]);

            $booking->update([
                'room_id' => $roomChangeRequest->requested_room_id,
                'bed_id' => $newBed->id,
            ]);

            $roomChangeRequest->update([
                'status' => 'approved',
                'requested_bed_id' => $newBed->id,
                'manager_approved_by' => $manager->id,
                'manager_approved_at' => now(),
                'manager_note' => $request->input('manager_note'),
            ]);
        });

        app(OutboundWebhookService::class)->dispatch('booking.manager_approved', [
            'booking_id' => $booking->id,
            'student_id' => $booking->user_id,
            'status' => $booking->status,
            'note' => 'Room change request approved by manager',
        ]);
        $this->notifier->approved($roomChangeRequest->fresh(['student', 'currentRoom.hostel', 'requestedRoom.hostel']), $manager);

        return back()->with('success', 'Room change approved and student booking has been updated.');
    }

    public function reject(Request $request, RoomChangeRequest $roomChangeRequest)
    {
        $manager = auth()->user();
        $hostelIds = $manager->managedHostelIds();
        $validated = $request->validate([
            'manager_note' => ['required', 'string', 'max:1000'],
        ]);

        abort_unless(
            $hostelIds->contains($roomChangeRequest->requestedRoom?->hostel_id),
            403
        );

        if ($roomChangeRequest->status !== 'pending_manager_approval') {
            return back()->with('error', 'This room change request can no longer be rejected.');
        }

        $roomChangeRequest->update([
            'status' => 'rejected',
            'manager_approved_by' => $manager->id,
            'manager_approved_at' => now(),
            'manager_note' => $validated['manager_note'],
        ]);
        $this->notifier->rejected($roomChangeRequest->fresh(['student', 'currentRoom.hostel', 'requestedRoom.hostel']), $manager);

        return back()->with('success', 'Room change request rejected.');
    }
}
