<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        return redirect('/');
    }

    public function adminDashboard()
    {
        return redirect()->route('filament.admin.pages.dashboard');
    }

    private function managerDashboard()
    {
        $hostel = auth()->user()->hostel;

        if (!$hostel) {
            return redirect('/')->with('error', 'No hostel assigned to your account');
        }

        $stats = [
            'total_rooms' => $hostel->rooms()->count(),
            'total_students' => $hostel->students()->count(),
            'occupancy_rate' => $this->calculateOccupancy($hostel),
            'pending_bookings' => $hostel->bookings()->where('status', 'pending')->count(),
        ];

        $recentBookings = $hostel->bookings()->latest()->limit(10)->get();

        return view('manager.dashboard', compact('stats', 'recentBookings', 'hostel'));
    }

    private function studentDashboard()
    {
        $currentBooking = auth()->user()->bookings()
            ->whereIn('status', ['approved', 'pending'])
            ->latest()
            ->first();

        $stats = [
            'active_booking' => $currentBooking ? true : false,
            'pending_bookings' => auth()->user()->bookings()->where('status', 'pending')->count(),
            'completed_bookings' => auth()->user()->bookings()->where('status', 'completed')->count(),
        ];

        return view('student.dashboard', compact('stats', 'currentBooking'));
    }

    private function calculateOccupancy($hostel)
    {
        $totalBeds = $hostel->rooms()->withCount('beds')->get()->sum('beds_count');
        if ($totalBeds === 0) {
            return 0;
        }

        $occupiedBeds = \App\Models\Bed::whereIn('room_id', $hostel->rooms()->pluck('id'))
            ->where('is_occupied', true)
            ->count();

        return round(($occupiedBeds / $totalBeds) * 100, 2);
    }
}
