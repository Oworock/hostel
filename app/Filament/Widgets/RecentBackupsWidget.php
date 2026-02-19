<?php

namespace App\Filament\Widgets;

use App\Services\SystemBackupService;
use Filament\Widgets\Widget;

class RecentBackupsWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-backups-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getViewData(): array
    {
        return [
            'backups' => app(SystemBackupService::class)->recentBackups(5),
        ];
    }
}
