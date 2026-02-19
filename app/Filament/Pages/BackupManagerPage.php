<?php

namespace App\Filament\Pages;

use App\Services\SystemBackupService;
use Filament\Actions\Action;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BackupManagerPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 8;

    protected static ?string $title = 'Backup Manager';

    protected static ?string $slug = 'system/backups';

    protected static string $view = 'filament.pages.backup-manager-page';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label('Create Backup')
                ->icon('heroicon-o-archive-box')
                ->action(function (): void {
                    $backupPath = app(SystemBackupService::class)->createBackup();
                    $fileName = basename($backupPath);

                    Notification::make()
                        ->success()
                        ->title('Backup created successfully')
                        ->actions([
                            NotificationAction::make('download')
                                ->label('Download')
                                ->url(route('admin.backups.download', ['file' => $fileName]), shouldOpenInNewTab: true),
                        ])
                        ->send();
                }),
        ];
    }

    public function getViewData(): array
    {
        return [
            'backups' => app(SystemBackupService::class)->recentBackups(30),
        ];
    }
}
