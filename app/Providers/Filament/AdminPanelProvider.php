<?php

namespace App\Providers\Filament;

use App\Models\SystemSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\View\PanelsRenderHook;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\UserProfile;
use App\Filament\Pages\SendSMS;
use App\Filament\Pages\SendEmail;
use App\Filament\Pages\SystemSettings;
use App\Filament\Pages\FileManagerPage;
use App\Filament\Pages\BackupManagerPage;
use App\Filament\Pages\StaffPayrollSettings;
use App\Filament\Pages\ReferralSettings;
use App\Filament\Pages\SystemUpdatePage;
use App\Filament\Pages\Dashboard as AdminDashboard;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn () => (string) SystemSetting::getSetting('global_header_brand', SystemSetting::getSetting('app_name', config('app.name', 'Hostel Manager'))))
            ->brandLogo(function () {
                $logoLight = (string) SystemSetting::getSetting('global_header_logo_light', SystemSetting::getSetting('global_header_logo', SystemSetting::getSetting('app_logo', '')));
                $fallback = (string) SystemSetting::getSetting('global_header_favicon', $logoLight);
                $toUrl = static function (string $path): string {
                    $path = trim($path);
                    if ($path === '') {
                        return '';
                    }
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
                        return $path;
                    }

                    $path = ltrim($path, '/');
                    $path = preg_replace('/^(storage\/|public\/)/', '', $path);

                    return asset('storage/' . $path);
                };

                if ($logoLight === '' && $fallback !== '') {
                    $logoLight = $fallback;
                }
                if ($logoLight === '') {
                    return null;
                }

                return $toUrl($logoLight);
            })
            ->darkModeBrandLogo(function () {
                $logoLight = (string) SystemSetting::getSetting('global_header_logo_light', SystemSetting::getSetting('global_header_logo', SystemSetting::getSetting('app_logo', '')));
                $logoDark = (string) SystemSetting::getSetting('global_header_logo_dark', $logoLight);
                $fallback = (string) SystemSetting::getSetting('global_header_favicon', $logoLight);
                $path = trim($logoDark !== '' ? $logoDark : ($logoLight !== '' ? $logoLight : $fallback));

                if ($path === '') {
                    return null;
                }

                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
                    return $path;
                }

                $path = ltrim($path, '/');
                $path = preg_replace('/^(storage\/|public\/)/', '', $path);

                return asset('storage/' . $path);
            })
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => view('filament.partials.topbar-notifications')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.partials.intl-phone-script')
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([
                // Add explicit resources here and remove ones that conflict with pages
            ])
            // Keep explicit pages to avoid duplicates from legacy pages.
            ->pages([
                AdminDashboard::class,
                UserProfile::class,
                SendSMS::class,
                SendEmail::class,
                SystemSettings::class,
                StaffPayrollSettings::class,
                ReferralSettings::class,
                FileManagerPage::class,
                BackupManagerPage::class,
                SystemUpdatePage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
