<?php

namespace App\Filament\Widgets;

use App\Models\Addon;
use App\Models\AssetSubscription;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema;

class AssetSubscriptionAlertsWidget extends Widget
{
    protected static string $view = 'filament.widgets.asset-subscription-alerts-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin()
            && Addon::isActive('asset-management')
            && Schema::hasTable('asset_subscriptions');
    }

    public function getViewData(): array
    {
        $expiring = AssetSubscription::query()
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', now()->toDateString())
            ->whereDate('expires_at', '<=', now()->addDays(7)->toDateString())
            ->with('hostel')
            ->orderBy('expires_at')
            ->limit(12)
            ->get()
            ->map(function (AssetSubscription $subscription): array {
                return [
                    'name' => $subscription->name,
                    'hostel' => $subscription->hostel?->name,
                    'expires_at' => optional($subscription->expires_at)->format('M d, Y'),
                    'days_remaining' => $subscription->daysRemaining(),
                ];
            });

        $expiredCount = AssetSubscription::query()
            ->where('status', 'active')
            ->whereDate('expires_at', '<', now()->toDateString())
            ->count();

        return [
            'expiring' => $expiring,
            'expiredCount' => $expiredCount,
        ];
    }
}
