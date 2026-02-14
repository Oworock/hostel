<?php

namespace App\Helpers;

use App\Models\SystemSetting;

class SystemHelper
{
    public static function getSettings(): array
    {
        return [
            'app_name' => SystemSetting::getSetting('app_name', config('app.name')),
            'app_email' => SystemSetting::getSetting('app_email', config('mail.from.address')),
            'app_phone' => SystemSetting::getSetting('app_phone', config('app.phone')),
            'sms_provider' => SystemSetting::getSetting('sms_provider', 'none'),
            'sms_url' => SystemSetting::getSetting('sms_url', ''),
            'sms_api_key' => SystemSetting::getSetting('sms_api_key', ''),
            'sms_sender_id' => SystemSetting::getSetting('sms_sender_id', ''),
            'sms_message_template' => SystemSetting::getSetting('sms_message_template', ''),
            'paystack_public_key' => SystemSetting::getSetting('paystack_public_key', ''),
            'paystack_secret_key' => SystemSetting::getSetting('paystack_secret_key', ''),
            'flutterwave_public_key' => SystemSetting::getSetting('flutterwave_public_key', ''),
            'flutterwave_secret_key' => SystemSetting::getSetting('flutterwave_secret_key', ''),
        ];
    }

    public static function getSetting($key, $default = null)
    {
        return SystemSetting::getSetting($key, $default);
    }
}
