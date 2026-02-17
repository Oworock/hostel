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
