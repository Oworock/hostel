<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Illuminate\Support\Str;

class AddonLifecycleService
{
    public function activate(Addon $addon): void
    {
        if ($addon->slug === 'asset-management') {
            $this->runAssetManagementMigrations();
        }
        if ($addon->slug === 'staff-payroll') {
            $this->runStaffPayrollMigrations();
            $this->initializeStaffPayrollDefaults();
        }
        if ($addon->slug === 'referral-system') {
            $this->initializeReferralDefaults();
        }

        $addon->forceFill([
            'is_active' => true,
            'installed_at' => now(),
        ])->save();

        app(OutboundWebhookService::class)->dispatch('addon.activated', [
            'addon_id' => $addon->id,
            'slug' => $addon->slug,
            'name' => $addon->name,
            'version' => $addon->version,
        ]);
    }

    public function deactivate(Addon $addon): void
    {
        $addon->forceFill(['is_active' => false])->save();

        app(OutboundWebhookService::class)->dispatch('addon.deactivated', [
            'addon_id' => $addon->id,
            'slug' => $addon->slug,
            'name' => $addon->name,
            'version' => $addon->version,
        ]);
    }

    private function runAssetManagementMigrations(): void
    {
        $path = base_path('database/addons/asset-management/migrations');
        if (!is_dir($path)) {
            throw new RuntimeException('Asset management migration path is missing.');
        }

        Artisan::call('migrate', [
            '--path' => $path,
            '--realpath' => true,
            '--force' => true,
        ]);
    }

    public function purgeAddonData(Addon $addon): void
    {
        if ($addon->slug === 'asset-management') {
            $this->purgeAssetManagementData();
        }
        if ($addon->slug === 'staff-payroll') {
            $this->purgeStaffPayrollData();
        }
        if ($addon->slug === 'referral-system') {
            $this->purgeReferralSettings();
        }
    }

    private function runStaffPayrollMigrations(): void
    {
        $path = base_path('database/addons/staff-payroll/migrations');
        if (!is_dir($path)) {
            throw new RuntimeException('Staff payroll migration path is missing.');
        }

        Artisan::call('migrate', [
            '--path' => $path,
            '--realpath' => true,
            '--force' => true,
        ]);
    }

    private function purgeAssetManagementData(): void
    {
        $this->deleteAssetManagementImages();

        if (Schema::hasTable('asset_issues')) {
            Schema::drop('asset_issues');
        }

        if (Schema::hasTable('asset_subscription_notification_logs')) {
            Schema::drop('asset_subscription_notification_logs');
        }

        if (Schema::hasTable('asset_subscriptions')) {
            Schema::drop('asset_subscriptions');
        }

        if (Schema::hasTable('asset_movements')) {
            Schema::drop('asset_movements');
        }

        if (Schema::hasTable('assets')) {
            Schema::drop('assets');
        }

        DB::table('migrations')
            ->whereIn('migration', [
                '2026_02_19_000001_create_assets_table',
                '2026_02_19_000002_create_asset_issues_table',
                '2026_02_19_000003_add_extended_columns_to_assets_table',
                '2026_02_19_000004_create_asset_movements_table',
                '2026_02_19_000005_create_asset_subscriptions_table',
                '2026_02_19_000006_create_asset_subscription_notification_logs_table',
                '2026_02_19_000007_add_supplier_invoice_maintenance_to_assets_table',
            ])
            ->delete();
    }

    private function deleteAssetManagementImages(): void
    {
        if (!Schema::hasTable('assets')) {
            return;
        }

        DB::table('assets')
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->filter()
            ->each(fn ($path) => \Illuminate\Support\Facades\Storage::disk('public')->delete((string) $path));
    }

    private function purgeStaffPayrollData(): void
    {
        if (Schema::hasTable('salary_payments')) {
            Schema::drop('salary_payments');
        }

        if (Schema::hasTable('staff_members')) {
            Schema::drop('staff_members');
        }

        DB::table('migrations')
            ->whereIn('migration', [
                '2026_02_20_000001_create_staff_members_table',
                '2026_02_20_000002_create_salary_payments_table',
                '2026_02_20_000003_add_profile_and_user_link_to_staff_members_table',
                '2026_02_20_000004_add_assignment_and_approval_fields_to_staff_members_table',
                '2026_02_20_000005_add_category_to_staff_members_table',
            ])
            ->delete();

        DB::table('system_settings')
            ->whereIn('key', [
                'staff_payroll_registration_enabled',
                'staff_payroll_registration_token',
                'staff_payroll_email_notifications_enabled',
                'staff_payroll_sms_notifications_enabled',
                'staff_payroll_salary_paid_email_template',
                'staff_payroll_salary_paid_sms_template',
                'staff_payroll_suspended_email_template',
                'staff_payroll_suspended_sms_template',
                'staff_payroll_sacked_email_template',
                'staff_payroll_sacked_sms_template',
                'staff_payroll_active_email_template',
                'staff_payroll_active_sms_template',
                'staff_payroll_id_card_email_template',
                'staff_payroll_registration_intro',
                'staff_payroll_registration_show_department',
                'staff_payroll_registration_require_department',
                'staff_payroll_registration_show_job_title',
                'staff_payroll_registration_require_job_title',
                'staff_payroll_registration_show_category',
                'staff_payroll_registration_require_category',
                'staff_payroll_registration_show_address',
                'staff_payroll_registration_require_address',
                'staff_payroll_registration_show_profile_image',
                'staff_payroll_registration_require_profile_image',
                'staff_payroll_registration_show_hostel_selector',
                'staff_payroll_registration_require_hostel_selector',
                'staff_payroll_registration_label_full_name',
                'staff_payroll_registration_label_email',
                'staff_payroll_registration_label_phone',
                'staff_payroll_registration_label_bank_name',
                'staff_payroll_registration_label_bank_account_name',
                'staff_payroll_registration_label_bank_account_number',
                'staff_payroll_registration_label_department',
                'staff_payroll_registration_label_job_title',
                'staff_payroll_registration_label_category',
                'staff_payroll_registration_label_address',
                'staff_payroll_registration_label_profile_image',
                'staff_payroll_registration_label_assigned_hostel',
                'staff_payroll_registration_label_general_staff',
                'staff_payroll_departments_csv',
                'staff_payroll_categories_csv',
                'staff_payroll_id_card_title',
                'staff_payroll_id_card_subtitle',
                'staff_payroll_id_card_footer',
                'staff_payroll_id_card_show_email',
                'staff_payroll_id_card_show_phone',
                'staff_payroll_id_card_show_department',
                'staff_payroll_id_card_use_custom_brand',
                'staff_payroll_id_card_brand_name',
                'staff_payroll_id_card_brand_logo',
                'staff_payroll_id_card_background_template',
                'staff_payroll_id_card_png_scale',
                'staff_payroll_id_card_layout_json',
                'staff_payroll_registration_custom_fields_json',
            ])
            ->delete();
    }

    private function initializeStaffPayrollDefaults(): void
    {
        if ((string) SystemSetting::getSetting('staff_payroll_registration_token', '') === '') {
            SystemSetting::setSetting('staff_payroll_registration_token', Str::random(40));
        }

        SystemSetting::setSetting(
            'staff_payroll_registration_enabled',
            filter_var(SystemSetting::getSetting('staff_payroll_registration_enabled', true), FILTER_VALIDATE_BOOL) ? '1' : '0'
        );
        SystemSetting::setSetting(
            'staff_payroll_email_notifications_enabled',
            filter_var(SystemSetting::getSetting('staff_payroll_email_notifications_enabled', true), FILTER_VALIDATE_BOOL) ? '1' : '0'
        );
        SystemSetting::setSetting(
            'staff_payroll_sms_notifications_enabled',
            filter_var(SystemSetting::getSetting('staff_payroll_sms_notifications_enabled', false), FILTER_VALIDATE_BOOL) ? '1' : '0'
        );
        SystemSetting::setSetting(
            'staff_payroll_salary_paid_email_template',
            (string) SystemSetting::getSetting('staff_payroll_salary_paid_email_template', 'Hello {name}, your salary of {amount} for {month} {year} has been paid. Reference: {reference}. View payslip: {payslip_link}')
        );
        SystemSetting::setSetting(
            'staff_payroll_salary_paid_sms_template',
            (string) SystemSetting::getSetting('staff_payroll_salary_paid_sms_template', 'Salary paid: {amount} for {month} {year}. Ref: {reference}. Payslip: {payslip_link}')
        );
        SystemSetting::setSetting(
            'staff_payroll_suspended_email_template',
            (string) SystemSetting::getSetting('staff_payroll_suspended_email_template', 'Hello {name}, your staff profile has been suspended.')
        );
        SystemSetting::setSetting(
            'staff_payroll_suspended_sms_template',
            (string) SystemSetting::getSetting('staff_payroll_suspended_sms_template', 'Your staff profile has been suspended.')
        );
        SystemSetting::setSetting(
            'staff_payroll_sacked_email_template',
            (string) SystemSetting::getSetting('staff_payroll_sacked_email_template', 'Hello {name}, your staff profile has been marked as sacked.')
        );
        SystemSetting::setSetting(
            'staff_payroll_sacked_sms_template',
            (string) SystemSetting::getSetting('staff_payroll_sacked_sms_template', 'Your staff profile has been marked as sacked.')
        );
        SystemSetting::setSetting(
            'staff_payroll_active_email_template',
            (string) SystemSetting::getSetting('staff_payroll_active_email_template', 'Hello {name}, your staff profile is now active.')
        );
        SystemSetting::setSetting(
            'staff_payroll_active_sms_template',
            (string) SystemSetting::getSetting('staff_payroll_active_sms_template', 'Your staff profile is now active.')
        );
        SystemSetting::setSetting(
            'staff_payroll_id_card_email_template',
            (string) SystemSetting::getSetting('staff_payroll_id_card_email_template', 'Hello {name}, attached is your staff ID card.')
        );
        SystemSetting::setSetting(
            'staff_payroll_registration_intro',
            (string) SystemSetting::getSetting('staff_payroll_registration_intro', 'Fill your details below. Your record will be reviewed by the administrator.')
        );
        SystemSetting::setSetting('staff_payroll_registration_show_department', filter_var(SystemSetting::getSetting('staff_payroll_registration_show_department', true), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_department', filter_var(SystemSetting::getSetting('staff_payroll_registration_require_department', false), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_show_job_title', filter_var(SystemSetting::getSetting('staff_payroll_registration_show_job_title', true), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_job_title', filter_var(SystemSetting::getSetting('staff_payroll_registration_require_job_title', false), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_show_category', filter_var(SystemSetting::getSetting('staff_payroll_registration_show_category', true), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_category', filter_var(SystemSetting::getSetting('staff_payroll_registration_require_category', false), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_show_address', filter_var(SystemSetting::getSetting('staff_payroll_registration_show_address', true), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_address', filter_var(SystemSetting::getSetting('staff_payroll_registration_require_address', false), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_show_profile_image', filter_var(SystemSetting::getSetting('staff_payroll_registration_show_profile_image', true), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_profile_image', filter_var(SystemSetting::getSetting('staff_payroll_registration_require_profile_image', false), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_show_hostel_selector', filter_var(SystemSetting::getSetting('staff_payroll_registration_show_hostel_selector', true), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_hostel_selector', filter_var(SystemSetting::getSetting('staff_payroll_registration_require_hostel_selector', false), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_label_full_name', (string) SystemSetting::getSetting('staff_payroll_registration_label_full_name', 'Full Name'));
        SystemSetting::setSetting('staff_payroll_registration_label_email', (string) SystemSetting::getSetting('staff_payroll_registration_label_email', 'Email'));
        SystemSetting::setSetting('staff_payroll_registration_label_phone', (string) SystemSetting::getSetting('staff_payroll_registration_label_phone', 'Phone'));
        SystemSetting::setSetting('staff_payroll_registration_label_bank_name', (string) SystemSetting::getSetting('staff_payroll_registration_label_bank_name', 'Bank Name'));
        SystemSetting::setSetting('staff_payroll_registration_label_bank_account_name', (string) SystemSetting::getSetting('staff_payroll_registration_label_bank_account_name', 'Account Name'));
        SystemSetting::setSetting('staff_payroll_registration_label_bank_account_number', (string) SystemSetting::getSetting('staff_payroll_registration_label_bank_account_number', 'Account Number'));
        SystemSetting::setSetting('staff_payroll_registration_label_department', (string) SystemSetting::getSetting('staff_payroll_registration_label_department', 'Department'));
        SystemSetting::setSetting('staff_payroll_registration_label_job_title', (string) SystemSetting::getSetting('staff_payroll_registration_label_job_title', 'Job Title'));
        SystemSetting::setSetting('staff_payroll_registration_label_category', (string) SystemSetting::getSetting('staff_payroll_registration_label_category', 'Category'));
        SystemSetting::setSetting('staff_payroll_registration_label_address', (string) SystemSetting::getSetting('staff_payroll_registration_label_address', 'Address'));
        SystemSetting::setSetting('staff_payroll_registration_label_profile_image', (string) SystemSetting::getSetting('staff_payroll_registration_label_profile_image', 'Passport Photo'));
        SystemSetting::setSetting('staff_payroll_registration_label_assigned_hostel', (string) SystemSetting::getSetting('staff_payroll_registration_label_assigned_hostel', 'Assigned Hostel'));
        SystemSetting::setSetting('staff_payroll_registration_label_general_staff', (string) SystemSetting::getSetting('staff_payroll_registration_label_general_staff', 'I am a general staff (all hostels)'));
        SystemSetting::setSetting('staff_payroll_departments_csv', (string) SystemSetting::getSetting('staff_payroll_departments_csv', ''));
        SystemSetting::setSetting('staff_payroll_categories_csv', (string) SystemSetting::getSetting('staff_payroll_categories_csv', ''));
        SystemSetting::setSetting('staff_payroll_id_card_title', (string) SystemSetting::getSetting('staff_payroll_id_card_title', 'STAFF ID CARD'));
        SystemSetting::setSetting('staff_payroll_id_card_subtitle', (string) SystemSetting::getSetting('staff_payroll_id_card_subtitle', ''));
        SystemSetting::setSetting('staff_payroll_id_card_footer', (string) SystemSetting::getSetting('staff_payroll_id_card_footer', ''));
        SystemSetting::setSetting('staff_payroll_id_card_show_email', filter_var(SystemSetting::getSetting('staff_payroll_id_card_show_email', true), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_id_card_show_phone', filter_var(SystemSetting::getSetting('staff_payroll_id_card_show_phone', true), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_id_card_show_department', filter_var(SystemSetting::getSetting('staff_payroll_id_card_show_department', true), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_id_card_use_custom_brand', filter_var(SystemSetting::getSetting('staff_payroll_id_card_use_custom_brand', false), FILTER_VALIDATE_BOOL) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_id_card_brand_name', (string) SystemSetting::getSetting('staff_payroll_id_card_brand_name', ''));
        SystemSetting::setSetting('staff_payroll_id_card_brand_logo', (string) SystemSetting::getSetting('staff_payroll_id_card_brand_logo', ''));
        SystemSetting::setSetting('staff_payroll_id_card_background_template', (string) SystemSetting::getSetting('staff_payroll_id_card_background_template', ''));
        $pngScale = (string) SystemSetting::getSetting('staff_payroll_id_card_png_scale', '2');
        if (!in_array($pngScale, ['2', '3'], true)) {
            $pngScale = '2';
        }
        SystemSetting::setSetting('staff_payroll_id_card_png_scale', $pngScale);
        SystemSetting::setSetting('staff_payroll_id_card_layout_json', (string) SystemSetting::getSetting('staff_payroll_id_card_layout_json', '[]'));
        SystemSetting::setSetting('staff_payroll_registration_custom_fields_json', (string) SystemSetting::getSetting('staff_payroll_registration_custom_fields_json', '[]'));

        app(StaffDirectorySyncService::class)->syncCoreUsers();
    }

    private function initializeReferralDefaults(): void
    {
        if ((string) SystemSetting::getSetting('referral_partner_invite_token', '') === '') {
            SystemSetting::setSetting('referral_partner_invite_token', Str::random(24));
        }

        SystemSetting::setSetting(
            'referral_enabled',
            filter_var(SystemSetting::getSetting('referral_enabled', true), FILTER_VALIDATE_BOOL) ? '1' : '0'
        );
        SystemSetting::setSetting(
            'referral_students_can_be_agents',
            filter_var(SystemSetting::getSetting('referral_students_can_be_agents', true), FILTER_VALIDATE_BOOL) ? '1' : '0'
        );
        $defaultType = (string) SystemSetting::getSetting('referral_default_commission_type', 'percentage');
        if (!in_array($defaultType, ['percentage', 'fixed'], true)) {
            $defaultType = 'percentage';
        }
        SystemSetting::setSetting('referral_default_commission_type', $defaultType);
        SystemSetting::setSetting('referral_default_commission_value', (string) ((float) SystemSetting::getSetting('referral_default_commission_value', 5)));
        SystemSetting::setSetting('referral_min_payout', (string) ((float) SystemSetting::getSetting('referral_min_payout', 0)));
        SystemSetting::setSetting(
            'referral_notify_email',
            filter_var(SystemSetting::getSetting('referral_notify_email', true), FILTER_VALIDATE_BOOL) ? '1' : '0'
        );
        SystemSetting::setSetting(
            'referral_notify_sms',
            filter_var(SystemSetting::getSetting('referral_notify_sms', false), FILTER_VALIDATE_BOOL) ? '1' : '0'
        );
        SystemSetting::setSetting(
            'referral_notify_student_registered_email_subject',
            (string) SystemSetting::getSetting('referral_notify_student_registered_email_subject', 'New referral registration')
        );
        SystemSetting::setSetting(
            'referral_notify_student_registered_email_template',
            (string) SystemSetting::getSetting('referral_notify_student_registered_email_template', 'A new student registered with your referral link: {student_name} ({student_email}).')
        );
        SystemSetting::setSetting(
            'referral_notify_student_registered_sms_template',
            (string) SystemSetting::getSetting('referral_notify_student_registered_sms_template', 'New referral signup: {student_name} ({student_email}).')
        );
        SystemSetting::setSetting(
            'referral_notify_commission_email_subject',
            (string) SystemSetting::getSetting('referral_notify_commission_email_subject', 'Referral commission earned')
        );
        SystemSetting::setSetting(
            'referral_notify_commission_email_template',
            (string) SystemSetting::getSetting('referral_notify_commission_email_template', 'A referred student completed payment. Commission earned: {commission_amount}. Booking #{booking_id}.')
        );
        SystemSetting::setSetting(
            'referral_notify_commission_sms_template',
            (string) SystemSetting::getSetting('referral_notify_commission_sms_template', 'Commission earned: {commission_amount} for booking #{booking_id}.')
        );
        SystemSetting::setSetting(
            'referral_notify_payout_email_subject',
            (string) SystemSetting::getSetting('referral_notify_payout_email_subject', 'Referral payout update')
        );
        SystemSetting::setSetting(
            'referral_notify_payout_email_template',
            (string) SystemSetting::getSetting('referral_notify_payout_email_template', 'Your payout request for {payout_amount} is now {payout_status}.')
        );
        SystemSetting::setSetting(
            'referral_notify_payout_sms_template',
            (string) SystemSetting::getSetting('referral_notify_payout_sms_template', 'Payout {payout_amount} status: {payout_status}.')
        );
        SystemSetting::setSetting(
            'referral_popup_enabled',
            filter_var(SystemSetting::getSetting('referral_popup_enabled', false), FILTER_VALIDATE_BOOL) ? '1' : '0'
        );
        SystemSetting::setSetting('referral_popup_title', (string) SystemSetting::getSetting('referral_popup_title', ''));
        SystemSetting::setSetting('referral_popup_body', (string) SystemSetting::getSetting('referral_popup_body', ''));
        SystemSetting::setSetting('referral_popup_start_at', (string) SystemSetting::getSetting('referral_popup_start_at', ''));
        SystemSetting::setSetting('referral_popup_end_at', (string) SystemSetting::getSetting('referral_popup_end_at', ''));
    }

    private function purgeReferralSettings(): void
    {
        DB::table('system_settings')
            ->whereIn('key', [
                'referral_enabled',
                'referral_students_can_be_agents',
                'referral_default_commission_type',
                'referral_default_commission_value',
                'referral_min_payout',
                'referral_notify_email',
                'referral_notify_sms',
                'referral_partner_invite_token',
                'referral_notify_student_registered_email_subject',
                'referral_notify_student_registered_email_template',
                'referral_notify_student_registered_sms_template',
                'referral_notify_commission_email_subject',
                'referral_notify_commission_email_template',
                'referral_notify_commission_sms_template',
                'referral_notify_payout_email_subject',
                'referral_notify_payout_email_template',
                'referral_notify_payout_sms_template',
                'referral_popup_enabled',
                'referral_popup_title',
                'referral_popup_body',
                'referral_popup_start_at',
                'referral_popup_end_at',
            ])
            ->delete();
    }
}
