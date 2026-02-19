<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Booking;
use App\Models\Complaint;
use App\Models\Hostel;
use App\Models\Payment;
use App\Models\Addon;
use App\Models\AssetSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('filament.admin.pages.dashboard');
        } elseif ($user->isManager()) {
            return $this->managerDashboard();
        } elseif ($user->isStudent()) {
            return $this->studentDashboard();
        }

        return $this->studentDashboard();
    }

    public function adminDashboard()
    {
        return redirect()->route('filament.admin.pages.dashboard');
    }

    private function managerDashboard()
    {
        $manager = auth()->user();
        $hostelIds = $manager->managedHostelIds();
        $hostels = Hostel::whereIn('id', $hostelIds)->get();
        if ($hostels->isEmpty()) {
            return redirect('/')->with('error', 'No hostel assigned to your account');
        }
        $roomIds = \App\Models\Room::whereIn('hostel_id', $hostelIds)->pluck('id');
        $bookingsQuery = Booking::whereIn('room_id', $roomIds);
        $paymentsQuery = Payment::whereHas('booking.room', function ($query) use ($hostelIds) {
            $query->whereIn('hostel_id', $hostelIds);
        });
        $complaintsQuery = Schema::hasTable('complaints') && Schema::hasColumn('complaints', 'user_id') && Schema::hasColumn('users', 'hostel_id')
            ? Complaint::whereHas('user', function ($query) use ($hostelIds) {
                $query->whereIn('hostel_id', $hostelIds);
            })
            : Complaint::query()->whereRaw('1 = 0');

        $totalBeds = Bed::whereIn('room_id', $roomIds)->count();
        $occupiedBeds = Bed::whereIn('room_id', $roomIds)->where('is_occupied', true)->count();
        $availableBeds = max(0, $totalBeds - $occupiedBeds);

        $stats = [
            'total_rooms' => \App\Models\Room::whereIn('hostel_id', $hostelIds)->count(),
            'total_students' => \App\Models\User::where('role', 'student')->whereIn('hostel_id', $hostelIds)->count(),
            'occupancy_rate' => $this->calculateOccupancy($hostelIds),
            'total_beds' => $totalBeds,
            'occupied_beds' => $occupiedBeds,
            'available_beds' => $availableBeds,
            'pending_bookings' => (clone $bookingsQuery)->where('status', 'pending')->count(),
            'approved_bookings' => (clone $bookingsQuery)->where('status', 'approved')->count(),
            'open_complaints' => (clone $complaintsQuery)->whereIn('status', ['open', 'in_progress'])->count(),
            'monthly_revenue' => (clone $paymentsQuery)
                ->where('status', 'paid')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'total_revenue' => (clone $paymentsQuery)->where('status', 'paid')->sum('amount'),
        ];

        $recentBookings = (clone $bookingsQuery)
            ->with(['user', 'room', 'bed'])
            ->latest()
            ->limit(8)
            ->get();

        $recentPayments = (clone $paymentsQuery)
            ->with(['booking.room', 'user', 'createdByAdmin'])
            ->latest()
            ->limit(6)
            ->get();

        $manualAdminPayments = (clone $paymentsQuery)
            ->where('is_manual', true)
            ->with(['booking.room', 'user', 'createdByAdmin'])
            ->latest()
            ->limit(6)
            ->get();

        $recentComplaints = (clone $complaintsQuery)
            ->with('user')
            ->latest()
            ->limit(6)
            ->get();

        $roomSnapshot = \App\Models\Room::whereIn('hostel_id', $hostelIds)
            ->withCount([
                'beds as occupied_beds_count' => function ($query) {
                    $query->where('is_occupied', true);
                },
                'beds as total_beds_count',
            ])
            ->orderBy('room_number')
            ->limit(6)
            ->get();

        $hostelLabel = $hostels->count() === 1
            ? ($hostels->first()->name ?? 'Assigned Hostel')
            : $hostels->pluck('name')->join(', ');

        $subscriptionAlerts = collect();
        $expiredSubscriptionsCount = 0;
        if (Addon::isActive('asset-management') && Schema::hasTable('asset_subscriptions')) {
            $subscriptionAlerts = AssetSubscription::query()
                ->whereIn('hostel_id', $hostelIds)
                ->where('status', 'active')
                ->whereDate('expires_at', '<=', now()->addDays(7)->toDateString())
                ->orderBy('expires_at')
                ->with('hostel')
                ->limit(10)
                ->get();

            $expiredSubscriptionsCount = AssetSubscription::query()
                ->whereIn('hostel_id', $hostelIds)
                ->where('status', 'active')
                ->whereDate('expires_at', '<', now()->toDateString())
                ->count();
        }

        return view(
            'manager.dashboard',
            compact('stats', 'recentBookings', 'recentPayments', 'recentComplaints', 'roomSnapshot', 'hostelLabel', 'manualAdminPayments', 'subscriptionAlerts', 'expiredSubscriptionsCount')
        );
    }

    private function studentDashboard()
    {
        $student = auth()->user();
        $student->load('hostel');

        $currentBooking = $student->bookings()
            ->whereIn('status', ['approved', 'pending'])
            ->latest()
            ->first();

        $paidPayments = Payment::where('user_id', $student->id)->where('status', 'paid');
        $openComplaints = Schema::hasTable('complaints') && Schema::hasColumn('complaints', 'user_id')
            ? Complaint::where('user_id', $student->id)->whereIn('status', ['open', 'in_progress'])
            : Complaint::query()->whereRaw('1 = 0');
        $recentBookings = $student->bookings()->with(['room.hostel', 'bed'])->latest()->limit(5)->get();
        $recentPayments = Payment::where('user_id', $student->id)
            ->with('booking.room')
            ->latest()
            ->limit(5)
            ->get();
        $recentComplaints = Schema::hasTable('complaints') && Schema::hasColumn('complaints', 'user_id')
            ? Complaint::where('user_id', $student->id)->latest()->limit(5)->get()
            : collect();

        $currentBookingPaidAmount = $currentBooking
            ? Payment::where('booking_id', $currentBooking->id)->where('status', 'paid')->sum('amount')
            : 0;
        $currentBookingBalance = $currentBooking
            ? max(0, (float) $currentBooking->total_amount - (float) $currentBookingPaidAmount)
            : 0;

        $stats = [
            'active_booking' => $currentBooking ? true : false,
            'pending_bookings' => $student->bookings()->where('status', 'pending')->count(),
            'completed_bookings' => $student->bookings()->where('status', 'completed')->count(),
            'total_paid' => (clone $paidPayments)->sum('amount'),
            'open_complaints' => (clone $openComplaints)->count(),
            'current_booking_balance' => $currentBookingBalance,
            'days_to_checkout' => $currentBooking?->check_out_date
                ? max(0, Carbon::now()->startOfDay()->diffInDays($currentBooking->check_out_date, false))
                : null,
        ];

        $currentHostelName = $student->hostel?->name
            ?? $currentBooking?->room?->hostel?->name;

        return view(
            'student.dashboard',
            compact('stats', 'currentBooking', 'recentBookings', 'recentPayments', 'recentComplaints', 'currentHostelName')
        );
    }

    private function calculateOccupancy($hostelIds)
    {
        $totalBeds = \App\Models\Room::whereIn('hostel_id', $hostelIds)->withCount('beds')->get()->sum('beds_count');
        if ($totalBeds === 0) {
            return 0;
        }

        $occupiedBeds = \App\Models\Bed::whereIn('room_id', \App\Models\Room::whereIn('hostel_id', $hostelIds)->pluck('id'))
            ->where('is_occupied', true)
            ->count();

        return round(($occupiedBeds / $totalBeds) * 100, 2);
    }
}
