<?php

namespace App\Filament\Pages;

use App\Models\Addon;
use App\Models\SystemSetting;
use App\Services\StaffDirectorySyncService;
use App\Services\SmsGatewayService;
use App\Services\StaffPayrollNotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Throwable;

class StaffPayrollSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?string $navigationLabel = 'Staff Payroll Settings';

    protected static ?int $navigationSort = 12;

    protected static ?string $title = 'Staff Payroll Settings';

    protected static string $view = 'filament.pages.staff-payroll-settings';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() && Addon::isActive('staff-payroll');
    }

    public function mount(): void
    {
        abort_unless(static::shouldRegisterNavigation(), 403);
        if (Schema::hasTable('staff_members')) {
            try {
                app(StaffDirectorySyncService::class)->syncCoreUsers();
            } catch (Throwable $e) {
                // Keep settings available even when staff schema updates are still pending.
            }
        }

        $token = (string) SystemSetting::getSetting('staff_payroll_registration_token', '');
        if ($token === '') {
            $token = Str::random(40);
            SystemSetting::setSetting('staff_payroll_registration_token', $token);
        }

        $this->form->fill([
            'staff_payroll_registration_enabled' => filter_var(SystemSetting::getSetting('staff_payroll_registration_enabled', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_token' => $token,
            'staff_payroll_email_notifications_enabled' => filter_var(SystemSetting::getSetting('staff_payroll_email_notifications_enabled', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_sms_notifications_enabled' => filter_var(SystemSetting::getSetting('staff_payroll_sms_notifications_enabled', false), FILTER_VALIDATE_BOOL),
            'staff_payroll_salary_paid_email_template' => (string) SystemSetting::getSetting('staff_payroll_salary_paid_email_template', 'Hello {name}, your salary of {amount} for {month} {year} has been paid. Reference: {reference}. View payslip: {payslip_link}'),
            'staff_payroll_salary_paid_sms_template' => (string) SystemSetting::getSetting('staff_payroll_salary_paid_sms_template', 'Salary paid: {amount} for {month} {year}. Ref: {reference}. Payslip: {payslip_link}'),
            'staff_payroll_suspended_email_template' => (string) SystemSetting::getSetting('staff_payroll_suspended_email_template', 'Hello {name}, your staff profile has been suspended.'),
            'staff_payroll_suspended_sms_template' => (string) SystemSetting::getSetting('staff_payroll_suspended_sms_template', 'Your staff profile has been suspended.'),
            'staff_payroll_sacked_email_template' => (string) SystemSetting::getSetting('staff_payroll_sacked_email_template', 'Hello {name}, your staff profile has been marked as sacked.'),
            'staff_payroll_sacked_sms_template' => (string) SystemSetting::getSetting('staff_payroll_sacked_sms_template', 'Your staff profile has been marked as sacked.'),
            'staff_payroll_active_email_template' => (string) SystemSetting::getSetting('staff_payroll_active_email_template', 'Hello {name}, your staff profile is now active.'),
            'staff_payroll_active_sms_template' => (string) SystemSetting::getSetting('staff_payroll_active_sms_template', 'Your staff profile is now active.'),
            'staff_payroll_id_card_email_template' => (string) SystemSetting::getSetting('staff_payroll_id_card_email_template', 'Hello {name}, attached is your staff ID card.'),
            'staff_payroll_registration_intro' => (string) SystemSetting::getSetting('staff_payroll_registration_intro', 'Fill your details below. Your record will be reviewed by the administrator.'),
            'staff_payroll_registration_show_department' => filter_var(SystemSetting::getSetting('staff_payroll_registration_show_department', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_require_department' => filter_var(SystemSetting::getSetting('staff_payroll_registration_require_department', false), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_show_job_title' => filter_var(SystemSetting::getSetting('staff_payroll_registration_show_job_title', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_require_job_title' => filter_var(SystemSetting::getSetting('staff_payroll_registration_require_job_title', false), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_show_category' => filter_var(SystemSetting::getSetting('staff_payroll_registration_show_category', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_require_category' => filter_var(SystemSetting::getSetting('staff_payroll_registration_require_category', false), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_show_address' => filter_var(SystemSetting::getSetting('staff_payroll_registration_show_address', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_require_address' => filter_var(SystemSetting::getSetting('staff_payroll_registration_require_address', false), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_show_profile_image' => filter_var(SystemSetting::getSetting('staff_payroll_registration_show_profile_image', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_require_profile_image' => filter_var(SystemSetting::getSetting('staff_payroll_registration_require_profile_image', false), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_show_hostel_selector' => filter_var(SystemSetting::getSetting('staff_payroll_registration_show_hostel_selector', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_require_hostel_selector' => filter_var(SystemSetting::getSetting('staff_payroll_registration_require_hostel_selector', false), FILTER_VALIDATE_BOOL),
            'staff_payroll_registration_label_full_name' => (string) SystemSetting::getSetting('staff_payroll_registration_label_full_name', 'Full Name'),
            'staff_payroll_registration_label_email' => (string) SystemSetting::getSetting('staff_payroll_registration_label_email', 'Email'),
            'staff_payroll_registration_label_phone' => (string) SystemSetting::getSetting('staff_payroll_registration_label_phone', 'Phone'),
            'staff_payroll_registration_label_bank_name' => (string) SystemSetting::getSetting('staff_payroll_registration_label_bank_name', 'Bank Name'),
            'staff_payroll_registration_label_bank_account_name' => (string) SystemSetting::getSetting('staff_payroll_registration_label_bank_account_name', 'Account Name'),
            'staff_payroll_registration_label_bank_account_number' => (string) SystemSetting::getSetting('staff_payroll_registration_label_bank_account_number', 'Account Number'),
            'staff_payroll_registration_label_department' => (string) SystemSetting::getSetting('staff_payroll_registration_label_department', 'Department'),
            'staff_payroll_registration_label_job_title' => (string) SystemSetting::getSetting('staff_payroll_registration_label_job_title', 'Job Title'),
            'staff_payroll_registration_label_category' => (string) SystemSetting::getSetting('staff_payroll_registration_label_category', 'Category'),
            'staff_payroll_registration_label_address' => (string) SystemSetting::getSetting('staff_payroll_registration_label_address', 'Address'),
            'staff_payroll_registration_label_profile_image' => (string) SystemSetting::getSetting('staff_payroll_registration_label_profile_image', 'Passport Photo'),
            'staff_payroll_registration_label_assigned_hostel' => (string) SystemSetting::getSetting('staff_payroll_registration_label_assigned_hostel', 'Assigned Hostel'),
            'staff_payroll_registration_label_general_staff' => (string) SystemSetting::getSetting('staff_payroll_registration_label_general_staff', 'I am a general staff (all hostels)'),
            'staff_payroll_departments_csv' => (string) SystemSetting::getSetting('staff_payroll_departments_csv', ''),
            'staff_payroll_categories_csv' => (string) SystemSetting::getSetting('staff_payroll_categories_csv', ''),
            'staff_payroll_id_card_title' => (string) SystemSetting::getSetting('staff_payroll_id_card_title', 'STAFF ID CARD'),
            'staff_payroll_id_card_subtitle' => (string) SystemSetting::getSetting('staff_payroll_id_card_subtitle', ''),
            'staff_payroll_id_card_footer' => (string) SystemSetting::getSetting('staff_payroll_id_card_footer', ''),
            'staff_payroll_id_card_show_email' => filter_var(SystemSetting::getSetting('staff_payroll_id_card_show_email', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_id_card_show_phone' => filter_var(SystemSetting::getSetting('staff_payroll_id_card_show_phone', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_id_card_show_department' => filter_var(SystemSetting::getSetting('staff_payroll_id_card_show_department', true), FILTER_VALIDATE_BOOL),
            'staff_payroll_id_card_use_custom_brand' => filter_var(SystemSetting::getSetting('staff_payroll_id_card_use_custom_brand', false), FILTER_VALIDATE_BOOL),
            'staff_payroll_id_card_brand_name' => (string) SystemSetting::getSetting('staff_payroll_id_card_brand_name', ''),
            'staff_payroll_id_card_brand_logo' => (string) SystemSetting::getSetting('staff_payroll_id_card_brand_logo', ''),
            'staff_payroll_id_card_background_template' => (string) SystemSetting::getSetting('staff_payroll_id_card_background_template', ''),
            'staff_payroll_id_card_png_scale' => (string) SystemSetting::getSetting('staff_payroll_id_card_png_scale', '2'),
            'staff_payroll_id_card_layout_json' => $this->decodeJsonArraySetting('staff_payroll_id_card_layout_json'),
            'staff_payroll_registration_custom_fields' => $this->decodeJsonArraySetting('staff_payroll_registration_custom_fields_json'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('staff_payroll_tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Registration')
                            ->schema([
                                Forms\Components\Section::make('Staff Registration Link')
                                    ->schema([
                                        Forms\Components\Toggle::make('staff_payroll_registration_enabled')
                                            ->label('Enable hidden staff registration link')
                                            ->default(true),
                                        Forms\Components\TextInput::make('staff_payroll_registration_token')
                                            ->label('Registration token')
                                            ->required()
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('regenerate')
                                                    ->label('Regenerate')
                                                    ->action(function (Forms\Set $set): void {
                                                        $set('staff_payroll_registration_token', Str::random(40));
                                                    })
                                            ),
                                        Forms\Components\Placeholder::make('registration_link')
                                            ->label('Registration URL')
                                            ->content(function (Forms\Get $get): string {
                                                $token = trim((string) $get('staff_payroll_registration_token'));

                                                return url('/staff/register/' . $token);
                                            }),
                                    ]),
                                Forms\Components\Section::make('Registration Form Builder')
                                    ->description('Control base fields and add custom fields shown on the staff registration page.')
                                    ->schema([
                                        Forms\Components\Textarea::make('staff_payroll_registration_intro')
                                            ->label('Form Intro Text')
                                            ->rows(2),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('staff_payroll_registration_show_department')->label('Show Department'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_require_department')->label('Require Department'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_show_job_title')->label('Show Job Title'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_require_job_title')->label('Require Job Title'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_show_category')->label('Show Category'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_require_category')->label('Require Category'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_show_address')->label('Show Address'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_require_address')->label('Require Address'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_show_profile_image')->label('Show Passport Photo'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_require_profile_image')->label('Require Passport Photo'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_show_hostel_selector')->label('Show Hostel Assignment'),
                                                Forms\Components\Toggle::make('staff_payroll_registration_require_hostel_selector')->label('Require Hostel if not General'),
                                            ]),
                                        Forms\Components\Textarea::make('staff_payroll_departments_csv')
                                            ->label('Predefined Departments (comma separated)')
                                            ->rows(2)
                                            ->placeholder('Administration, Security, Cleaning'),
                                        Forms\Components\Textarea::make('staff_payroll_categories_csv')
                                            ->label('Predefined Categories (comma separated)')
                                            ->rows(2)
                                            ->placeholder('Full-time, Part-time, Contract'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_full_name')->label('Label: Full Name'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_email')->label('Label: Email'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_phone')->label('Label: Phone'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_bank_name')->label('Label: Bank Name'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_bank_account_name')->label('Label: Account Name'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_bank_account_number')->label('Label: Account Number'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_department')->label('Label: Department'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_job_title')->label('Label: Job Title'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_category')->label('Label: Category'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_address')->label('Label: Address'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_profile_image')->label('Label: Passport Photo'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_assigned_hostel')->label('Label: Assigned Hostel'),
                                        Forms\Components\TextInput::make('staff_payroll_registration_label_general_staff')->label('Label: General Staff Checkbox'),
                                        Forms\Components\Repeater::make('staff_payroll_registration_custom_fields')
                                            ->label('Custom Registration Fields')
                                            ->formatStateUsing(fn (mixed $state): array => $this->normalizeRepeaterState($state))
                                            ->afterStateHydrated(function (Forms\Components\Repeater $component, mixed $state): void {
                                                $component->state($this->normalizeRepeaterState($state));
                                            })
                                            ->schema([
                                                Forms\Components\TextInput::make('key')
                                                    ->label('Field Key')
                                                    ->helperText('Use lowercase letters, numbers and underscore only.')
                                                    ->required(),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Field Label')
                                                    ->required(),
                                                Forms\Components\Select::make('type')
                                                    ->options([
                                                        'text' => 'Text',
                                                        'textarea' => 'Textarea',
                                                        'email' => 'Email',
                                                        'number' => 'Number',
                                                        'date' => 'Date',
                                                        'select' => 'Select',
                                                    ])
                                                    ->required()
                                                    ->default('text'),
                                                Forms\Components\TextInput::make('options')
                                                    ->label('Options (comma separated, select only)'),
                                                Forms\Components\TextInput::make('placeholder')
                                                    ->label('Placeholder'),
                                                Forms\Components\Toggle::make('required')
                                                    ->label('Required')
                                                    ->default(false),
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Notifications')
                            ->schema([
                                Forms\Components\Section::make('Salary Paid Notifications')
                                    ->schema([
                                        Forms\Components\Toggle::make('staff_payroll_email_notifications_enabled')
                                            ->label('Send email to staff when salary is paid')
                                            ->default(true),
                                        Forms\Components\Toggle::make('staff_payroll_sms_notifications_enabled')
                                            ->label('Send SMS to staff when salary is paid')
                                            ->default(false),
                                    ]),
                                Forms\Components\Section::make('Notification Prompts')
                                    ->description('Supported placeholders: {name}, {email}, {phone}, {department}, {job_title}, {status}, {amount}, {month}, {year}, {reference}, {payslip_link}, {app_name}')
                                    ->schema([
                                        Forms\Components\Textarea::make('staff_payroll_salary_paid_email_template')->label('Salary Paid (Email)')->rows(3),
                                        Forms\Components\Textarea::make('staff_payroll_salary_paid_sms_template')->label('Salary Paid (SMS)')->rows(2),
                                        Forms\Components\Textarea::make('staff_payroll_suspended_email_template')->label('Suspended (Email)')->rows(3),
                                        Forms\Components\Textarea::make('staff_payroll_suspended_sms_template')->label('Suspended (SMS)')->rows(2),
                                        Forms\Components\Textarea::make('staff_payroll_sacked_email_template')->label('Sacked (Email)')->rows(3),
                                        Forms\Components\Textarea::make('staff_payroll_sacked_sms_template')->label('Sacked (SMS)')->rows(2),
                                        Forms\Components\Textarea::make('staff_payroll_active_email_template')->label('Reactivated (Email)')->rows(3),
                                        Forms\Components\Textarea::make('staff_payroll_active_sms_template')->label('Reactivated (SMS)')->rows(2),
                                        Forms\Components\Textarea::make('staff_payroll_id_card_email_template')->label('ID Card Email Body')->rows(3),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('ID Card')
                            ->schema([
                                Forms\Components\Section::make('ID Card Layout')
                                    ->description('The platform logo from system branding is used automatically on staff ID cards.')
                                    ->schema([
                                        Forms\Components\Toggle::make('staff_payroll_id_card_use_custom_brand')
                                            ->label('Use Custom Brand Name and Logo')
                                            ->live(),
                                        Forms\Components\TextInput::make('staff_payroll_id_card_brand_name')
                                            ->label('Custom Brand Name')
                                            ->visible(fn (Forms\Get $get): bool => (bool) $get('staff_payroll_id_card_use_custom_brand')),
                                        Forms\Components\FileUpload::make('staff_payroll_id_card_brand_logo')
                                            ->label('Custom Brand Logo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('staff/id-card-branding')
                                            ->maxSize(4096)
                                            ->visible(fn (Forms\Get $get): bool => (bool) $get('staff_payroll_id_card_use_custom_brand')),
                                        Forms\Components\TextInput::make('staff_payroll_id_card_title')->label('Card Title'),
                                        Forms\Components\TextInput::make('staff_payroll_id_card_subtitle')->label('Card Subtitle'),
                                        Forms\Components\TextInput::make('staff_payroll_id_card_footer')->label('Card Footer Note'),
                                        Forms\Components\Toggle::make('staff_payroll_id_card_show_email')->label('Show Email')->default(true),
                                        Forms\Components\Toggle::make('staff_payroll_id_card_show_phone')->label('Show Phone')->default(true),
                                        Forms\Components\Toggle::make('staff_payroll_id_card_show_department')->label('Show Department')->default(true),
                                        Forms\Components\FileUpload::make('staff_payroll_id_card_background_template')
                                            ->label('Template Background Image (optional)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('staff/id-card-templates')
                                            ->maxSize(8192),
                                        Forms\Components\Select::make('staff_payroll_id_card_png_scale')
                                            ->label('PNG Export Quality')
                                            ->options([
                                                '2' => '2x (good)',
                                                '3' => '3x (best)',
                                            ])
                                            ->default('2')
                                            ->native(false),
                                        Forms\Components\Hidden::make('staff_payroll_id_card_layout_json')
                                            ->dehydrated(true)
                                            ->default([
                                                ['key' => '__logo', 'x' => 545, 'y' => 32, 'width' => 170, 'height' => 34, 'locked' => false],
                                                ['key' => '__photo', 'x' => 545, 'y' => 75, 'width' => 130, 'height' => 160, 'locked' => false],
                                                ['key' => '__app_name', 'x' => 55, 'y' => 72, 'size' => 24, 'color' => '#e2e8f0', 'weight' => 700, 'locked' => false],
                                                ['key' => '__card_title', 'x' => 55, 'y' => 105, 'size' => 16, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
                                                ['key' => 'full_name', 'x' => 55, 'y' => 162, 'size' => 30, 'color' => '#ffffff', 'weight' => 700, 'locked' => false],
                                                ['key' => 'job_title', 'x' => 55, 'y' => 198, 'size' => 16, 'color' => '#cbd5e1', 'weight' => 500, 'locked' => false],
                                                ['key' => '__label_staff_code', 'x' => 55, 'y' => 252, 'size' => 14, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
                                                ['key' => '__label_department', 'x' => 55, 'y' => 280, 'size' => 14, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
                                                ['key' => '__label_email', 'x' => 55, 'y' => 308, 'size' => 14, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
                                                ['key' => '__label_phone', 'x' => 55, 'y' => 336, 'size' => 14, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
                                                ['key' => '__label_joined', 'x' => 55, 'y' => 364, 'size' => 14, 'color' => '#93c5fd', 'weight' => 600, 'locked' => false],
                                                ['key' => 'employee_code', 'x' => 185, 'y' => 252, 'size' => 14, 'color' => '#ffffff', 'weight' => 600, 'locked' => false],
                                                ['key' => 'department', 'x' => 185, 'y' => 280, 'size' => 14, 'color' => '#ffffff', 'weight' => 600, 'locked' => false],
                                                ['key' => 'email', 'x' => 185, 'y' => 308, 'size' => 14, 'color' => '#ffffff', 'weight' => 600, 'locked' => false],
                                                ['key' => 'phone', 'x' => 185, 'y' => 336, 'size' => 14, 'color' => '#ffffff', 'weight' => 600, 'locked' => false],
                                                ['key' => 'joined_on', 'x' => 185, 'y' => 364, 'size' => 14, 'color' => '#ffffff', 'weight' => 600, 'locked' => false],
                                            ])
                                            ->formatStateUsing(fn (mixed $state): array => $this->normalizeRepeaterState($state)),
                                        Forms\Components\View::make('filament.forms.id-card-canvas-builder')
                                            ->columnSpanFull(),
                                        Forms\Components\Placeholder::make('id_card_builder_tip')
                                            ->label('Preview')
                                            ->content(function (Forms\Get $get): HtmlString {
                                                $title = e((string) ($get('staff_payroll_id_card_title') ?: 'STAFF ID CARD'));
                                                $subtitle = e((string) ($get('staff_payroll_id_card_subtitle') ?: ''));
                                                $footer = e((string) ($get('staff_payroll_id_card_footer') ?: ''));
                                                $template = $this->normalizeStoredFileValue($get('staff_payroll_id_card_background_template'));
                                                $templateUrl = $template !== '' ? asset('storage/' . ltrim((string) preg_replace('/^(storage\/|public\/)/', '', $template), '/')) : '';

                                                $style = $templateUrl !== ''
                                                    ? "background-image:url('{$templateUrl}');background-size:cover;background-position:center;"
                                                    : 'background:linear-gradient(140deg,#0f172a 0%,#1e3a8a 100%);';

                                                $html = '<div style="max-width:540px;border-radius:12px;padding:16px;color:#fff;' . $style . '">'
                                                    . '<div style="font-size:18px;font-weight:700;">Sample Staff Name</div>'
                                                    . '<div style="font-size:12px;color:#cbd5e1;">' . $title . '</div>'
                                                    . ($subtitle !== '' ? '<div style="font-size:11px;color:#dbeafe;">' . $subtitle . '</div>' : '')
                                                    . '<div style="margin-top:10px;font-size:12px;">Code: 1234 | Department: Admin | Phone: 08000000000</div>'
                                                    . ($footer !== '' ? '<div style="margin-top:18px;font-size:10px;color:#cbd5e1;">' . $footer . '</div>' : '')
                                                    . '</div>';

                                                return new HtmlString($html);
                                            }),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SystemSetting::setSetting('staff_payroll_registration_enabled', !empty($data['staff_payroll_registration_enabled']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_token', (string) ($data['staff_payroll_registration_token'] ?? ''));
        SystemSetting::setSetting('staff_payroll_email_notifications_enabled', !empty($data['staff_payroll_email_notifications_enabled']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_sms_notifications_enabled', !empty($data['staff_payroll_sms_notifications_enabled']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_salary_paid_email_template', (string) ($data['staff_payroll_salary_paid_email_template'] ?? ''));
        SystemSetting::setSetting('staff_payroll_salary_paid_sms_template', (string) ($data['staff_payroll_salary_paid_sms_template'] ?? ''));
        SystemSetting::setSetting('staff_payroll_suspended_email_template', (string) ($data['staff_payroll_suspended_email_template'] ?? ''));
        SystemSetting::setSetting('staff_payroll_suspended_sms_template', (string) ($data['staff_payroll_suspended_sms_template'] ?? ''));
        SystemSetting::setSetting('staff_payroll_sacked_email_template', (string) ($data['staff_payroll_sacked_email_template'] ?? ''));
        SystemSetting::setSetting('staff_payroll_sacked_sms_template', (string) ($data['staff_payroll_sacked_sms_template'] ?? ''));
        SystemSetting::setSetting('staff_payroll_active_email_template', (string) ($data['staff_payroll_active_email_template'] ?? ''));
        SystemSetting::setSetting('staff_payroll_active_sms_template', (string) ($data['staff_payroll_active_sms_template'] ?? ''));
        SystemSetting::setSetting('staff_payroll_id_card_email_template', (string) ($data['staff_payroll_id_card_email_template'] ?? ''));
        SystemSetting::setSetting('staff_payroll_registration_intro', (string) ($data['staff_payroll_registration_intro'] ?? ''));
        SystemSetting::setSetting('staff_payroll_registration_show_department', !empty($data['staff_payroll_registration_show_department']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_department', !empty($data['staff_payroll_registration_require_department']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_show_job_title', !empty($data['staff_payroll_registration_show_job_title']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_job_title', !empty($data['staff_payroll_registration_require_job_title']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_show_category', !empty($data['staff_payroll_registration_show_category']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_category', !empty($data['staff_payroll_registration_require_category']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_show_address', !empty($data['staff_payroll_registration_show_address']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_address', !empty($data['staff_payroll_registration_require_address']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_show_profile_image', !empty($data['staff_payroll_registration_show_profile_image']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_profile_image', !empty($data['staff_payroll_registration_require_profile_image']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_show_hostel_selector', !empty($data['staff_payroll_registration_show_hostel_selector']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_require_hostel_selector', !empty($data['staff_payroll_registration_require_hostel_selector']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_registration_label_full_name', (string) ($data['staff_payroll_registration_label_full_name'] ?? 'Full Name'));
        SystemSetting::setSetting('staff_payroll_registration_label_email', (string) ($data['staff_payroll_registration_label_email'] ?? 'Email'));
        SystemSetting::setSetting('staff_payroll_registration_label_phone', (string) ($data['staff_payroll_registration_label_phone'] ?? 'Phone'));
        SystemSetting::setSetting('staff_payroll_registration_label_bank_name', (string) ($data['staff_payroll_registration_label_bank_name'] ?? 'Bank Name'));
        SystemSetting::setSetting('staff_payroll_registration_label_bank_account_name', (string) ($data['staff_payroll_registration_label_bank_account_name'] ?? 'Account Name'));
        SystemSetting::setSetting('staff_payroll_registration_label_bank_account_number', (string) ($data['staff_payroll_registration_label_bank_account_number'] ?? 'Account Number'));
        SystemSetting::setSetting('staff_payroll_registration_label_department', (string) ($data['staff_payroll_registration_label_department'] ?? 'Department'));
        SystemSetting::setSetting('staff_payroll_registration_label_job_title', (string) ($data['staff_payroll_registration_label_job_title'] ?? 'Job Title'));
        SystemSetting::setSetting('staff_payroll_registration_label_category', (string) ($data['staff_payroll_registration_label_category'] ?? 'Category'));
        SystemSetting::setSetting('staff_payroll_registration_label_address', (string) ($data['staff_payroll_registration_label_address'] ?? 'Address'));
        SystemSetting::setSetting('staff_payroll_registration_label_profile_image', (string) ($data['staff_payroll_registration_label_profile_image'] ?? 'Passport Photo'));
        SystemSetting::setSetting('staff_payroll_registration_label_assigned_hostel', (string) ($data['staff_payroll_registration_label_assigned_hostel'] ?? 'Assigned Hostel'));
        SystemSetting::setSetting('staff_payroll_registration_label_general_staff', (string) ($data['staff_payroll_registration_label_general_staff'] ?? 'I am a general staff (all hostels)'));
        SystemSetting::setSetting('staff_payroll_departments_csv', (string) ($data['staff_payroll_departments_csv'] ?? ''));
        SystemSetting::setSetting('staff_payroll_categories_csv', (string) ($data['staff_payroll_categories_csv'] ?? ''));
        SystemSetting::setSetting('staff_payroll_id_card_title', (string) ($data['staff_payroll_id_card_title'] ?? 'STAFF ID CARD'));
        SystemSetting::setSetting('staff_payroll_id_card_subtitle', (string) ($data['staff_payroll_id_card_subtitle'] ?? ''));
        SystemSetting::setSetting('staff_payroll_id_card_footer', (string) ($data['staff_payroll_id_card_footer'] ?? ''));
        SystemSetting::setSetting('staff_payroll_id_card_show_email', !empty($data['staff_payroll_id_card_show_email']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_id_card_show_phone', !empty($data['staff_payroll_id_card_show_phone']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_id_card_show_department', !empty($data['staff_payroll_id_card_show_department']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_id_card_use_custom_brand', !empty($data['staff_payroll_id_card_use_custom_brand']) ? '1' : '0');
        SystemSetting::setSetting('staff_payroll_id_card_brand_name', (string) ($data['staff_payroll_id_card_brand_name'] ?? ''));
        SystemSetting::setSetting('staff_payroll_id_card_brand_logo', $this->normalizeStoredFileValue($data['staff_payroll_id_card_brand_logo'] ?? null));
        SystemSetting::setSetting('staff_payroll_id_card_background_template', $this->normalizeStoredFileValue($data['staff_payroll_id_card_background_template'] ?? null));
        $pngScale = (string) ($data['staff_payroll_id_card_png_scale'] ?? '2');
        if (!in_array($pngScale, ['2', '3'], true)) {
            $pngScale = '2';
        }
        SystemSetting::setSetting('staff_payroll_id_card_png_scale', $pngScale);
        SystemSetting::setSetting(
            'staff_payroll_id_card_layout_json',
            json_encode($this->normalizeIdCardLayout($data['staff_payroll_id_card_layout_json'] ?? []), JSON_UNESCAPED_UNICODE)
        );
        SystemSetting::setSetting(
            'staff_payroll_registration_custom_fields_json',
            json_encode($this->normalizeCustomFields($data['staff_payroll_registration_custom_fields'] ?? []), JSON_UNESCAPED_UNICODE)
        );

        if (Schema::hasTable('staff_members')) {
            try {
                app(StaffDirectorySyncService::class)->syncCoreUsers();
            } catch (Throwable $e) {
                // Keep settings save resilient.
            }
        }

        Notification::make()
            ->success()
            ->title('Staff payroll settings saved')
            ->send();

        if (!empty($data['staff_payroll_email_notifications_enabled']) && !app(StaffPayrollNotificationService::class)->isEmailChannelConfigured()) {
            Notification::make()
                ->warning()
                ->title('SMTP is not configured')
                ->body('Email notifications are enabled, but SMTP settings are incomplete.')
                ->send();
        }

        if (!empty($data['staff_payroll_sms_notifications_enabled']) && !app(SmsGatewayService::class)->isConfigured()) {
            Notification::make()
                ->warning()
                ->title('SMS gateway is not configured')
                ->body('SMS notifications are enabled, but SMS provider settings are incomplete.')
                ->send();
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeCustomFields(array $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $key = strtolower(trim((string) ($row['key'] ?? '')));
            $key = preg_replace('/[^a-z0-9_]/', '_', $key);
            $key = trim((string) $key, '_');
            if ($key === '') {
                continue;
            }

            $type = (string) ($row['type'] ?? 'text');
            if (!in_array($type, ['text', 'textarea', 'email', 'number', 'date', 'select'], true)) {
                $type = 'text';
            }

            $normalized[] = [
                'key' => $key,
                'label' => (string) ($row['label'] ?? ucfirst(str_replace('_', ' ', $key))),
                'type' => $type,
                'required' => !empty($row['required']),
                'options' => (string) ($row['options'] ?? ''),
                'placeholder' => (string) ($row['placeholder'] ?? ''),
            ];
        }

        return array_values($normalized);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeIdCardLayout(array $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $key = trim((string) ($row['key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $normalized[] = [
                'key' => $key,
                'x' => (int) ($row['x'] ?? 55),
                'y' => (int) ($row['y'] ?? 200),
                'size' => max(10, (int) ($row['size'] ?? 14)),
                'color' => (string) ($row['color'] ?? '#ffffff'),
                'weight' => max(300, min(900, (int) ($row['weight'] ?? 400))),
                'width' => max(24, (int) ($row['width'] ?? 140)),
                'height' => max(24, (int) ($row['height'] ?? 48)),
                'locked' => !empty($row['locked']),
            ];
        }

        return array_values($normalized);
    }

    private function normalizeStoredFileValue(mixed $value): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return trim((string) $value);
            }

            return '';
        }

        return trim((string) ($value ?? ''));
    }

    /**
     * @return array<int, mixed>
     */
    private function decodeJsonArraySetting(string $key): array
    {
        $decoded = json_decode((string) SystemSetting::getSetting($key, '[]'), true);

        return $this->normalizeRepeaterState($decoded);
    }

    /**
     * @return array<int, mixed>
     */
    private function normalizeRepeaterState(mixed $state): array
    {
        if ($state instanceof \Illuminate\Contracts\Support\Arrayable) {
            $state = $state->toArray();
        }

        if (!is_array($state)) {
            return [];
        }

        return array_values(array_filter($state, static fn (mixed $row): bool => is_array($row)));
    }
}
