<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PaymentGateway;
use App\Models\Room;
use App\Models\AcademicSession;
use App\Models\Hostel;
use App\Models\Semester;
use App\Services\OutboundWebhookService;
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

    public function available(Request $request)
    {
        $blockingBooking = $this->getBlockingBooking();

        $query = Room::query()
            ->where('is_available', true)
            ->with(['hostel', 'beds', 'images']);

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->integer('hostel_id'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('room_number', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhereHas('hostel', fn ($h) => $h->where('name', 'like', '%' . $search . '%')->orWhere('city', 'like', '%' . $search . '%'));
            });
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_month', '<=', (float) $request->input('max_price'));
        }

        $sort = $request->input('sort', 'price_asc');
        match ($sort) {
            'price_desc' => $query->orderByDesc('price_per_month'),
            'recent' => $query->latest(),
            default => $query->orderBy('price_per_month'),
        };

        $rooms = $query->paginate(12)->withQueryString();
        $hostels = Hostel::where('is_active', true)->orderBy('name')->get();

        return view('student.bookings.available', compact('rooms', 'blockingBooking', 'hostels', 'sort'));
    }

    public function create(Room $room)
    {
        $blockingBooking = $this->getBlockingBooking();
        if ($blockingBooking) {
            return redirect()
                ->route('student.bookings.available')
                ->with('error', $this->bookingBlockedMessage($blockingBooking));
        }

        $room->load(['images', 'hostel']);
        $availableBeds = $room->availableBeds()->get();

        if ($availableBeds->isEmpty()) {
            return redirect()->back()->with('error', 'No available beds in this room');
        }

        $periodType = getBookingPeriodType();
        $academicSessions = AcademicSession::where('is_active', true)->get();
        $semesters = Semester::whereHas('academicSession', function($q) {
            $q->where('is_active', true);
        })->get();

        return view('student.bookings.create', compact('room', 'availableBeds', 'periodType', 'academicSessions', 'semesters'));
    }

    public function store(Request $request)
    {
        $blockingBooking = $this->getBlockingBooking();
        if ($blockingBooking) {
            return redirect()
                ->route('student.bookings.available')
                ->with('error', $this->bookingBlockedMessage($blockingBooking));
        }

        // Check if student has profile image
        if (!auth()->user()->profile_image) {
            return redirect()->route('student.bookings.available')
                ->with('error', 'Please upload a profile picture before booking a room. Go to your profile settings.');
        }

        $periodType = getBookingPeriodType();

        if ($periodType === 'months') {
            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'bed_id' => 'nullable|exists:beds,id',
                'check_in_date' => 'required|date|after:today',
                'check_out_date' => 'required|date|after:check_in_date',
            ]);
        } else {
            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'bed_id' => 'nullable|exists:beds,id',
                'semester_id' => 'required|exists:semesters,id',
                'academic_session_id' => 'required|exists:academic_sessions,id',
            ]);

            $semester = Semester::where('id', $validated['semester_id'])
                ->where('academic_session_id', $validated['academic_session_id'])
                ->first();

            if (!$semester) {
                return redirect()->back()->with('error', 'Invalid semester/session selection.');
            }

            $validated['check_in_date'] = $semester->start_date;
            $validated['check_out_date'] = $semester->end_date;
        }

        $room = Room::find($validated['room_id']);
        if (!$room || !$room->is_available) {
            return redirect()->back()->with('error', 'Selected room is not available.');
        }

        if (!empty($validated['bed_id'])) {
            $bed = $room->beds()
                ->where('id', $validated['bed_id'])
                ->where('is_occupied', false)
                ->where('is_approved', true)
                ->first();

            if (!$bed) {
                return redirect()->back()->with('error', 'Selected bed is not available for booking.');
            }
        } else {
            $autoBed = $room->availableBeds()->first();
            $validated['bed_id'] = $autoBed?->id;
        }

        $validated['user_id'] = auth()->id();
        $validated['total_amount'] = $room->price_per_month;

        $booking = Booking::create($validated);
        app(OutboundWebhookService::class)->dispatch('booking.created', [
            'booking_id' => $booking->id,
            'student_id' => $booking->user_id,
            'room_id' => $booking->room_id,
            'bed_id' => $booking->bed_id,
            'status' => $booking->status,
            'total_amount' => $booking->total_amount,
        ]);

        return redirect()->route('student.bookings.show', $booking)->with('success', 'Booking created. Complete payment to conclude your booking.');
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['room.hostel', 'bed', 'payments.createdByAdmin']);
        $activeGateways = PaymentGateway::whereIn('name', ['Paystack', 'Flutterwave', 'Stripe', 'PayPal', 'Razorpay', 'Square'])
            ->where('is_active', true)
            ->get()
            ->keyBy(fn ($gateway) => strtolower($gateway->name));

        return view('student.bookings.show', compact('booking', 'activeGateways'));
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('update', $booking);

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return redirect()->back()->with('error', 'Cannot cancel this booking');
        }

        if ($booking->payments()->exists()) {
            return redirect()->back()->with('error', 'Booking cannot be cancelled after payment has been initiated.');
        }

        $booking->update(['status' => 'cancelled']);
        app(OutboundWebhookService::class)->dispatch('booking.cancelled', [
            'booking_id' => $booking->id,
            'student_id' => $booking->user_id,
            'status' => $booking->status,
        ]);

        return redirect()->route('student.bookings.index')->with('success', 'Booking cancelled successfully');
    }

    public function receipt(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['room.hostel', 'bed', 'payments.createdByAdmin', 'user']);
        
        $pdf = \PDF::loadView('student.bookings.receipt', compact('booking'));
        return $pdf->download('Receipt-' . $booking->id . '.pdf');
    }

    private function getBlockingBooking(): ?Booking
    {
        return auth()->user()
            ->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->first();
    }

    private function bookingBlockedMessage(Booking $booking): string
    {
        if ($booking->status === 'pending') {
            return 'You already have a pending booking. Please pay for it or cancel it before booking another room.';
        }

        return 'You already have an active booking. Apply for hostel change or room change instead of creating another booking.';
    }
}
