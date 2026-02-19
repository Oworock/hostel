<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\AssetSubscriptionNotificationService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('subscriptions:notify-expiring', function (AssetSubscriptionNotificationService $service) {
    $service->sendDueNotifications();
    $this->info('Subscription expiry notifications processed.');
})->purpose('Send 7/5/3/1-day expiry notifications for intangible asset subscriptions.');

Schedule::command('subscriptions:notify-expiring')->dailyAt('08:00');
