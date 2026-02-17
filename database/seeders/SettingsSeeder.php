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
        $defaultCustomCss = <<<'CSS'
/* Default system style baseline (editable by admin) */
.sidebar-menu a {
    font-weight: 700;
    font-size: 0.98rem;
}

.sidebar-menu a svg {
    width: 1.35rem;
    height: 1.35rem;
    stroke-width: 2.2;
}

.dashboard-card,
main .rounded-lg.shadow-sm,
main .rounded-lg.shadow-md,
main .rounded-xl.shadow-sm,
main .rounded-xl.shadow-md {
    border-radius: 0.65rem;
}

main .shadow-sm {
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
}

main .shadow-md {
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.09);
}

.dark main .shadow-sm,
.dark main .shadow-md {
    box-shadow: 0 2px 10px rgba(2, 6, 23, 0.35);
}
CSS;

        // System Settings
        $settings = [
            'app_name' => 'Hostel Management System',
            'app_description' => 'Complete hostel booking and management solution',
            'primary_color' => '#2563eb',
            'secondary_color' => '#1e40af',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'support_email' => 'support@hostelmanager.com',
            'website_theme' => 'oceanic',
            'homepage_enabled' => '1',
            'custom_css' => $defaultCustomCss,
            'registration_fields_json' => json_encode(['phone']),
            'registration_required_fields_json' => json_encode([]),
            'registration_custom_fields_json' => json_encode([]),
            'sms_http_method' => 'POST',
            'paystack_enabled' => '0',
            'paystack_public_key' => '',
            'paystack_secret_key' => '',
            'flutterwave_enabled' => '0',
            'flutterwave_public_key' => '',
            'flutterwave_secret_key' => '',
            'stripe_enabled' => '0',
            'stripe_public_key' => '',
            'stripe_secret_key' => '',
            'paypal_enabled' => '0',
            'paypal_public_key' => '',
            'paypal_secret_key' => '',
            'paypal_environment' => 'live',
            'razorpay_enabled' => '0',
            'razorpay_public_key' => '',
            'razorpay_secret_key' => '',
            'square_enabled' => '0',
            'square_public_key' => '',
            'square_secret_key' => '',
            'square_location_id' => '',
            'square_environment' => 'live',
            'webhook_enabled' => '0',
            'webhook_url' => '',
            'webhook_secret' => '',
            'webhook_events_json' => json_encode([
                'booking.created',
                'booking.cancelled',
                'booking.manager_approved',
                'booking.manager_rejected',
                'booking.manager_cancelled',
                'payment.completed',
                'complaint.created',
                'complaint.responded',
                'hostel_change.submitted',
                'hostel_change.manager_approved',
                'hostel_change.manager_rejected',
                'hostel_change.admin_approved',
                'hostel_change.admin_rejected',
                'system.webhook_test',
            ]),
            'api_enabled' => '0',
            'api_access_key' => '',
            'sms_payload_template_json' => json_encode([
                'to' => '{phone}',
                'message' => '{message}',
                'sender_id' => '{sender_id}',
                'api_key' => '{api_key}',
            ]),
            'sms_custom_headers_json' => json_encode([]),
            'notification_templates_json' => json_encode([
                'hostel_change.submitted' => [
                    'title' => 'Hostel Change Request Submitted',
                    'message' => 'A hostel change request by {student_name} was submitted and is awaiting manager review.',
                ],
                'hostel_change.manager_approved' => [
                    'title' => 'Hostel Change Manager Approved',
                    'message' => '{actor_name} approved a hostel change request. Awaiting admin approval.',
                ],
                'hostel_change.manager_rejected' => [
                    'title' => 'Hostel Change Rejected by Manager',
                    'message' => '{actor_name} rejected a hostel change request.',
                ],
                'hostel_change.admin_approved' => [
                    'title' => 'Hostel Change Approved',
                    'message' => '{actor_name} approved the hostel change request.',
                ],
                'hostel_change.admin_rejected' => [
                    'title' => 'Hostel Change Rejected by Admin',
                    'message' => '{actor_name} rejected the hostel change request.',
                ],
                'room_change.submitted' => [
                    'title' => 'Room Change Request Submitted',
                    'message' => '{student_name} requested to move from room {current_room} to room {requested_room}.',
                ],
                'room_change.approved' => [
                    'title' => 'Room Change Approved',
                    'message' => '{actor_name} approved room change to {requested_room}.',
                ],
                'room_change.rejected' => [
                    'title' => 'Room Change Rejected',
                    'message' => '{actor_name} rejected room change request to {requested_room}.',
                ],
            ]),
        ];

        foreach ($settings as $key => $value) {
            SystemSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'string']
            );
        }

        // Global header + welcome content defaults
        $websiteContentDefaults = [
            'global_header_logo' => ['', 'string'],
            'global_header_favicon' => ['', 'string'],
            'global_header_brand' => ['Hostel Manager', 'string'],
            'global_header_notice_html' => ['<p><strong>Admissions Open:</strong> Secure your room early for the next term.</p>', 'text'],
            'global_header_contact_email' => ['support@hostelmanager.com', 'string'],
            'global_header_contact_phone' => ['+1 (555) 123-4567', 'string'],
            'global_header_hero_title' => ['Welcome to Hostel Manager', 'string'],
            'global_header_hero_subtitle' => ['Your complete solution for hostel room booking and management', 'text'],
            'global_header_primary_button_text' => ['Sign In', 'string'],
            'global_header_primary_button_url' => ['/login', 'string'],
            'global_header_secondary_button_text' => ['Create Account', 'string'],
            'global_header_secondary_button_url' => ['/register', 'string'],
            'global_header_authenticated_cta_text' => ['Go to Dashboard', 'string'],

            'welcome_body_student_title' => ['For Students', 'string'],
            'welcome_body_student_description' => ['<p>Browse available rooms, create bookings, and manage your accommodation with ease.</p>', 'text'],
            'welcome_body_manager_title' => ['For Managers', 'string'],
            'welcome_body_manager_description' => ['<p>Manage rooms, approve bookings, and monitor occupancy rates efficiently.</p>', 'text'],
            'welcome_body_admin_title' => ['For Admins', 'string'],
            'welcome_body_admin_description' => ['<p>Oversee multiple hostels, assign managers, and track system-wide statistics.</p>', 'text'],

            'global_footer_title' => ['Hostel Manager', 'string'],
            'global_footer_description_html' => ['<p>Professional hostel management system for seamless room booking and resident operations.</p>', 'text'],
            'global_footer_contact_email' => ['info@hostelmanager.com', 'string'],
            'global_footer_contact_phone' => ['+1 (555) 123-4567', 'string'],
            'global_footer_copyright_html' => ['<p>&copy; ' . date('Y') . ' Hostel Manager. All rights reserved.</p>', 'text'],
        ];

        foreach ($websiteContentDefaults as $key => [$value, $type]) {
            SystemSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => $type]
            );
        }

        // Payment Gateways (idempotent)
        PaymentGateway::firstOrCreate(
            ['name' => 'Paystack'],
            [
                'public_key' => null,
                'secret_key' => null,
                'is_active' => false,
                'transaction_fee' => 1.5,
            ]
        );

        PaymentGateway::firstOrCreate(
            ['name' => 'Flutterwave'],
            [
                'public_key' => null,
                'secret_key' => null,
                'is_active' => false,
                'transaction_fee' => 2.0,
            ]
        );

        PaymentGateway::firstOrCreate(
            ['name' => 'Stripe'],
            [
                'public_key' => null,
                'secret_key' => null,
                'is_active' => false,
                'transaction_fee' => 2.9,
            ]
        );

        PaymentGateway::firstOrCreate(
            ['name' => 'PayPal'],
            [
                'public_key' => null,
                'secret_key' => null,
                'is_active' => false,
                'transaction_fee' => 3.5,
            ]
        );

        PaymentGateway::firstOrCreate(
            ['name' => 'Razorpay'],
            [
                'public_key' => null,
                'secret_key' => null,
                'is_active' => false,
                'transaction_fee' => 2.0,
            ]
        );

        PaymentGateway::firstOrCreate(
            ['name' => 'Square'],
            [
                'public_key' => null,
                'secret_key' => null,
                'is_active' => false,
                'transaction_fee' => 2.6,
            ]
        );

        // SMS Providers (idempotent)
        SmsProvider::firstOrCreate(
            ['name' => 'Twilio'],
            [
                'api_key' => null,
                'api_secret' => null,
                'sender_id' => null,
                'is_active' => false,
                'config' => json_encode(['account_sid' => '', 'auth_token' => '']),
            ]
        );

        SmsProvider::firstOrCreate(
            ['name' => 'Termii'],
            [
                'api_key' => null,
                'sender_id' => null,
                'is_active' => false,
                'config' => json_encode(['channel' => 'generic']),
            ]
        );

        SmsProvider::firstOrCreate(
            ['name' => 'Africa\'s Talking'],
            [
                'api_key' => null,
                'api_secret' => null,
                'sender_id' => null,
                'is_active' => false,
                'config' => json_encode(['username' => '', 'api_key' => '']),
            ]
        );
    }
}
