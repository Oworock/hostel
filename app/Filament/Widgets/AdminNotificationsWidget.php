<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AdminNotificationsWidget extends Widget
{
    protected static string $view = 'filament.widgets.admin-notifications-widget';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $user = auth()->user();

        return [
            'unreadCount' => $user?->unreadNotifications()->count() ?? 0,
            'notifications' => $user?->notifications()->latest()->limit(8)->get() ?? collect(),
        ];
    }

    public function markAllRead(): void
    {
        auth()->user()?->unreadNotifications()->update(['read_at' => now()]);
    }
}

