<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\Hostel;
use App\Models\Bed;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ManagerStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $manager = auth()->user();
        $hostels = Hostel::where('owner_id', $manager->id)->pluck('id');
        
        $totalBeds = Bed::whereIn('hostel_id', $hostels)->count();
        $occupiedBeds = Bed::whereIn('hostel_id', $hostels)->where('is_occupied', true)->count();
        $availableBeds = $totalBeds - $occupiedBeds;
        
        $totalBookings = \App\Models\Booking::whereIn('hostel_id', $hostels)->count();
        $pendingBookings = \App\Models\Booking::whereIn('hostel_id', $hostels)->where('status', 'pending')->count();
        
        $totalRevenue = \App\Models\Payment::whereIn('hostel_id', $hostels)->where('status', 'completed')->sum('amount');

        return [
            Stat::make('Total Beds', $totalBeds)
                ->description('All beds in your hostels')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),
            
            Stat::make('Available Beds', $availableBeds)
                ->description('Ready for booking')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Occupied Beds', $occupiedBeds)
                ->description('Currently booked')
                ->descriptionIcon('heroicon-m-user')
                ->color('warning'),
            
            Stat::make('Pending Bookings', $pendingBookings)
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('secondary'),
            
            Stat::make('Total Revenue', '₦' . number_format($totalRevenue, 2))
                ->description('From all bookings')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),
        ];
    }
}
