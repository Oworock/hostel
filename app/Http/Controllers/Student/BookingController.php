<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $bookings = auth()->user()->bookings()->with(['room', 'bed', 'payments'])->paginate(15);
        return view('student.bookings.index', compact('bookings'));
    }

    public function available()
    {
        $rooms = Room::where('is_available', true)
            ->with(['hostel', 'beds'])
            ->paginate(12);

        return view('student.bookings.available', compact('rooms'));
    }

    public function create(Room $room)
    {
        $availableBeds = $room->availableBeds()->get();

        if ($availableBeds->isEmpty()) {
            return redirect()->back()->with('error', 'No available beds in this room');
        }

        return view('student.bookings.create', compact('room', 'availableBeds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'nullable|exists:beds,id',
            'check_in_date' => 'required|date|after:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $room = Room::find($validated['room_id']);
        $validated['user_id'] = auth()->id();
        $validated['total_amount'] = $room->price_per_month;

        $booking = Booking::create($validated);

        return redirect()->route('student.bookings.show', $booking)->with('success', 'Booking created successfully');
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['room', 'bed', 'payments']);

        return view('student.bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('update', $booking);

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return redirect()->back()->with('error', 'Cannot cancel this booking');
        }

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('student.bookings.index')->with('success', 'Booking cancelled successfully');
    }
}
