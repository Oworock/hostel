<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Complaint;
use App\Models\Hostel;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManagementApiController extends Controller
{
    public function health()
    {
        return response()->json([
            'ok' => true,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function listHostels()
    {
        return response()->json(Hostel::query()->latest()->paginate(20));
    }

    public function createHostel(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'price_per_month' => ['nullable', 'numeric', 'min:0'],
            'total_capacity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $hostel = Hostel::create($data);

        return response()->json($hostel, 201);
    }

    public function updateHostel(Request $request, Hostel $hostel)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:50'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'price_per_month' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'total_capacity' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $hostel->update($data);

        return response()->json($hostel->fresh());
    }

    public function deleteHostel(Hostel $hostel)
    {
        $hostel->delete();

        return response()->json(['message' => 'Hostel deleted.']);
    }

    public function listRooms()
    {
        return response()->json(Room::query()->with('hostel')->latest()->paginate(20));
    }

    public function createRoom(Request $request)
    {
        $data = $request->validate([
            'hostel_id' => ['required', 'exists:hostels,id'],
            'room_number' => ['required', 'string', 'max:50'],
            'type' => ['required', 'in:single,double,triple,quad,other'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price_per_month' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $room = Room::create($data);

        return response()->json($room->fresh('hostel'), 201);
    }

    public function updateRoom(Request $request, Room $room)
    {
        $data = $request->validate([
            'hostel_id' => ['sometimes', 'exists:hostels,id'],
            'room_number' => ['sometimes', 'string', 'max:50'],
            'type' => ['sometimes', 'in:single,double,triple,quad,other'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'price_per_month' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        $room->update($data);

        return response()->json($room->fresh('hostel'));
    }

    public function deleteRoom(Room $room)
    {
        $room->delete();

        return response()->json(['message' => 'Room deleted.']);
    }

    public function listStudents()
    {
        return response()->json(
            User::query()
                ->where('role', 'student')
                ->with('hostel')
                ->latest()
                ->paginate(20)
        );
    }

    public function createStudent(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'hostel_id' => ['nullable', 'exists:hostels,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $student = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? str()->random(16)),
            'role' => 'student',
            'phone' => $data['phone'] ?? null,
            'hostel_id' => $data['hostel_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json($student, 201);
    }

    public function updateStudent(Request $request, User $student)
    {
        abort_unless($student->role === 'student', 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student->id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'hostel_id' => ['sometimes', 'nullable', 'exists:hostels,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('password', $data) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $student->update($data);

        return response()->json($student->fresh());
    }

    public function deleteStudent(User $student)
    {
        abort_unless($student->role === 'student', 404);
        $student->delete();

        return response()->json(['message' => 'Student deleted.']);
    }

    public function listBookings()
    {
        return response()->json(Booking::query()->with(['user', 'room.hostel', 'bed'])->latest()->paginate(20));
    }

    public function createBooking(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'bed_id' => ['nullable', 'exists:beds,id'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['nullable', 'date', 'after_or_equal:check_in_date'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
            'status' => ['nullable', 'in:pending,approved,rejected,completed,cancelled'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $booking = Booking::create($data);

        return response()->json($booking->fresh(['user', 'room.hostel', 'bed']), 201);
    }

    public function updateBooking(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['sometimes', 'in:pending,approved,rejected,completed,cancelled'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'check_in_date' => ['sometimes', 'date'],
            'check_out_date' => ['sometimes', 'nullable', 'date'],
        ]);

        $booking->update($data);

        return response()->json($booking->fresh(['user', 'room.hostel', 'bed']));
    }

    public function listPayments()
    {
        return response()->json(Payment::query()->with(['booking.room.hostel', 'user'])->latest()->paginate(20));
    }

    public function createPayment(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,paid,failed,refunded'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = Payment::create($data);

        return response()->json($payment->fresh(['booking.room.hostel', 'user']), 201);
    }

    public function updatePayment(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'status' => ['sometimes', 'in:pending,paid,failed,refunded'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'payment_date' => ['sometimes', 'nullable', 'date'],
            'transaction_id' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $payment->update($data);

        return response()->json($payment->fresh(['booking.room.hostel', 'user']));
    }

    public function listComplaints()
    {
        return response()->json(Complaint::query()->with(['user', 'booking.room.hostel'])->latest()->paginate(20));
    }

    public function updateComplaint(Request $request, Complaint $complaint)
    {
        $data = $request->validate([
            'status' => ['sometimes', 'in:open,in_progress,resolved,closed'],
            'response' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $complaint->update($data);

        return response()->json($complaint->fresh(['user', 'booking.room.hostel']));
    }
}
