<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\AssetSubscription;
use App\Models\AssetSubscriptionNotificationLog;
use App\Models\User;
use App\Notifications\SystemEventNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class AssetSubscriptionNotificationService
{
    public function sendDueNotifications(): void
    {
        if (!Addon::isActive('asset-management')) {
            return;
        }

        if (!Schema::hasTable('asset_subscriptions') || !Schema::hasTable('asset_subscription_notification_logs')) {
            return;
        }

        $checkpoints = [7, 5, 3, 1];
        $today = Carbon::now()->startOfDay();

        AssetSubscription::query()
            ->where('status', 'active')
            ->whereDate('expires_at', '>=', $today)
            ->with(['hostel.managers', 'hostel'])
            ->chunkById(100, function ($subscriptions) use ($checkpoints, $today): void {
                foreach ($subscriptions as $subscription) {
                    $daysRemaining = $today->diffInDays($subscription->expires_at, false);
                    if (!in_array($daysRemaining, $checkpoints, true)) {
                        continue;
                    }

                    $recipients = User::query()
                        ->where(fn ($q) => $q->where('role', 'admin')->orWhere('role', 'super_admin'))
                        ->orWhereIn('id', $subscription->hostel?->managers?->pluck('id')->all() ?? [])
                        ->get();

                    foreach ($recipients as $recipient) {
                        $alreadySent = AssetSubscriptionNotificationLog::query()
                            ->where('asset_subscription_id', $subscription->id)
                            ->where('user_id', $recipient->id)
                            ->where('days_remaining', $daysRemaining)
                            ->exists();

                        if ($alreadySent) {
                            continue;
                        }

                        $recipient->notify(new SystemEventNotification(
                            event: 'asset_subscription_expiry',
                            title: 'Subscription Expiry Alert',
                            message: sprintf(
                                '%s for %s expires in %d day(s).',
                                $subscription->name,
                                $subscription->hostel?->name ?? 'Unknown Hostel',
                                $daysRemaining
                            ),
                            payload: [
                                'subscription_id' => $subscription->id,
                                'hostel_id' => $subscription->hostel_id,
                                'days_remaining' => $daysRemaining,
                                'expires_at' => optional($subscription->expires_at)->toDateString(),
                            ],
                        ));

                        app(OutboundWebhookService::class)->dispatch('asset.subscription.expiry_alert', [
                            'asset_subscription_id' => $subscription->id,
                            'hostel_id' => $subscription->hostel_id,
                            'days_remaining' => $daysRemaining,
                            'expires_at' => optional($subscription->expires_at)->toDateString(),
                            'user_id' => $recipient->id,
                        ]);

                        AssetSubscriptionNotificationLog::create([
                            'asset_subscription_id' => $subscription->id,
                            'user_id' => $recipient->id,
                            'days_remaining' => $daysRemaining,
                            'notified_at' => now(),
                        ]);
                    }
                }
            });
    }
}
