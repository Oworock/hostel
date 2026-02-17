<?php

use App\Helpers\SystemHelper;

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
        
        $symbols = [
            'NGN' => '₦',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'INR' => '₹',
            'ZAR' => 'R',
        ];
        
        return $symbols[$currency] ?? $currency;
    }
}

if (! function_exists('getBookingPeriodType')) {
    function getBookingPeriodType()
    {
        return SystemHelper::getSetting('booking_period_type', 'months');
    }
}
