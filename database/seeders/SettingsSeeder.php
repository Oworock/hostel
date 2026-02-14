<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\PaymentGateway;
use App\Models\SmsProvider;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // System Settings
        $settings = [
            'app_name' => 'Hostel Management System',
            'app_description' => 'Complete hostel booking and management solution',
            'primary_color' => '#2563eb',
            'secondary_color' => '#1e40af',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'support_email' => 'support@hostelmanager.com',
        ];

        foreach ($settings as $key => $value) {
            SystemSetting::setSetting($key, $value);
        }

        // Payment Gateways
        PaymentGateway::create([
            'name' => 'Paystack',
            'public_key' => null,
            'secret_key' => null,
            'is_active' => false,
            'transaction_fee' => 1.5,
        ]);

        PaymentGateway::create([
            'name' => 'Flutterwave',
            'public_key' => null,
            'secret_key' => null,
            'is_active' => false,
            'transaction_fee' => 2.0,
        ]);

        // SMS Providers
        SmsProvider::create([
            'name' => 'Twilio',
            'api_key' => null,
            'api_secret' => null,
            'sender_id' => null,
            'is_active' => false,
            'config' => json_encode(['account_sid' => '', 'auth_token' => '']),
        ]);

        SmsProvider::create([
            'name' => 'Termii',
            'api_key' => null,
            'sender_id' => null,
            'is_active' => false,
            'config' => json_encode(['channel' => 'generic']),
        ]);

        SmsProvider::create([
            'name' => 'Africa\'s Talking',
            'api_key' => null,
            'api_secret' => null,
            'sender_id' => null,
            'is_active' => false,
            'config' => json_encode(['username' => '', 'api_key' => '']),
        ]);
    }
}
