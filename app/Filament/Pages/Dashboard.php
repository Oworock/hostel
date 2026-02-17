<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\AdminStatsOverview::class,
            \App\Filament\Widgets\AdminNotificationsWidget::class,
            \App\Filament\Widgets\PaymentSettingsHealthCheck::class,
            \App\Filament\Widgets\BookingChart::class,
            \App\Filament\Widgets\RevenueChart::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }
}
