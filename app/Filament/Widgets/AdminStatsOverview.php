<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Hostel;
use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    protected function getStats(): array
    {
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $approvedBookings = Booking::where('status', 'approved')->count();
        $totalRevenue = Payment::where('status', 'paid')->sum('amount');
        $totalHostels = Hostel::count();
        $totalStudents = User::where('role', 'student')->count();
        $totalManagers = User::where('role', 'manager')->count();
        
        $currency = get_setting('system_currency', 'NGN');
        $currencySymbol = $this->getCurrencySymbol($currency);

        $revenueCompact = $currencySymbol . formatCompactNumber((float) $totalRevenue, 2);
        $revenueFull = $currencySymbol . number_format((float) $totalRevenue, 2);

        return [
            Stat::make('Total Hostels', $totalHostels)
                ->description('Active hostel facilities')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('success'),
            
            Stat::make('Total Bookings', $totalBookings)
                ->description('All booking records')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
            
            Stat::make('Pending Bookings', $pendingBookings)
                ->description('Awaiting payment/approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Approved Bookings', $approvedBookings)
                ->description('Concluded bookings')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Total Revenue', $revenueCompact)
                ->description('Full amount: ' . $revenueFull)
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),
            
            Stat::make('Total Students', $totalStudents)
                ->description('Registered students')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            
            Stat::make('Total Managers', $totalManagers)
                ->description('Hostel managers')
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
