<?php

namespace App\Filament\Widgets;

use App\Models\Allocation;
use App\Models\Bed;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Available Beds', Bed::where('is_occupied', false)->count())
                ->description('Beds ready for allocation')
                ->descriptionIcon('heroicon-m-home')
                ->color('success'),

            Stat::make('Active Students', Allocation::where('status', 'Active')->count())
                ->description('Students currently in hostel')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
