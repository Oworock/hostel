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
