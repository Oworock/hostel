<?php

namespace App\Helpers;

use App\Models\SystemSetting;

class SystemHelper
{
    public static function getSettings(): array
    {
        $payloadTemplate = json_decode(SystemSetting::getSetting('sms_payload_template_json', ''), true);
        $customHeaders = json_decode(SystemSetting::getSetting('sms_custom_headers_json', ''), true);

        return [
            'app_name' => SystemSetting::getSetting('app_name', config('app.name')),
            'app_email' => SystemSetting::getSetting('app_email', config('mail.from.address')),
            'app_phone' => SystemSetting::getSetting('app_phone', config('app.phone')),
            'sms_provider' => SystemSetting::getSetting('sms_provider', 'none'),
            'sms_url' => SystemSetting::getSetting('sms_url', ''),
            'sms_http_method' => SystemSetting::getSetting('sms_http_method', 'POST'),
            'sms_api_key' => SystemSetting::getSetting('sms_api_key', ''),
            'sms_sender_id' => SystemSetting::getSetting('sms_sender_id', ''),
            'sms_message_template' => SystemSetting::getSetting('sms_message_template', ''),
            'sms_payload_template' => is_array($payloadTemplate) ? $payloadTemplate : [],
            'sms_custom_headers' => is_array($customHeaders) ? $customHeaders : [],
            'paystack_public_key' => SystemSetting::getSetting('paystack_public_key', ''),
            'paystack_secret_key' => SystemSetting::getSetting('paystack_secret_key', ''),
            'flutterwave_public_key' => SystemSetting::getSetting('flutterwave_public_key', ''),
            'flutterwave_secret_key' => SystemSetting::getSetting('flutterwave_secret_key', ''),
            'stripe_public_key' => SystemSetting::getSetting('stripe_public_key', ''),
            'stripe_secret_key' => SystemSetting::getSetting('stripe_secret_key', ''),
            'paypal_public_key' => SystemSetting::getSetting('paypal_public_key', ''),
            'paypal_secret_key' => SystemSetting::getSetting('paypal_secret_key', ''),
            'paypal_environment' => SystemSetting::getSetting('paypal_environment', 'live'),
            'razorpay_public_key' => SystemSetting::getSetting('razorpay_public_key', ''),
            'razorpay_secret_key' => SystemSetting::getSetting('razorpay_secret_key', ''),
            'square_public_key' => SystemSetting::getSetting('square_public_key', ''),
            'square_secret_key' => SystemSetting::getSetting('square_secret_key', ''),
            'square_location_id' => SystemSetting::getSetting('square_location_id', ''),
            'square_environment' => SystemSetting::getSetting('square_environment', 'live'),
            'webhook_enabled' => SystemSetting::getSetting('webhook_enabled', false),
            'webhook_url' => SystemSetting::getSetting('webhook_url', ''),
            'webhook_events_json' => SystemSetting::getSetting('webhook_events_json', '[]'),
            'api_enabled' => SystemSetting::getSetting('api_enabled', false),
            'api_access_key' => SystemSetting::getSetting('api_access_key', ''),
        ];
    }

    public static function getSetting($key, $default = null)
    {
        return SystemSetting::getSetting($key, $default);
    }
}
