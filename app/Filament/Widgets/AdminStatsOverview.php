<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Hostel;
use App\Models\Student;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBookings = Booking::count();
        $activeBookings = Booking::where('status', 'active')->count();
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $totalHostels = Hostel::count();
        $totalStudents = Student::count();
        $totalUsers = \App\Models\User::count();
        
        $currency = config('app.currency', 'NGN');
        $currencySymbol = $this->getCurrencySymbol($currency);

        return [
            Stat::make('Total Hostels', $totalHostels)
                ->description('Active hostel facilities')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('success'),
            
            Stat::make('Total Bookings', $totalBookings)
                ->description('All booking records')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
            
            Stat::make('Active Bookings', $activeBookings)
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('warning'),
            
            Stat::make('Total Revenue', $currencySymbol . number_format($totalRevenue, 2))
                ->description('From all payments')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),
            
            Stat::make('Total Students', $totalStudents)
                ->description('Registered students')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            
            Stat::make('System Users', $totalUsers)
                ->description('Admin + Managers')
                ->descriptionIcon('heroicon-m-users')
                ->color('secondary'),
        ];
    }
    
    private function getCurrencySymbol(string $code): string
    {
        $symbols = [
            'NGN' => '₦',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            'ZAR' => 'R',
        ];
        
        return $symbols[$code] ?? $code;
    }
}
