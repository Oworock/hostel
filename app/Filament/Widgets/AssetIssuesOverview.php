<?php

namespace App\Filament\Widgets;

use App\Models\Addon;
use App\Models\AssetIssue;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class AssetIssuesOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin()
            && Addon::isActive('asset-management')
            && Schema::hasTable('asset_issues');
    }

    protected function getStats(): array
    {
        $open = AssetIssue::where('status', 'open')->count();
        $inProgress = AssetIssue::where('status', 'in_progress')->count();
        $resolved = AssetIssue::where('status', 'resolved')->count();

        return [
            Stat::make('Asset Issues (Open)', $open)
                ->description('Newly reported')
                ->color('danger')
                ->descriptionIcon('heroicon-m-exclamation-triangle'),
            Stat::make('Asset Issues (In Progress)', $inProgress)
                ->description('Under investigation')
                ->color('warning')
                ->descriptionIcon('heroicon-m-wrench-screwdriver'),
            Stat::make('Asset Issues (Resolved)', $resolved)
                ->description('Closed reports')
                ->color('success')
                ->descriptionIcon('heroicon-m-check-circle'),
        ];
    }
}
