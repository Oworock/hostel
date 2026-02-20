<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Complaint;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Payment;
use App\Policies\ComplaintPolicy;
use App\Policies\BookingPolicy;
use App\Policies\RoomPolicy;
use App\Observers\PaymentObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use App\Models\SystemSetting;
use App\Support\SystemTranslationStore;
use App\Models\SalaryPayment;
use App\Models\StaffMember;
use App\Observers\SalaryPaymentObserver;
use App\Observers\StaffMemberObserver;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Complaint::class => ComplaintPolicy::class,
        Booking::class => BookingPolicy::class,
        Room::class => RoomPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        Payment::observe(PaymentObserver::class);
        SalaryPayment::observe(SalaryPaymentObserver::class);
        StaffMember::observe(StaffMemberObserver::class);

        try {
            $locale = (string) SystemSetting::getSetting('app_locale', config('app.locale', 'en'));
            $fallback = (string) SystemSetting::getSetting('app_fallback_locale', config('app.fallback_locale', 'en'));

            app()->setLocale($locale);
            app()->setFallbackLocale($fallback);
            config(['app.locale' => $locale, 'app.fallback_locale' => $fallback]);

            $rtlLocales = ['ar', 'he', 'ur', 'fa'];
            View::share('isRtlLocale', in_array($locale, $rtlLocales, true));

            $rows = SystemTranslationStore::read();

            if (!empty($rows)) {
                $grouped = [];
                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $targetLocale = trim((string) ($row['locale'] ?? ''));
                    $key = trim((string) ($row['key'] ?? ''));
                    $value = (string) ($row['value'] ?? '');
                    if ($targetLocale === '' || $key === '' || $value === '') {
                        continue;
                    }
                    $grouped[$targetLocale][$key] = $value;
                }

                foreach ($grouped as $targetLocale => $lines) {
                    Lang::addLines($lines, $targetLocale);
                }
            }
        } catch (\Throwable $e) {
            // Ignore localization bootstrap errors to avoid breaking app startup.
        }
    }
}
