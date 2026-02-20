<?php

namespace App\Filament\Pages;

use App\Services\SystemBackupService;
use Filament\Actions\Action;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('createSystemBackup')
                ->label('Create Backup')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->action(function (): void {
                    $backupPath = app(SystemBackupService::class)->createBackup();
                    $fileName = basename($backupPath);

                    Notification::make()
                        ->success()
                        ->title('Backup created successfully')
                        ->body('Database SQL and system files were packed into one zip archive.')
                        ->actions([
                            NotificationAction::make('download')
                                ->label('Download Backup')
                                ->url(route('admin.backups.download', ['file' => $fileName]), shouldOpenInNewTab: true),
                        ])
                        ->send();
                }),
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\AdminStatsOverview::class,
            \App\Filament\Widgets\StaffPayrollOverview::class,
            \App\Filament\Widgets\StaffApprovalQueueWidget::class,
            \App\Filament\Widgets\AssetIssuesOverview::class,
            \App\Filament\Widgets\AssetSubscriptionAlertsWidget::class,
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
