<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::whereHas('room', function ($query) {
            $query->where('hostel_id', auth()->user()->hostel_id);
        })->with(['user', 'room', 'bed'])->paginate(15);

        return view('manager.bookings.index', compact('bookings'));
    }

    public function students()
    {
        $students = User::where('hostel_id', auth()->user()->hostel_id)
            ->where('role', 'student')
            ->with(['bookings' => function ($query) {
                $query->with('room', 'bed')->latest();
            }])
            ->paginate(15);

        return view('manager.students.index', compact('students'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['user', 'room', 'bed', 'payments']);
        return view('manager.bookings.show', compact('booking'));
    }

    public function approve(Booking $booking)
    {
        $this->authorize('update', $booking);

        $booking->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Booking approved successfully');
    }

    public function reject(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $booking->update([
            'status' => 'rejected',
        ]);

        return redirect()->back()->with('success', 'Booking rejected');
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('update', $booking);

        $booking->update(['status' => 'cancelled']);
        return redirect()->back()->with('success', 'Booking cancelled');
    }
}
