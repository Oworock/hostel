<?php

use App\Helpers\SystemHelper;
use App\Support\CurrencyCatalog;

if (! function_exists('get_system_settings')) {
    function get_system_settings()
    {
        return SystemHelper::getSettings();
    }
}

if (! function_exists('get_setting')) {
    function get_setting($key, $default = null)
    {
        return SystemHelper::getSetting($key, $default);
    }
}

if (! function_exists('getCurrencySymbol')) {
    function getCurrencySymbol()
    {
        $currency = SystemHelper::getSetting('system_currency', 'NGN');

        return CurrencyCatalog::symbol($currency);
    }
}

if (! function_exists('getBookingPeriodType')) {
    function getBookingPeriodType()
    {
        return SystemHelper::getSetting('booking_period_type', 'months');
    }
}

if (! function_exists('getBookingPeriodLabel')) {
    function getBookingPeriodLabel(bool $plural = false): string
    {
        return match (getBookingPeriodType()) {
            'semesters' => $plural ? 'semesters' : 'semester',
            'sessions' => $plural ? 'sessions' : 'session',
            default => $plural ? 'months' : 'month',
        };
    }
}

if (! function_exists('getBookingPriceSuffix')) {
    function getBookingPriceSuffix(): string
    {
        return '/'.getBookingPeriodLabel(false);
    }
}

if (! function_exists('formatCompactNumber')) {
    function formatCompactNumber(float|int $value, int $precision = 1): string
    {
        $abs = abs((float) $value);
        $suffixes = [
            1_000_000_000_000 => 'T',
            1_000_000_000 => 'B',
            1_000_000 => 'M',
            1_000 => 'K',
        ];

        foreach ($suffixes as $threshold => $suffix) {
            if ($abs >= $threshold) {
                $scaled = $value / $threshold;
                $formatted = number_format($scaled, $precision);
                $formatted = rtrim(rtrim($formatted, '0'), '.');
                return $formatted . $suffix;
            }
        }

        return number_format((float) $value, 2);
    }
}

if (! function_exists('formatCurrency')) {
    function formatCurrency(float|int|string|null $value, bool $compact = true, int $precision = 2): string
    {
        if ($value === null || $value === '') {
            return getCurrencySymbol() . ($compact ? '0' : '0.00');
        }

        $amount = (float) $value;

        return getCurrencySymbol() . ($compact
            ? formatCompactNumber($amount, $precision)
            : number_format($amount, 2));
    }
}
