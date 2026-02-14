<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\Hostel;
use App\Models\Student;
use App\Models\User;
use App\Models\Payment;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            return [
                \App\Filament\Widgets\AdminStatsOverview::class,
                \App\Filament\Widgets\BookingChart::class,
                \App\Filament\Widgets\RevenueChart::class,
            ];
        } elseif ($user->role === 'manager') {
            return [
                \App\Filament\Widgets\ManagerStatsOverview::class,
                \App\Filament\Widgets\ManagerBookingChart::class,
            ];
        } else {
            return [
                \App\Filament\Widgets\StudentStatsOverview::class,
            ];
        }
    }
}
