<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\OutboundWebhookService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::whereHas('room', function ($query) {
            $query->whereIn('hostel_id', auth()->user()->managedHostelIds());
        })->with(['user', 'room', 'bed'])->paginate(15);

        return view('manager.bookings.index', compact('bookings'));
    }

    public function students()
    {
        $students = User::whereIn('hostel_id', auth()->user()->managedHostelIds())
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

        if (!$booking->isFullyPaid()) {
            return redirect()->back()->with('error', 'Cannot approve booking until full payment is completed.');
        }

        $booking->update(['status' => 'approved']);
        app(OutboundWebhookService::class)->dispatch('booking.manager_approved', [
            'booking_id' => $booking->id,
            'student_id' => $booking->user_id,
            'manager_id' => auth()->id(),
            'status' => $booking->status,
        ]);
        return redirect()->back()->with('success', 'Booking approved successfully');
    }

    public function reject(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $booking->update([
            'status' => 'rejected',
        ]);
        app(OutboundWebhookService::class)->dispatch('booking.manager_rejected', [
            'booking_id' => $booking->id,
            'student_id' => $booking->user_id,
            'manager_id' => auth()->id(),
            'status' => $booking->status,
        ]);

        return redirect()->back()->with('success', 'Booking rejected');
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('update', $booking);

        $booking->update(['status' => 'cancelled']);
        app(OutboundWebhookService::class)->dispatch('booking.manager_cancelled', [
            'booking_id' => $booking->id,
            'student_id' => $booking->user_id,
            'manager_id' => auth()->id(),
            'status' => $booking->status,
        ]);
        return redirect()->back()->with('success', 'Booking cancelled');
    }
}
