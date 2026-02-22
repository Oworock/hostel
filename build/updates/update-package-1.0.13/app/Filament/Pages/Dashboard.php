<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use App\Services\SystemBackupService;
use App\Services\SystemUpdateService;
use Filament\Actions\Action;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Dashboard extends BaseDashboard
{
    private const MANIFEST_URL = 'https://oworock.com/hostel/manifest.json';

    protected function getHeaderActions(): array
    {
        $this->notifyIfUpdateAvailable();

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

    private function notifyIfUpdateAvailable(): void
    {
        try {
            $currentVersion = (string) SystemSetting::getSetting('system_app_version', '1.0.0');
            $manifest = Cache::remember(
                'admin:update_manifest:latest',
                now()->addMinutes(10),
                fn (): array => app(SystemUpdateService::class)->fetchRemoteManifest(self::MANIFEST_URL)
            );

            $incomingVersion = trim((string) ($manifest['version'] ?? ''));
            if ($incomingVersion === '' || !version_compare($incomingVersion, $currentVersion, '>')) {
                return;
            }

            $dismissedVersion = (string) session('admin_update_notice_dismissed_version', '');
            if ($dismissedVersion === $incomingVersion) {
                return;
            }

            Notification::make()
                ->warning()
                ->persistent()
                ->title("New update available: v{$incomingVersion}")
                ->body("Current version: v{$currentVersion}. Open System Updates to apply the latest release.")
                ->actions([
                    NotificationAction::make('openUpdateCenter')
                        ->label('Open System Updates')
                        ->url(route('filament.admin.pages.system.updates')),
                    NotificationAction::make('dismissUpdateNotice')
                        ->label('Dismiss')
                        ->url(route('admin.updates.dismiss-notice', ['version' => $incomingVersion])),
                ])
                ->send();
        } catch (Throwable) {
            // Keep dashboard stable if update check fails.
        }
    }
}
