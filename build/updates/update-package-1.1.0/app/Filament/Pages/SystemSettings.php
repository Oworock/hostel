<?php

namespace App\Filament\Pages;

use App\Models\PaymentGateway;
use App\Models\Addon;
use App\Services\PaymentGatewayDiagnosticsService;
use App\Services\NotificationTemplateService;
use App\Support\CurrencyCatalog;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\SystemSetting;
use Throwable;

class SystemSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.system-settings';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;
    
    protected static ?string $title = 'System Settings';
    
    protected static ?string $slug = 'settings/config';

    public ?array $data = [];
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function mount(): void
    {
        abort_if(auth()->user()?->role !== 'admin', 403);

        $paystackGateway = PaymentGateway::where('name', 'Paystack')->first();
        $flutterwaveGateway = PaymentGateway::where('name', 'Flutterwave')->first();
        $stripeGateway = PaymentGateway::where('name', 'Stripe')->first();
        $paypalGateway = PaymentGateway::where('name', 'PayPal')->first();
        $razorpayGateway = PaymentGateway::where('name', 'Razorpay')->first();
        $squareGateway = PaymentGateway::where('name', 'Square')->first();

        $smsPayloadTemplate = json_decode(SystemSetting::getSetting('sms_payload_template_json', ''), true);
        $smsCustomHeaders = json_decode(SystemSetting::getSetting('sms_custom_headers_json', ''), true);
        $registrationFields = json_decode(SystemSetting::getSetting('registration_fields_json', ''), true);
        $registrationRequiredFields = json_decode(SystemSetting::getSetting('registration_required_fields_json', ''), true);
        $registrationCustomFields = json_decode(SystemSetting::getSetting('registration_custom_fields_json', ''), true);
        $trimesterEligibleSchools = json_decode(SystemSetting::getSetting('trimester_eligible_schools_json', ''), true);
        $registrationSchoolOptions = json_decode(SystemSetting::getSetting('registration_school_options_json', ''), true);
        $bookingDiscountRules = json_decode(SystemSetting::getSetting('booking_discount_rules_json', ''), true);
        $webhookEvents = json_decode(SystemSetting::getSetting('webhook_events_json', ''), true);
        $notificationTemplates = app(NotificationTemplateService::class)->forRepeater();
        $referralInviteToken = (string) SystemSetting::getSetting('referral_partner_invite_token', '');
        if (trim($referralInviteToken) === '') {
            $referralInviteToken = strtoupper(Str::random(18));
            SystemSetting::setSetting('referral_partner_invite_token', $referralInviteToken);
        }
        
        $defaultCustomCss = $this->getDefaultCustomCss();
        $currentCustomCss = SystemSetting::getSetting('custom_css', '');
        if (trim((string) $currentCustomCss) === '') {
            SystemSetting::setSetting('custom_css', $defaultCustomCss);
            $currentCustomCss = $defaultCustomCss;
        }

        $this->form->fill([
            'app_name' => SystemSetting::getSetting('app_name', config('app.name')),
            'app_email' => SystemSetting::getSetting('app_email', config('mail.from.address')),
            'app_phone' => SystemSetting::getSetting('app_phone', config('app.phone')),
            'system_currency' => SystemSetting::getSetting('system_currency', 'NGN'),
            'website_theme' => SystemSetting::getSetting('website_theme', 'oceanic'),
            'homepage_enabled' => filter_var(SystemSetting::getSetting('homepage_enabled', true), FILTER_VALIDATE_BOOL),
            'custom_css_enabled' => filter_var(SystemSetting::getSetting('custom_css_enabled', true), FILTER_VALIDATE_BOOL),
            'custom_css' => $currentCustomCss,
            'app_locale' => SystemSetting::getSetting('app_locale', config('app.locale', 'en')),
            'app_fallback_locale' => SystemSetting::getSetting('app_fallback_locale', config('app.fallback_locale', 'en')),
            'registration_fields' => is_array($registrationFields) ? $registrationFields : ['phone'],
            'registration_required_fields' => is_array($registrationRequiredFields) ? $registrationRequiredFields : [],
            'registration_custom_fields' => is_array($registrationCustomFields) ? $registrationCustomFields : [],
            'booking_period_type' => SystemSetting::getSetting('booking_period_type', 'months'),
            'session_booking_enabled' => filter_var(SystemSetting::getSetting('session_booking_enabled', true), FILTER_VALIDATE_BOOL),
            'session_booking_discount_type' => SystemSetting::getSetting('session_booking_discount_type', 'none'),
            'session_booking_discount_value' => (float) SystemSetting::getSetting('session_booking_discount_value', 0),
            'trimester_booking_enabled' => filter_var(SystemSetting::getSetting('trimester_booking_enabled', false), FILTER_VALIDATE_BOOL),
            'trimester_eligible_schools_text' => implode(PHP_EOL, array_values(array_filter(is_array($trimesterEligibleSchools) ? $trimesterEligibleSchools : []))),
            'registration_school_options_text' => implode(PHP_EOL, array_values(array_filter(is_array($registrationSchoolOptions) ? $registrationSchoolOptions : []))),
            'school_catalog_text' => implode(PHP_EOL, array_values(array_filter(is_array($registrationSchoolOptions) ? $registrationSchoolOptions : []))),
            'booking_discount_rules' => is_array($bookingDiscountRules) ? $bookingDiscountRules : [],
            'referral_enabled' => filter_var(SystemSetting::getSetting('referral_enabled', true), FILTER_VALIDATE_BOOL),
            'referral_students_can_be_agents' => filter_var(SystemSetting::getSetting('referral_students_can_be_agents', true), FILTER_VALIDATE_BOOL),
            'referral_default_commission_type' => SystemSetting::getSetting('referral_default_commission_type', 'percentage'),
            'referral_default_commission_value' => (float) SystemSetting::getSetting('referral_default_commission_value', 5),
            'referral_min_payout' => (float) SystemSetting::getSetting('referral_min_payout', 0),
            'referral_notify_email' => filter_var(SystemSetting::getSetting('referral_notify_email', true), FILTER_VALIDATE_BOOL),
            'referral_notify_sms' => filter_var(SystemSetting::getSetting('referral_notify_sms', false), FILTER_VALIDATE_BOOL),
            'referral_partner_invite_token' => $referralInviteToken,
            'sms_provider' => SystemSetting::getSetting('sms_provider', 'none'),
            'sms_url' => SystemSetting::getSetting('sms_url', ''),
            'sms_http_method' => SystemSetting::getSetting('sms_http_method', 'POST'),
            'sms_api_key' => SystemSetting::getSetting('sms_api_key', ''),
            'sms_sender_id' => SystemSetting::getSetting('sms_sender_id', ''),
            'sms_message_template' => SystemSetting::getSetting('sms_message_template', ''),
            'sms_payload_template' => is_array($smsPayloadTemplate) ? $smsPayloadTemplate : [
                'to' => '{to}',
                'message' => '{message}',
                'from' => '{from}',
                'api_key' => '{api_key}',
            ],
            'sms_custom_headers' => is_array($smsCustomHeaders) ? $smsCustomHeaders : [],
            'test_phone' => '',
            'smtp_test_email' => SystemSetting::getSetting('app_email', config('mail.from.address')),
            'mail_mailer' => SystemSetting::getSetting('mail_mailer', config('mail.default', 'smtp')),
            'smtp_host' => SystemSetting::getSetting('smtp_host', config('mail.mailers.smtp.host')),
            'smtp_port' => SystemSetting::getSetting('smtp_port', config('mail.mailers.smtp.port')),
            'smtp_username' => SystemSetting::getSetting('smtp_username', config('mail.mailers.smtp.username')),
            'smtp_password' => SystemSetting::getSetting('smtp_password', config('mail.mailers.smtp.password')),
            'smtp_encryption' => SystemSetting::getSetting('smtp_encryption', config('mail.mailers.smtp.encryption')),
            'smtp_from_email' => SystemSetting::getSetting('smtp_from_email', ''),
            'smtp_from_name' => SystemSetting::getSetting('smtp_from_name', ''),
            'paystack_enabled' => $paystackGateway?->is_active ?? false,
            'paystack_public_key' => $paystackGateway?->public_key ?? SystemSetting::getSetting('paystack_public_key', ''),
            'paystack_secret_key' => $paystackGateway?->secret_key ?? SystemSetting::getSetting('paystack_secret_key', ''),
            'flutterwave_enabled' => $flutterwaveGateway?->is_active ?? false,
            'flutterwave_public_key' => $flutterwaveGateway?->public_key ?? SystemSetting::getSetting('flutterwave_public_key', ''),
            'flutterwave_secret_key' => $flutterwaveGateway?->secret_key ?? SystemSetting::getSetting('flutterwave_secret_key', ''),
            'stripe_enabled' => $stripeGateway?->is_active ?? false,
            'stripe_public_key' => $stripeGateway?->public_key ?? SystemSetting::getSetting('stripe_public_key', ''),
            'stripe_secret_key' => $stripeGateway?->secret_key ?? SystemSetting::getSetting('stripe_secret_key', ''),
            'paypal_enabled' => $paypalGateway?->is_active ?? false,
            'paypal_public_key' => $paypalGateway?->public_key ?? SystemSetting::getSetting('paypal_public_key', ''),
            'paypal_secret_key' => $paypalGateway?->secret_key ?? SystemSetting::getSetting('paypal_secret_key', ''),
            'paypal_environment' => SystemSetting::getSetting('paypal_environment', 'live'),
            'razorpay_enabled' => $razorpayGateway?->is_active ?? false,
            'razorpay_public_key' => $razorpayGateway?->public_key ?? SystemSetting::getSetting('razorpay_public_key', ''),
            'razorpay_secret_key' => $razorpayGateway?->secret_key ?? SystemSetting::getSetting('razorpay_secret_key', ''),
            'square_enabled' => $squareGateway?->is_active ?? false,
            'square_public_key' => $squareGateway?->public_key ?? SystemSetting::getSetting('square_public_key', ''),
            'square_secret_key' => $squareGateway?->secret_key ?? SystemSetting::getSetting('square_secret_key', ''),
            'square_location_id' => SystemSetting::getSetting('square_location_id', ''),
            'square_environment' => SystemSetting::getSetting('square_environment', 'live'),
            'webhook_enabled' => filter_var(SystemSetting::getSetting('webhook_enabled', false), FILTER_VALIDATE_BOOL),
            'webhook_url' => SystemSetting::getSetting('webhook_url', ''),
            'webhook_events' => is_array($webhookEvents) ? $webhookEvents : [],
            'api_enabled' => filter_var(SystemSetting::getSetting('api_enabled', false), FILTER_VALIDATE_BOOL),
            'api_access_key' => SystemSetting::getSetting('api_access_key', ''),
            'notification_templates' => $notificationTemplates,
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('General'))
                            ->icon('heroicon-m-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make(__('App Settings'))
                                    ->schema([
                                        Forms\Components\TextInput::make('app_name')
                                            ->label(__('Application Name'))
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('app_email')
                                            ->label(__('Admin Email'))
                                            ->email()
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('app_phone')
                                            ->label(__('Admin Phone'))
                                            ->tel(),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make(__('Site Configuration'))
                            ->icon('heroicon-m-window')
                            ->schema([
                                Forms\Components\Section::make(__('Booking Configuration'))
                                    ->schema([
                                        Forms\Components\Select::make('booking_period_type')
                                            ->label(__('Booking Period Type'))
                                            ->options([
                                                'months' => 'Months',
                                                'semesters' => 'Semesters',
                                                'sessions' => 'Sessions',
                                            ])
                                            ->required(),
                                        Forms\Components\Toggle::make('session_booking_enabled')
                                            ->label(__('Allow Session Booking In Semester Mode'))
                                            ->helperText(__('When enabled, students can choose Session booking even when booking type is set to Semesters. Session price defaults to 2x semester price.'))
                                            ->default(true),
                                        Forms\Components\Select::make('session_booking_discount_type')
                                            ->label(__('Session Discount Type'))
                                            ->options([
                                                'none' => 'No discount',
                                                'percentage' => 'Percentage (%)',
                                                'fixed' => 'Fixed amount',
                                            ])
                                            ->helperText(__('Discount applies to all session bookings based on the default 2x semester price.'))
                                            ->default('none')
                                            ->native(false)
                                            ->required(),
                                        Forms\Components\TextInput::make('session_booking_discount_value')
                                            ->label(__('Session Discount Value'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->required(),
                                        Forms\Components\Toggle::make('trimester_booking_enabled')
                                            ->label(__('Allow Trimester Booking'))
                                            ->helperText(__('When enabled, students can choose Trimester. Price is calculated as Session payable amount divided by 3.'))
                                            ->default(false),
                                        Forms\Components\Textarea::make('trimester_eligible_schools_text')
                                            ->label(__('Trimester Eligible Schools'))
                                            ->rows(4)
                                            ->placeholder("School of Science\nSchool of Engineering")
                                            ->helperText(__('Enter one school per line. Only students from these schools can choose trimester booking.')),
                                        Forms\Components\Textarea::make('school_catalog_text')
                                            ->label(__('Schools Available For Registration'))
                                            ->rows(5)
                                            ->placeholder("School of Science\nSchool of Engineering")
                                            ->helperText(__('Enter one school per line. Students will select from this list during registration.')),
                                        Forms\Components\Repeater::make('booking_discount_rules')
                                            ->label(__('Advanced Booking Discounts'))
                                            ->helperText(__('Create discount rules such as sibling, early-booker, and returning-student discounts.'))
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label(__('Discount Name'))
                                                    ->required(),
                                                Forms\Components\Select::make('rule_type')
                                                    ->label(__('Rule Type'))
                                                    ->options([
                                                        'sibling' => 'Sibling Discount',
                                                        'early_booker' => 'Early Booker Discount',
                                                        'returning_student' => 'Returning Student Discount',
                                                        'general' => 'General Discount',
                                                    ])
                                                    ->default('general')
                                                    ->required(),
                                                Forms\Components\Select::make('discount_type')
                                                    ->label(__('Discount Type'))
                                                    ->options([
                                                        'percentage' => 'Percentage (%)',
                                                        'fixed' => 'Fixed amount',
                                                    ])
                                                    ->default('percentage')
                                                    ->required(),
                                                Forms\Components\TextInput::make('discount_value')
                                                    ->label(__('Discount Value'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->required(),
                                                Forms\Components\Select::make('applies_to')
                                                    ->label(__('Applies To'))
                                                    ->options([
                                                        'all' => 'All Bookings',
                                                        'months' => 'Monthly Booking',
                                                        'semester' => 'Semester Booking',
                                                        'session' => 'Session Booking',
                                                        'trimester' => 'Trimester Booking',
                                                    ])
                                                    ->default('all')
                                                    ->required(),
                                                Forms\Components\DateTimePicker::make('starts_at')
                                                    ->label(__('Start Time'))
                                                    ->seconds(false),
                                                Forms\Components\DateTimePicker::make('ends_at')
                                                    ->label(__('End Time'))
                                                    ->seconds(false),
                                                Forms\Components\TextInput::make('sibling_similarity')
                                                    ->label(__('Sibling Name Similarity (%)'))
                                                    ->numeric()
                                                    ->minValue(50)
                                                    ->maxValue(100)
                                                    ->default(80)
                                                    ->visible(fn (Forms\Get $get): bool => (string) $get('rule_type') === 'sibling'),
                                                Forms\Components\Toggle::make('is_active')
                                                    ->label(__('Active'))
                                                    ->default(true),
                                            ])
                                            ->columns(3)
                                            ->columnSpanFull()
                                            ->collapsible()
                                            ->reorderableWithButtons(),
                                    ]),

                                Forms\Components\Section::make(__('System Currency'))
                                    ->schema([
                                        Forms\Components\Select::make('system_currency')
                                            ->label(__('Currency'))
                                            ->options(CurrencyCatalog::options())
                                            ->searchable()
                                            ->required(),
                                    ]),

                                Forms\Components\Section::make(__('Website Theme'))
                                    ->description(__('Choose one of five professional preset website layouts/design directions.'))
                                    ->schema([
                                        Forms\Components\Toggle::make('homepage_enabled')
                                            ->label(__('Enable Welcome/Home Page'))
                                            ->helperText(__('If disabled, the root URL redirects to Login and the welcome page is inaccessible.'))
                                            ->default(true),
                                        Forms\Components\Select::make('website_theme')
                                            ->label(__('Theme Preset'))
                                            ->options([
                                                'oceanic' => 'Oceanic Professional',
                                                'emerald' => 'Emerald Campus',
                                                'slate' => 'Slate Corporate',
                                                'sunset' => 'Sunset Warm',
                                                'royal' => 'Royal Indigo',
                                            ])
                                            ->default('oceanic')
                                            ->required(),
                                    ]),

                                Forms\Components\Section::make(__('Custom Website CSS'))
                                    ->description(__('Add custom CSS that will be applied globally to public and dashboard pages.'))
                                    ->schema([
                                        Forms\Components\Toggle::make('custom_css_enabled')
                                            ->label(__('Enable Custom CSS'))
                                            ->helperText(__('Disable to keep your saved CSS without applying it on the frontend.'))
                                            ->default(true)
                                            ->live(),
                                        Forms\Components\Textarea::make('custom_css')
                                            ->label(__('Custom CSS'))
                                            ->rows(12)
                                            ->placeholder("/* Example */\n:root { --brand: #0f766e; }\n.btn-primary { border-radius: 10px; }")
                                            ->visible(fn (Forms\Get $get): bool => (bool) $get('custom_css_enabled'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('Referral Settings'))
                            ->icon('heroicon-m-user-group')
                            ->schema([
                                Forms\Components\Section::make(__('Referral Incentives'))
                                    ->schema([
                                        Forms\Components\Toggle::make('referral_enabled')
                                            ->label(__('Enable Referral Program'))
                                            ->default(true),
                                        Forms\Components\Toggle::make('referral_students_can_be_agents')
                                            ->label(__('Allow Students To Become Referral Partners'))
                                            ->default(true),
                                        Forms\Components\Select::make('referral_default_commission_type')
                                            ->label(__('Default Commission Type'))
                                            ->options([
                                                'percentage' => 'Percentage (%)',
                                                'fixed' => 'Fixed amount',
                                            ])
                                            ->default('percentage')
                                            ->required(),
                                        Forms\Components\TextInput::make('referral_default_commission_value')
                                            ->label(__('Default Commission Value'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(5)
                                            ->required(),
                                        Forms\Components\TextInput::make('referral_min_payout')
                                            ->label(__('Minimum Payout Threshold'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->required(),
                                        Forms\Components\Toggle::make('referral_notify_email')
                                            ->label(__('Send Referral Event Email Notifications'))
                                            ->default(true),
                                        Forms\Components\Toggle::make('referral_notify_sms')
                                            ->label(__('Send Referral Event SMS Notifications'))
                                            ->default(false),
                                        Forms\Components\TextInput::make('referral_partner_invite_token')
                                            ->label(__('Referral Partner Invite Token'))
                                            ->helperText(fn (Forms\Get $get): string => url('/referrals/register?invite=' . trim((string) $get('referral_partner_invite_token'))))
                                            ->required(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('Registration Form'))
                            ->icon('heroicon-m-user-plus')
                            ->schema([
                                Forms\Components\Section::make(__('Student Registration Fields'))
                                    ->description(__('Choose optional fields to show on student signup and which are required.'))
                                    ->schema([
                                        Forms\Components\CheckboxList::make('registration_fields')
                                            ->label(__('Show These Optional Fields'))
                                            ->options([
                                                'phone' => 'Phone Number',
                                                'id_number' => 'ID Number',
                                                'address' => 'Address',
                                                'guardian_name' => 'Guardian Name',
                                                'guardian_phone' => 'Guardian Phone',
                                            ])
                                            ->columns(2),
                                        Forms\Components\CheckboxList::make('registration_required_fields')
                                            ->label(__('Make Required'))
                                            ->options([
                                                'phone' => 'Phone Number',
                                                'id_number' => 'ID Number',
                                                'address' => 'Address',
                                                'guardian_name' => 'Guardian Name',
                                                'guardian_phone' => 'Guardian Phone',
                                            ])
                                            ->columns(2)
                                            ->helperText(__('Only fields selected in "Show" are applied in registration.')),
                                        Forms\Components\Repeater::make('registration_custom_fields')
                                            ->label(__('Custom Fields'))
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label(__('Field Name'))
                                                    ->helperText(__('Use lowercase and underscore only, e.g. matric_no')),
                                                Forms\Components\TextInput::make('label')
                                                    ->label(__('Field Label')),
                                                Forms\Components\Select::make('type')
                                                    ->options([
                                                        'text' => 'Text',
                                                        'email' => 'Email',
                                                        'tel' => 'Phone',
                                                        'number' => 'Number',
                                                        'date' => 'Date',
                                                        'upload' => 'Image Upload',
                                                    ])
                                                    ->default('text'),
                                                Forms\Components\TextInput::make('placeholder')
                                                    ->label(__('Placeholder')),
                                                Forms\Components\Toggle::make('required')
                                                    ->label(__('Required'))
                                                    ->default(false),
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->collapsible()
                                            ->reorderableWithButtons()
                                            ->helperText(__('Add custom registration fields to capture more information over time.')),
                                        Forms\Components\Textarea::make('registration_school_options_text')
                                            ->label(__('Schools For Student Registration'))
                                            ->rows(5)
                                            ->placeholder("School of Science\nSchool of Engineering")
                                            ->helperText(__('Enter one school per line. Students will pick from this list during registration.')),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('Localization'))
                            ->icon('heroicon-m-language')
                            ->schema([
                                Forms\Components\Section::make(__('Language Configuration'))
                                    ->description(__('Set default and fallback languages.'))
                                    ->schema([
                                        Forms\Components\Select::make('app_locale')
                                            ->label(__('Default Language'))
                                            ->options($this->languageOptions())
                                            ->searchable()
                                            ->required(),
                                        Forms\Components\Select::make('app_fallback_locale')
                                            ->label(__('Fallback Language'))
                                            ->options($this->languageOptions())
                                            ->searchable()
                                            ->required(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('Website Content'))
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Forms\Components\Section::make(__('Header, Footer, Welcome Page Content'))
                                    ->schema([
                                        Forms\Components\Placeholder::make('website_content_note')
                                            ->content('Manage logo, header/footer content, and welcome-page sections from one place.'),
                                        Forms\Components\Placeholder::make('website_content_link')
                                            ->content(new HtmlString('<a href="' . route('filament.admin.resources.welcome-contents.index') . '" class="text-primary-600 underline">Open Website Content Manager</a>')),
                                        Forms\Components\Placeholder::make('file_manager_link')
                                            ->content(new HtmlString('<a href="' . route('filament.admin.pages.system.file-manager') . '" class="text-primary-600 underline">Open File Manager</a>')),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make(__('SMS Configuration'))
                            ->icon('heroicon-m-chat-bubble-left')
                            ->schema([
                                Forms\Components\Section::make(__('SMS Provider Settings'))
                                    ->description(__('Configure your SMS provider for sending notifications'))
                                    ->schema([
                                        Forms\Components\Select::make('sms_provider')
                                            ->label(__('SMS Provider'))
                                            ->options([
                                                'custom' => 'Custom SMS Gateway',
                                                'none' => 'Disabled',
                                            ])
                                            ->required()
                                            ->live(),
                                        
                                        Forms\Components\TextInput::make('sms_url')
                                            ->label(__('SMS Gateway URL'))
                                            ->placeholder(__('https://your-sms-provider.com/api/send'))
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->required(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),

                                        Forms\Components\Select::make('sms_http_method')
                                            ->label(__('HTTP Method'))
                                            ->options([
                                                'POST' => 'POST',
                                                'GET' => 'GET',
                                            ])
                                            ->default('POST')
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->required(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        
                                        Forms\Components\TextInput::make('sms_api_key')
                                            ->label(__('API Key'))
                                            ->password()
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->required(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        
                                        Forms\Components\Textarea::make('sms_message_template')
                                            ->label(__('Message Template (Optional)'))
                                            ->placeholder(__('Use {{name}}, {{message}}, {{hostel}} as placeholders'))
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->columnSpanFull(),

                                        Forms\Components\KeyValue::make('sms_payload_template')
                                            ->label(__('SMS Payload Key-Value Template'))
                                            ->helperText(__('Recommended placeholders: {to} recipient phone, {message} body text, {from} sender ID/Name, {api_key} API key. Backward-compatible: {phone}, {sender_id}.'))
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->columnSpanFull(),

                                        Forms\Components\KeyValue::make('sms_custom_headers')
                                            ->label(__('Custom Request Headers'))
                                            ->helperText(__('Optional HTTP headers sent with SMS requests.'))
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->columnSpanFull(),

                                        Forms\Components\Placeholder::make('sms_key_guide')
                                            ->content(new HtmlString(
                                                '<div><strong>SMS placeholder guide:</strong><br>{to} = recipient phone number<br>{message} = SMS body<br>{from} = sender ID/name<br>{api_key} = API key/token<br>Legacy placeholders still supported: {phone}, {sender_id}</div>'
                                            ))
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('test_phone')
                                            ->label(__('Test Phone Number'))
                                            ->tel()
                                            ->placeholder(__('+2349000000000'))
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('test_sms_inline')
                                                ->label(__('Test SMS'))
                                                ->color('info')
                                                ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                                ->action(fn () => $this->testSMS()),
                                        ]),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make(__('Email Configuration'))
                            ->icon('heroicon-m-envelope')
                            ->schema([
                                Forms\Components\Section::make(__('SMTP Mail Settings'))
                                    ->description(__('Configure SMTP for sending emails'))
                                    ->schema([
                                        Forms\Components\Select::make('mail_mailer')
                                            ->label(__('Email Driver'))
                                            ->options([
                                                'smtp' => 'SMTP',
                                                'sendmail' => 'PHP Mail (sendmail/mail())',
                                            ])
                                            ->default('smtp')
                                            ->live()
                                            ->required(),
                                        Forms\Components\TextInput::make('smtp_host')
                                            ->label(__('SMTP Host'))
                                            ->placeholder(__('smtp.gmail.com'))
                                            ->visible(fn (Forms\Get $get) => ($get('mail_mailer') ?? 'smtp') === 'smtp'),
                                        
                                        Forms\Components\TextInput::make('smtp_port')
                                            ->label(__('SMTP Port'))
                                            ->numeric()
                                            ->placeholder(__('587'))
                                            ->visible(fn (Forms\Get $get) => ($get('mail_mailer') ?? 'smtp') === 'smtp'),
                                        
                                        Forms\Components\TextInput::make('smtp_username')
                                            ->label(__('Username/Email'))
                                            ->placeholder(__('your-email@example.com'))
                                            ->visible(fn (Forms\Get $get) => ($get('mail_mailer') ?? 'smtp') === 'smtp'),
                                        
                                        Forms\Components\TextInput::make('smtp_password')
                                            ->label(__('Password'))
                                            ->password()
                                            ->placeholder(__('••••••••'))
                                            ->visible(fn (Forms\Get $get) => ($get('mail_mailer') ?? 'smtp') === 'smtp'),
                                        
                                        Forms\Components\Select::make('smtp_encryption')
                                            ->label(__('Encryption'))
                                            ->options([
                                                'tls' => 'TLS',
                                                'ssl' => 'SSL',
                                            ])
                                            ->default('tls')
                                            ->visible(fn (Forms\Get $get) => ($get('mail_mailer') ?? 'smtp') === 'smtp'),
                                        Forms\Components\TextInput::make('smtp_from_email')
                                            ->label(__('From Email (Override)'))
                                            ->email()
                                            ->placeholder(__('noreply@example.com'))
                                            ->helperText(__('Optional. If empty, system email is used.')),
                                        Forms\Components\TextInput::make('smtp_from_name')
                                            ->label(__('From Name (Override)'))
                                            ->placeholder(__('Hostel Management'))
                                            ->helperText(__('Optional. If empty, system name is used.')),
                                        Forms\Components\TextInput::make('smtp_test_email')
                                            ->label(__('Test Recipient Email'))
                                            ->email()
                                            ->placeholder(__('you@example.com')),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('test_email_inline')
                                                ->label(__('Test Email'))
                                                ->color('info')
                                                ->action(fn () => $this->testEmail()),
                                        ]),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make(__('Payment Gateways'))
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                Forms\Components\Section::make(__('Paystack Configuration'))
                                    ->description(__('Configure Paystack for payment processing'))
                                    ->schema([
                                        Forms\Components\Toggle::make('paystack_enabled')
                                            ->label(__('Enable Paystack')),
                                        Forms\Components\TextInput::make('paystack_public_key')
                                            ->label(__('Public Key'))
                                            ->password()
                                            ->placeholder(__('pk_live_xxxxx')),
                                        
                                        Forms\Components\TextInput::make('paystack_secret_key')
                                            ->label(__('Secret Key'))
                                            ->password()
                                            ->placeholder(__('sk_live_xxxxx')),
                                    ]),
                                
                                Forms\Components\Section::make(__('Flutterwave Configuration'))
                                    ->description(__('Configure Flutterwave for payment processing'))
                                    ->schema([
                                        Forms\Components\Toggle::make('flutterwave_enabled')
                                            ->label(__('Enable Flutterwave')),
                                        Forms\Components\TextInput::make('flutterwave_public_key')
                                            ->label(__('Public Key'))
                                            ->password()
                                            ->placeholder(__('pk_test_xxxxx')),
                                        
                                        Forms\Components\TextInput::make('flutterwave_secret_key')
                                            ->label(__('Secret Key'))
                                            ->password()
                                            ->placeholder(__('sk_test_xxxxx')),
                                    ]),

                                Forms\Components\Section::make(__('Stripe Configuration'))
                                    ->description(__('Configure Stripe for payment processing'))
                                    ->schema([
                                        Forms\Components\Toggle::make('stripe_enabled')
                                            ->label(__('Enable Stripe')),
                                        Forms\Components\TextInput::make('stripe_public_key')
                                            ->label(__('Publishable Key'))
                                            ->password()
                                            ->placeholder(__('pk_live_xxxxx')),
                                        Forms\Components\TextInput::make('stripe_secret_key')
                                            ->label(__('Secret Key'))
                                            ->password()
                                            ->placeholder(__('sk_live_xxxxx')),
                                    ]),

                                Forms\Components\Section::make(__('PayPal Configuration'))
                                    ->description(__('Configure PayPal (Client ID + Client Secret)'))
                                    ->schema([
                                        Forms\Components\Toggle::make('paypal_enabled')
                                            ->label(__('Enable PayPal')),
                                        Forms\Components\TextInput::make('paypal_public_key')
                                            ->label(__('Client ID'))
                                            ->password()
                                            ->placeholder(__('PAYPAL_CLIENT_ID')),
                                        Forms\Components\TextInput::make('paypal_secret_key')
                                            ->label(__('Client Secret'))
                                            ->password()
                                            ->placeholder(__('PAYPAL_CLIENT_SECRET')),
                                        Forms\Components\Select::make('paypal_environment')
                                            ->label(__('Environment'))
                                            ->options([
                                                'live' => 'Live',
                                                'sandbox' => 'Sandbox',
                                            ])
                                            ->default('live'),
                                    ]),

                                Forms\Components\Section::make(__('Razorpay Configuration'))
                                    ->description(__('Configure Razorpay (Key ID + Key Secret)'))
                                    ->schema([
                                        Forms\Components\Toggle::make('razorpay_enabled')
                                            ->label(__('Enable Razorpay')),
                                        Forms\Components\TextInput::make('razorpay_public_key')
                                            ->label(__('Key ID'))
                                            ->password()
                                            ->placeholder(__('rzp_live_xxxxx')),
                                        Forms\Components\TextInput::make('razorpay_secret_key')
                                            ->label(__('Key Secret'))
                                            ->password()
                                            ->placeholder(__('xxxxxxxx')),
                                    ]),

                                Forms\Components\Section::make(__('Square Configuration'))
                                    ->description(__('Configure Square (Application ID + Access Token)'))
                                    ->schema([
                                        Forms\Components\Toggle::make('square_enabled')
                                            ->label(__('Enable Square')),
                                        Forms\Components\TextInput::make('square_public_key')
                                            ->label(__('Application ID'))
                                            ->password()
                                            ->placeholder(__('sq0idp-xxxxxxxx')),
                                        Forms\Components\TextInput::make('square_secret_key')
                                            ->label(__('Access Token'))
                                            ->password()
                                            ->placeholder(__('EAAAE...')),
                                        Forms\Components\TextInput::make('square_location_id')
                                            ->label(__('Location ID'))
                                            ->placeholder(__('L123ABCDEF')),
                                        Forms\Components\Select::make('square_environment')
                                            ->label(__('Environment'))
                                            ->options([
                                                'live' => 'Live',
                                                'sandbox' => 'Sandbox',
                                            ])
                                            ->default('live'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('Integrations'))
                            ->icon('heroicon-m-link')
                            ->schema([
                                Forms\Components\Section::make(__('Outgoing Webhooks'))
                                    ->description(__('Send system events to Zapier, Make, n8n, or custom URLs.'))
                                    ->schema([
                                        Forms\Components\Toggle::make('webhook_enabled')
                                            ->label(__('Enable Webhooks')),
                                        Forms\Components\TextInput::make('webhook_url')
                                            ->label(__('Webhook URL'))
                                            ->url()
                                            ->placeholder(__('https://hooks.zapier.com/...')),
                                        Forms\Components\CheckboxList::make('webhook_events')
                                            ->label(__('Events to Send'))
                                            ->options([
                                                'hostel.created' => 'Hostel Created',
                                                'hostel.updated' => 'Hostel Updated',
                                                'hostel.deleted' => 'Hostel Deleted',
                                                'room.created' => 'Room Created',
                                                'room.updated' => 'Room Updated',
                                                'room.deleted' => 'Room Deleted',
                                                'booking.created' => 'Booking Created',
                                                'booking.cancelled' => 'Booking Cancelled',
                                                'booking.manager_approved' => 'Manager Approved Booking',
                                                'booking.manager_rejected' => 'Manager Rejected Booking',
                                                'booking.manager_cancelled' => 'Manager Cancelled Booking',
                                                'payment.completed' => 'Payment Completed',
                                                'complaint.created' => 'Complaint Created',
                                                'complaint.responded' => 'Complaint Responded',
                                                'notification.read' => 'Notification Read',
                                                'hostel_change.submitted' => 'Hostel Change Submitted',
                                                'hostel_change.manager_approved' => 'Hostel Change Manager Approved',
                                                'hostel_change.manager_rejected' => 'Hostel Change Manager Rejected',
                                                'hostel_change.admin_approved' => 'Hostel Change Admin Approved',
                                                'hostel_change.admin_rejected' => 'Hostel Change Admin Rejected',
                                                'asset.created' => 'Addon: Asset Created',
                                                'asset.issue_reported' => 'Addon: Asset Issue Reported',
                                                'asset.movement_requested' => 'Addon: Movement Requested',
                                                'asset.movement_receiving_decision' => 'Addon: Receiving Manager Decision',
                                                'asset.movement_approved' => 'Addon: Admin Approved Movement',
                                                'asset.movement_rejected' => 'Addon: Admin Rejected Movement',
                                                'asset.subscription.created' => 'Addon: Subscription Created',
                                                'asset.subscription.updated' => 'Addon: Subscription Updated',
                                                'asset.subscription.deleted' => 'Addon: Subscription Deleted',
                                                'asset.subscription.expiry_alert' => 'Addon: Subscription Expiry Alert',
                                                'addon.activated' => 'Addon Activated',
                                                'addon.deactivated' => 'Addon Deactivated',
                                                'system.webhook_test' => 'Webhook Test Event',
                                            ])
                                            ->columns(2),
                                    ]),
                                Forms\Components\Section::make(__('Third-Party API Access'))
                                    ->description(__('Use this key as Bearer token or X-API-Key on /api/v1 routes.'))
                                    ->schema([
                                        Forms\Components\Placeholder::make('api_docs_link')
                                            ->label(__('API Documentation'))
                                            ->content(new HtmlString('<a href="' . route('admin.api.docs') . '" target="_blank" class="text-primary-600 underline">Open API Documentation</a>')),
                                        Forms\Components\Toggle::make('api_enabled')
                                            ->label(__('Enable Public API')),
                                        Forms\Components\TextInput::make('api_access_key')
                                            ->label(__('API Access Key'))
                                            ->placeholder(__('generate-a-random-secret-key'))
                                            ->helperText(__('Visible for admin. Keep private and rotate periodically.')),
                                        Forms\Components\Placeholder::make('api_access_key_copy')
                                            ->label(__('Quick Copy'))
                                            ->content(new HtmlString('<button type="button" onclick="(function(){const i=document.querySelector(\'input[name=&quot;data[api_access_key]&quot;]\'); if(!i||!i.value){alert(\'No API key available.\');return;} navigator.clipboard.writeText(i.value).then(()=>alert(\'API key copied.\')).catch(()=>alert(\'Copy failed. Please copy manually.\'));})();" class="fi-btn fi-btn-size-sm fi-color-primary fi-btn-color-primary fi-ac-action fi-ac-btn-action">Copy API Key</button>')),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('Cron / Scheduler'))
                            ->icon('heroicon-m-clock')
                            ->visible(fn (): bool => Addon::isActive('asset-management'))
                            ->schema([
                                Forms\Components\Section::make(__('Addon Scheduler Setup'))
                                    ->description(__('Use these cron entries on any hosting provider to keep addon notifications and scheduled tasks running.'))
                                    ->schema([
                                        Forms\Components\Placeholder::make('cron_status')
                                            ->label(__('Addon Status'))
                                            ->content('Asset Management addon is active. Scheduler setup is available.'),
                                        Forms\Components\Placeholder::make('cron_command_every_minute')
                                            ->label(__('Recommended Cron (every minute)'))
                                            ->content(new HtmlString('<code>* * * * * ' . $this->cronPhpBinary() . ' ' . base_path('artisan') . ' schedule:run >> /dev/null 2>&1</code>')),
                                        Forms\Components\Placeholder::make('cron_command_direct_daily')
                                            ->label(__('Fallback Direct Cron (daily 8AM)'))
                                            ->content(new HtmlString('<code>0 8 * * * ' . $this->cronPhpBinary() . ' ' . base_path('artisan') . ' subscriptions:notify-expiring >> /dev/null 2>&1</code>')),
                                        Forms\Components\Placeholder::make('cron_help')
                                            ->content(new HtmlString(
                                                '<ul style="margin-left:1rem; list-style:disc;">'
                                                . '<li><strong>cPanel:</strong> Cron Jobs > add the recommended command.</li>'
                                                . '<li><strong>Plesk:</strong> Scheduled Tasks > Run a command.</li>'
                                                . '<li><strong>VPS/Dedicated:</strong> add entry via <code>crontab -e</code>.</li>'
                                                . '<li><strong>Windows Task Scheduler:</strong> run <code>php artisan schedule:run</code> every minute.</li>'
                                                . '</ul>'
                                            )),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('Notifications'))
                            ->icon('heroicon-m-bell')
                            ->schema([
                                Forms\Components\Section::make(__('System Notification Message Templates'))
                                    ->description(__('Customize in-app, email, and SMS message copy. Placeholders: {student_name}, {actor_name}, {current_hostel}, {requested_hostel}, {current_room}, {requested_room}, {status}, {reason}.'))
                                    ->schema([
                                        Forms\Components\Repeater::make('notification_templates')
                                            ->label(__('Event Templates'))
                                            ->schema([
                                                Forms\Components\TextInput::make('event')
                                                    ->disabled()
                                                    ->dehydrated(true)
                                                    ->required(),
                                                Forms\Components\TextInput::make('title')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('message')
                                                    ->rows(2)
                                                    ->required(),
                                            ])
                                            ->columns(1)
                                            ->reorderable(false)
                                            ->addable(false)
                                            ->deletable(false)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if ((bool) ($data['custom_css_enabled'] ?? true) && trim((string) ($data['custom_css'] ?? '')) === '') {
            $data['custom_css'] = $this->getDefaultCustomCss();
        }

        $data['sms_payload_template_json'] = json_encode($data['sms_payload_template'] ?? []);
        $data['sms_custom_headers_json'] = json_encode($data['sms_custom_headers'] ?? []);
        $data['webhook_events_json'] = json_encode(array_values($data['webhook_events'] ?? []));
        $data['registration_fields_json'] = json_encode(array_values($data['registration_fields'] ?? []));
        $data['registration_required_fields_json'] = json_encode(array_values($data['registration_required_fields'] ?? []));
        $data['registration_custom_fields_json'] = json_encode(array_values($data['registration_custom_fields'] ?? []));
        $data['booking_discount_rules_json'] = json_encode(
            collect($data['booking_discount_rules'] ?? [])
                ->map(function ($rule) {
                    if (!is_array($rule)) {
                        return null;
                    }

                    $name = trim((string) ($rule['name'] ?? ''));
                    $ruleType = (string) ($rule['rule_type'] ?? 'general');
                    $discountType = (string) ($rule['discount_type'] ?? 'percentage');
                    $discountValue = (float) ($rule['discount_value'] ?? 0);
                    $appliesTo = (string) ($rule['applies_to'] ?? 'all');

                    if ($name === '' || !in_array($ruleType, ['sibling', 'early_booker', 'returning_student', 'general'], true)) {
                        return null;
                    }

                    if (!in_array($discountType, ['percentage', 'fixed'], true)) {
                        $discountType = 'percentage';
                    }

                    if (!in_array($appliesTo, ['all', 'months', 'semester', 'session', 'trimester'], true)) {
                        $appliesTo = 'all';
                    }

                    return [
                        'name' => $name,
                        'rule_type' => $ruleType,
                        'discount_type' => $discountType,
                        'discount_value' => max(0, $discountValue),
                        'applies_to' => $appliesTo,
                        'starts_at' => isset($rule['starts_at']) ? (string) $rule['starts_at'] : null,
                        'ends_at' => isset($rule['ends_at']) ? (string) $rule['ends_at'] : null,
                        'sibling_similarity' => max(50, min(100, (int) ($rule['sibling_similarity'] ?? 80))),
                        'is_active' => (bool) ($rule['is_active'] ?? true),
                    ];
                })
                ->filter()
                ->values()
                ->all()
        );
        $data['trimester_eligible_schools_json'] = json_encode(
            collect(preg_split('/\r\n|\r|\n/', (string) ($data['trimester_eligible_schools_text'] ?? '')) ?: [])
                ->map(fn ($school) => trim((string) $school))
                ->filter()
                ->unique()
                ->values()
                ->all()
        );
        $registrationSchoolsText = trim((string) ($data['school_catalog_text'] ?? ''));
        if ($registrationSchoolsText === '') {
            $registrationSchoolsText = (string) ($data['registration_school_options_text'] ?? '');
        }
        $data['registration_school_options_json'] = json_encode(
            collect(preg_split('/\r\n|\r|\n/', $registrationSchoolsText) ?: [])
                ->map(fn ($school) => trim((string) $school))
                ->filter()
                ->unique()
                ->values()
                ->all()
        );
        if (!array_key_exists((string) ($data['app_locale'] ?? ''), $this->languageOptions())) {
            $data['app_locale'] = config('app.locale', 'en');
        }
        if (!array_key_exists((string) ($data['app_fallback_locale'] ?? ''), $this->languageOptions())) {
            $data['app_fallback_locale'] = config('app.fallback_locale', 'en');
        }
        $data['notification_templates_json'] = json_encode(
            collect($data['notification_templates'] ?? [])
                ->filter(fn ($row) => is_array($row) && !empty($row['event']))
                ->mapWithKeys(fn ($row) => [
                    (string) $row['event'] => [
                        'title' => trim((string) ($row['title'] ?? '')),
                        'message' => trim((string) ($row['message'] ?? '')),
                    ],
                ])
                ->all()
        );

        // Keep required list subset of visible fields.
        $visibleFields = collect($data['registration_fields'] ?? [])->values();
        $requiredFields = collect($data['registration_required_fields'] ?? [])->filter(fn ($f) => $visibleFields->contains($f))->values();
        $data['registration_required_fields_json'] = json_encode($requiredFields->all());

        // Keep only valid complete custom field rows.
        $customFields = collect($data['registration_custom_fields'] ?? [])
            ->map(function ($row) {
                $name = trim((string) ($row['name'] ?? ''));
                $label = trim((string) ($row['label'] ?? ''));
                $type = (string) ($row['type'] ?? 'text');

                if ($name === '' || $label === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
                    return null;
                }

                if (!in_array($type, ['text', 'email', 'tel', 'number', 'date', 'upload'], true)) {
                    $type = 'text';
                }

                return [
                    'name' => $name,
                    'label' => $label,
                    'type' => $type,
                    'placeholder' => (string) ($row['placeholder'] ?? ''),
                    'required' => (bool) ($row['required'] ?? false),
                ];
            })
            ->filter()
            ->unique('name')
            ->values()
            ->all();
        $data['registration_custom_fields_json'] = json_encode($customFields);

        foreach ($data as $key => $value) {
            if (!in_array($key, ['test_phone', 'sms_payload_template', 'sms_custom_headers', 'webhook_events', 'registration_fields', 'registration_required_fields', 'registration_custom_fields', 'notification_templates', 'trimester_eligible_schools_text', 'registration_school_options_text', 'school_catalog_text', 'booking_discount_rules'], true)) {
                SystemSetting::setSetting($key, $value);
            }
        }

        PaymentGateway::updateOrCreate(
            ['name' => 'Paystack'],
            [
                'public_key' => $data['paystack_public_key'] ?? null,
                'secret_key' => $data['paystack_secret_key'] ?? null,
                'is_active' => (bool) ($data['paystack_enabled'] ?? false),
                'transaction_fee' => 1.5,
            ]
        );

        PaymentGateway::updateOrCreate(
            ['name' => 'Flutterwave'],
            [
                'public_key' => $data['flutterwave_public_key'] ?? null,
                'secret_key' => $data['flutterwave_secret_key'] ?? null,
                'is_active' => (bool) ($data['flutterwave_enabled'] ?? false),
                'transaction_fee' => 2.0,
            ]
        );

        PaymentGateway::updateOrCreate(
            ['name' => 'Stripe'],
            [
                'public_key' => $data['stripe_public_key'] ?? null,
                'secret_key' => $data['stripe_secret_key'] ?? null,
                'is_active' => (bool) ($data['stripe_enabled'] ?? false),
                'transaction_fee' => 2.9,
            ]
        );

        PaymentGateway::updateOrCreate(
            ['name' => 'PayPal'],
            [
                'public_key' => $data['paypal_public_key'] ?? null,
                'secret_key' => $data['paypal_secret_key'] ?? null,
                'is_active' => (bool) ($data['paypal_enabled'] ?? false),
                'transaction_fee' => 3.5,
            ]
        );

        PaymentGateway::updateOrCreate(
            ['name' => 'Razorpay'],
            [
                'public_key' => $data['razorpay_public_key'] ?? null,
                'secret_key' => $data['razorpay_secret_key'] ?? null,
                'is_active' => (bool) ($data['razorpay_enabled'] ?? false),
                'transaction_fee' => 2.0,
            ]
        );

        PaymentGateway::updateOrCreate(
            ['name' => 'Square'],
            [
                'public_key' => $data['square_public_key'] ?? null,
                'secret_key' => $data['square_secret_key'] ?? null,
                'is_active' => (bool) ($data['square_enabled'] ?? false),
                'transaction_fee' => 2.6,
            ]
        );

        // Update .env file and runtime config
        $this->updateEnvironmentFile($data);

        Notification::make()
            ->success()
            ->title(__('Settings Saved'))
            ->body(__('Your system settings have been updated successfully.'))
            ->send();
    }

    private function cronPhpBinary(): string
    {
        $binary = trim((string) PHP_BINARY);
        return $binary !== '' ? $binary : 'php';
    }

    private function updateEnvironmentFile(array $data): void
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        // Mapping of form fields to .env variables
        $envMappings = [
            'app_name' => 'APP_NAME',
            'app_locale' => 'APP_LOCALE',
            'app_fallback_locale' => 'APP_FALLBACK_LOCALE',
            'system_currency' => 'APP_CURRENCY',
            'booking_period_type' => 'BOOKING_PERIOD_TYPE',
            'mail_mailer' => 'MAIL_MAILER',
            'smtp_host' => 'MAIL_HOST',
            'smtp_port' => 'MAIL_PORT',
            'smtp_username' => 'MAIL_USERNAME',
            'smtp_password' => 'MAIL_PASSWORD',
            'smtp_encryption' => 'MAIL_ENCRYPTION',
            'smtp_from_email' => 'MAIL_FROM_ADDRESS',
            'smtp_from_name' => 'MAIL_FROM_NAME',
        ];

        if (trim((string) ($data['smtp_from_email'] ?? '')) === '') {
            $data['smtp_from_email'] = (string) ($data['app_email'] ?? '');
        }
        if (trim((string) ($data['smtp_from_name'] ?? '')) === '') {
            $data['smtp_from_name'] = (string) ($data['app_name'] ?? '');
        }

        foreach ($envMappings as $formKey => $envKey) {
            if (isset($data[$formKey])) {
                $value = $data[$formKey];
                $envContent = $this->setEnvironmentValue($envContent, $envKey, $value);
            }
        }

        file_put_contents($envPath, $envContent);

        $fromEmail = trim((string) ($data['smtp_from_email'] ?? ''));
        $fromName = trim((string) ($data['smtp_from_name'] ?? ''));
        $resolvedFromEmail = $fromEmail !== '' ? $fromEmail : (string) ($data['app_email'] ?? config('mail.from.address'));
        $resolvedFromName = $fromName !== '' ? $fromName : (string) ($data['app_name'] ?? config('mail.from.name'));

        // Update runtime config
        Config::set('app.name', $data['app_name'] ?? config('app.name'));
        Config::set('app.locale', $data['app_locale'] ?? config('app.locale'));
        Config::set('app.fallback_locale', $data['app_fallback_locale'] ?? config('app.fallback_locale'));
        app()->setLocale((string) ($data['app_locale'] ?? config('app.locale')));
        app()->setFallbackLocale((string) ($data['app_fallback_locale'] ?? config('app.fallback_locale')));
        Config::set('app.currency', $data['system_currency'] ?? config('app.currency'));
        Config::set('app.booking_period_type', $data['booking_period_type'] ?? config('app.booking_period_type'));
        Config::set('mail.from.address', $resolvedFromEmail);
        Config::set('mail.from.name', $resolvedFromName);
        Config::set('mail.default', $data['mail_mailer'] ?? config('mail.default'));
        Config::set('mail.mailers.smtp.host', $data['smtp_host'] ?? config('mail.mailers.smtp.host'));
        Config::set('mail.mailers.smtp.port', $data['smtp_port'] ?? config('mail.mailers.smtp.port'));
        Config::set('mail.mailers.smtp.username', $data['smtp_username'] ?? config('mail.mailers.smtp.username'));
        Config::set('mail.mailers.smtp.password', $data['smtp_password'] ?? config('mail.mailers.smtp.password'));
        Config::set('mail.mailers.smtp.encryption', $data['smtp_encryption'] ?? config('mail.mailers.smtp.encryption'));
    }

    private function setEnvironmentValue(string $content, string $key, $value): string
    {
        $value = is_string($value) ? trim($value) : $value;
        
        // Escape quotes in the value
        if (is_string($value)) {
            $value = str_contains($value, ' ') ? "\"{$value}\"" : $value;
        }

        $pattern = "/^{$key}=.*/m";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "{$key}={$value}", $content);
        } else {
            $content .= "\n{$key}={$value}";
        }

        return $content;
    }

    public function testSMS(): void
    {
        $data = $this->form->getState();

        if (empty($data['test_phone'])) {
            Notification::make()
                ->warning()
                ->title(__('Phone Required'))
                ->body(__('Please enter a test phone number.'))
                ->send();
            return;
        }

        try {
            $payloadTemplate = $data['sms_payload_template'] ?? [];
            $payload = [];

            if (empty($payloadTemplate)) {
                $payload = [
                    'api_key' => $data['sms_api_key'],
                    'sender_id' => $data['sms_sender_id'],
                    'to' => $data['test_phone'],
                    'message' => 'Test SMS from Hostel Management System',
                ];
            } else {
                $replacements = [
                    'phone' => $data['test_phone'],
                    'to' => $data['test_phone'],
                    'message' => 'Test SMS from Hostel Management System',
                    'sender_id' => $data['sms_sender_id'] ?? '',
                    'from' => $data['sms_sender_id'] ?? '',
                    'api_key' => $data['sms_api_key'] ?? '',
                ];

                foreach ($payloadTemplate as $key => $value) {
                    $resolved = (string) $value;
                    foreach ($replacements as $placeholder => $replacement) {
                        $resolved = str_replace('{' . $placeholder . '}', (string) $replacement, $resolved);
                    }
                    $payload[$key] = $resolved;
                }
            }

            $headers = array_filter($data['sms_custom_headers'] ?? [], fn ($v) => $v !== null && $v !== '');
            $request = Http::withHeaders($headers);

            $response = strtoupper($data['sms_http_method'] ?? 'POST') === 'GET'
                ? $request->get($data['sms_url'], $payload)
                : $request->post($data['sms_url'], $payload);

            if ($response->successful()) {
                Notification::make()
                    ->success()
                    ->title(__('SMS Sent Successfully'))
                    ->body('Test SMS has been sent to ' . $data['test_phone'])
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title(__('SMS Failed'))
                    ->body('Failed to send SMS. Error: ' . $response->body())
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title(__('Error'))
                ->body('Error testing SMS: ' . $e->getMessage())
                ->send();
        }
    }

    public function testEmail(): void
    {
        $data = $this->form->getState();
        $to = $data['smtp_test_email'] ?? null;
        $mailer = (string) ($data['mail_mailer'] ?? 'smtp');

        if (empty($to)) {
            Notification::make()
                ->warning()
                ->title(__('Recipient Required'))
                ->body(__('Enter a test recipient email under SMTP Configuration.'))
                ->send();
            return;
        }

        try {
            Config::set('mail.default', $mailer);
            $fromEmail = trim((string) ($data['smtp_from_email'] ?? ''));
            $fromName = trim((string) ($data['smtp_from_name'] ?? ''));
            Config::set('mail.from.address', $fromEmail !== '' ? $fromEmail : ($data['app_email'] ?? config('mail.from.address')));
            Config::set('mail.from.name', $fromName !== '' ? $fromName : ($data['app_name'] ?? config('mail.from.name')));
            if ($mailer === 'smtp') {
                Config::set('mail.mailers.smtp.host', $data['smtp_host'] ?? config('mail.mailers.smtp.host'));
                Config::set('mail.mailers.smtp.port', $data['smtp_port'] ?? config('mail.mailers.smtp.port'));
                Config::set('mail.mailers.smtp.username', $data['smtp_username'] ?? config('mail.mailers.smtp.username'));
                Config::set('mail.mailers.smtp.password', $data['smtp_password'] ?? config('mail.mailers.smtp.password'));
                Config::set('mail.mailers.smtp.encryption', $data['smtp_encryption'] ?? config('mail.mailers.smtp.encryption'));
            }

            Mail::raw('This is a test email from Hostel Management System SMTP settings.', function ($message) use ($to) {
                $message->to($to)
                    ->subject('SMTP Test Email');
            });

            Notification::make()
                ->success()
                ->title(__('Email Sent'))
                ->body('Test email sent to ' . $to)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title(__('Email Test Failed'))
                ->body($e->getMessage())
            ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateApiKey')
                ->label(__('Generate API Key'))
                ->icon('heroicon-o-key')
                ->color('info')
                ->requiresConfirmation()
                ->action(fn () => $this->generateApiKey()),
            Action::make('testWebhook')
                ->label(__('Test Webhook'))
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->action(fn () => $this->testWebhook()),
            Action::make('testGatewayConnections')
                ->label(__('Test Gateway Connection'))
                ->icon('heroicon-o-signal')
                ->color('gray')
                ->action(fn () => $this->testGatewayConnections()),
        ];
    }

    public function generateApiKey(): void
    {
        $key = Str::random(48);
        $this->data['api_access_key'] = $key;
        SystemSetting::setSetting('api_access_key', $key);

        Notification::make()
            ->success()
            ->title(__('API Key Generated'))
            ->body(__('A new API key has been generated and saved.'))
            ->send();
    }

    public function testWebhook(): void
    {
        $state = $this->form->getState();
        $this->data = array_merge($this->data ?? [], $state);
        $this->save();

        $ok = app(\App\Services\OutboundWebhookService::class)->dispatch('system.webhook_test', [
            'message' => 'Webhook test from admin settings',
            'actor' => auth()->user()?->email,
        ], true);

        Notification::make()
            ->title($ok ? 'Webhook Test Sent' : 'Webhook Test Failed')
            ->body($ok ? 'Webhook endpoint acknowledged the test event.' : 'Webhook request failed. Check URL and logs.')
            ->color($ok ? 'success' : 'danger')
            ->send();
    }

    public function testGatewayConnections(): void
    {
        try {
            $results = app(PaymentGatewayDiagnosticsService::class)->testConfiguredGateways();

            $working = [];
            $issues = [];

            foreach ($results as $name => $result) {
                $isReady = (bool) ($result['initialization_ready'] && $result['verification_ready']);
                if ($isReady) {
                    $working[] = strtoupper((string) $name);
                    continue;
                }

                $issues[] = strtoupper((string) $name) . ': ' . ($result['message'] ?? 'Configuration issue');
            }

            $allReady = count($issues) === 0;

            $summary = [
                'Working: ' . (!empty($working) ? implode(', ', $working) : 'None'),
                'Needs attention: ' . (!empty($issues) ? implode(' | ', $issues) : 'None'),
            ];

            Notification::make()
                ->title($allReady ? 'Gateway Diagnostics Passed' : 'Gateway Diagnostics Found Issues')
                ->body(implode("\n", $summary))
                ->color($allReady ? 'success' : 'warning')
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->danger()
                ->title(__('Gateway Diagnostics Failed'))
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Settings'))
                ->submit('save'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function languageOptions(): array
    {
        return [
            'en' => '🇺🇸 English',
            'fr' => '🇫🇷 French (Français)',
            'es' => '🇪🇸 Spanish (Español)',
            'de' => '🇩🇪 German (Deutsch)',
            'pt' => '🇵🇹 Portuguese (Português)',
            'ar' => '🇸🇦 Arabic (العربية) - RTL',
            'he' => '🇮🇱 Hebrew (עברית) - RTL',
            'ur' => '🇵🇰 Urdu (اردو) - RTL',
            'hi' => '🇮🇳 Hindi (हिन्दी)',
            'zh_CN' => '🇨🇳 Chinese Simplified (简体中文)',
        ];
    }

    /**
     * @param array<int, mixed> $rows
     * @return array<int, array{locale: string, key: string, value: string}>
     */
    private function sanitizeCustomTranslations(array $rows): array
    {
        $allowedLocales = array_keys($this->languageOptions());

        return collect($rows)
            ->map(function ($row) use ($allowedLocales) {
                if (!is_array($row)) {
                    return null;
                }

                $locale = (string) ($row['locale'] ?? '');
                $key = trim((string) ($row['key'] ?? ''));
                $value = $this->normalizeTranslationValue((string) ($row['value'] ?? ''));

                if (!in_array($locale, $allowedLocales, true) || $key === '' || $value === '') {
                    return null;
                }

                return [
                    'locale' => $locale,
                    'key' => $key,
                    'value' => $value,
                ];
            })
            ->filter()
            ->unique(fn ($row) => $row['locale'] . '|' . $row['key'])
            ->values()
            ->all();
    }

    /**
     * Keep the editor responsive by loading only a manageable subset into UI state.
     *
     * @param array<int, array{locale: string, key: string, value: string}> $rows
     * @return array<int, array{locale: string, key: string, value: string}>
     */
    private function subsetCustomTranslationsForUi(array $rows, string $preferredLocale, int $limit = 400): array
    {
        $sanitized = $this->sanitizeCustomTranslations($rows);
        $preferred = [];
        $others = [];

        foreach ($sanitized as $row) {
            if (($row['locale'] ?? '') === $preferredLocale) {
                $preferred[] = $row;
            } else {
                $others[] = $row;
            }
        }

        $subset = array_merge($preferred, $others);

        return array_slice($subset, 0, max(100, $limit));
    }

    /**
     * @param array<int, mixed> $rows
     * @param array<int, string> $allowedKeys
     * @return array<int, array{locale: string, key: string, value: string}>
     */
    private function sanitizeTranslationEditorRows(array $rows, string $locale, array $allowedKeys): array
    {
        $allowedLocales = array_keys($this->languageOptions());
        if (!in_array($locale, $allowedLocales, true)) {
            return [];
        }

        $allowedKeyMap = array_fill_keys($allowedKeys, true);

        return collect($rows)
            ->map(function ($row) use ($locale, $allowedKeyMap) {
                if (!is_array($row)) {
                    return null;
                }

                $key = trim((string) ($row['key'] ?? ''));
                $value = $this->normalizeTranslationValue((string) ($row['value'] ?? ''));
                if ($key === '' || !isset($allowedKeyMap[$key])) {
                    return null;
                }

                if ($value === '') {
                    return [
                        'locale' => $locale,
                        'key' => $key,
                        'value' => '',
                    ];
                }

                return [
                    'locale' => $locale,
                    'key' => $key,
                    'value' => $value,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param array<int, array{locale: string, key: string, value: string}> $baseRows
     * @param array<int, array{locale: string, key: string, value: string}> $incomingRows
     * @return array<int, array{locale: string, key: string, value: string}>
     */
    private function mergeTranslationRows(array $baseRows, array $incomingRows): array
    {
        $merged = [];

        foreach ($baseRows as $row) {
            $id = $row['locale'] . '|' . $row['key'];
            $merged[$id] = $row;
        }

        foreach ($incomingRows as $row) {
            $id = $row['locale'] . '|' . $row['key'];
            if (trim($row['value']) === '') {
                unset($merged[$id]);
                continue;
            }
            $merged[$id] = $row;
        }

        return array_values($merged);
    }

    /**
     * @param array<int, array{locale: string, key: string, value: string}> $customRows
     * @return array<int, array{key: string, value: string}>
     */
    private function buildTranslationEditorRows(
        string $locale,
        array $customRows,
        string $search = '',
        int $page = 1,
        int $perPage = 50
    ): array
    {
        $catalog = $this->translationKeyCatalog();
        $starter = $this->starterPackMapForLocale($locale);
        $existing = [];

        foreach ($customRows as $row) {
            if (($row['locale'] ?? '') !== $locale) {
                continue;
            }
            $key = (string) ($row['key'] ?? '');
            $value = (string) ($row['value'] ?? '');
            if ($key !== '') {
                $existing[$key] = $value;
            }
        }

        $needle = mb_strtolower(trim($search));
        $filteredKeys = [];
        foreach ($catalog as $key) {
            if ($needle !== '' && !str_contains(mb_strtolower($key), $needle)) {
                continue;
            }
            $filteredKeys[] = $key;
        }

        $page = max(1, $page);
        $perPage = max(25, min(200, $perPage));
        $offset = ($page - 1) * $perPage;
        $slice = array_slice($filteredKeys, $offset, $perPage);

        return collect($slice)
            ->map(fn (string $key) => [
                'key' => $key,
                'value' => $existing[$key] ?? ($starter[$key] ?? ''),
            ])
            ->values()
            ->all();
    }

    private function translationEditorTotalCount(string $locale, string $search = ''): int
    {
        $catalog = $this->translationKeyCatalog();
        $needle = mb_strtolower(trim($search));

        if ($needle === '') {
            return count($catalog);
        }

        $count = 0;
        foreach ($catalog as $key) {
            if (str_contains(mb_strtolower($key), $needle)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return array<int, string>
     */
    private function translationKeyCatalog(): array
    {
        if (is_array($this->translationCatalogCache)) {
            return $this->translationCatalogCache;
        }
        $cachedCatalog = Cache::get('settings.translation_catalog.v2');
        if (is_array($cachedCatalog) && !empty($cachedCatalog)) {
            $this->translationCatalogCache = $cachedCatalog;
            return $cachedCatalog;
        }

        $defaults = [
            'Dashboard',
            'Settings',
            'Profile',
            'Password',
            'Appearance',
            'Two-Factor Auth',
            'Log in',
            'Sign up',
            'Forgot password',
            'Reset password',
            'Create account',
            'Save',
            'Saved.',
            'Delete account',
            'Cancel',
            'Email',
            'Password',
            'Confirm password',
            'Remember me',
            'Log Out',
            'Repository',
            'Documentation',
            'Search',
            'Platform',
            'Light',
            'Dark',
            'System',
            'Current password',
            'New password',
            'Update password',
            'Enable 2FA',
            'Disable 2FA',
            'Back',
            'Confirm',
            'Continue',
            'Authentication Code',
            'Recovery Code',
        ];
        $defaults = array_merge($defaults, $this->phaseTwoBaseTerms());
        $defaults = array_merge($defaults, $this->phaseTwoBResourceTerms());
        $defaults = array_merge($defaults, $this->resourceSpecificTermCatalog());

        $paths = [resource_path('views'), app_path()];
        $keys = $defaults;

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                    continue;
                }
                $ext = strtolower((string) $file->getExtension());
                if (!in_array($ext, ['php', 'blade.php'], true)) {
                    if (!str_ends_with((string) $file->getFilename(), '.blade.php')) {
                        continue;
                    }
                }

                $content = @file_get_contents($file->getPathname());
                if ($content === false) {
                    continue;
                }

                preg_match_all("/__\\(\\s*'((?:\\\\'|[^'])+)'\\s*\\)/", $content, $singleMatches);
                preg_match_all('/__\\(\\s*"((?:\\\\"|[^"])+)"\\s*\\)/', $content, $doubleMatches);
                preg_match_all("/@lang\\(\\s*'((?:\\\\'|[^'])+)'\\s*\\)/", $content, $langSingle);
                preg_match_all('/@lang\\(\\s*"((?:\\\\"|[^"])+)"\\s*\\)/', $content, $langDouble);

                foreach ([$singleMatches[1] ?? [], $doubleMatches[1] ?? [], $langSingle[1] ?? [], $langDouble[1] ?? []] as $matchSet) {
                    foreach ($matchSet as $raw) {
                        $value = trim(str_replace(["\\'", '\\"'], ["'", '"'], (string) $raw));
                        if ($value !== '') {
                            $keys[] = $value;
                        }
                    }
                }
            }
        }

        $keys = array_values(array_unique($keys));
        sort($keys);

        Cache::put('settings.translation_catalog.v2', $keys, now()->addHours(6));
        $this->translationCatalogCache = $keys;
        return $keys;
    }

    private function normalizeTranslationValue(string $value): string
    {
        $value = trim($value);

        // Allow admins to type {name} while Laravel placeholders use :name.
        return (string) preg_replace('/\{([a-zA-Z0-9_]+)\}/', ':$1', $value);
    }

    /**
     * @param array<int, array{locale: string, key: string, value: string}> $rows
     * @param array<int, string> $locales
     * @return array<int, array{locale: string, key: string, value: string}>
     */
    private function fillMissingTranslationsForLocales(array $rows, array $locales): array
    {
        $normalizedLocales = collect($locales)
            ->filter(fn ($locale) => is_string($locale) && array_key_exists($locale, $this->languageOptions()))
            ->unique()
            ->values()
            ->all();

        if (empty($normalizedLocales)) {
            return $rows;
        }

        $existing = $this->sanitizeCustomTranslations($rows);
        $packs = $this->fullLanguagePackRowsForLocales($normalizedLocales);

        return $this->mergeTranslationRows($packs, $existing);
    }

    /**
     * @param array<int, string> $locales
     * @return array<int, array{locale: string, key: string, value: string}>
     */
    private function fullLanguagePackRowsForLocales(array $locales): array
    {
        $catalog = $this->translationKeyCatalog();
        $rows = [];

        foreach ($locales as $locale) {
            if (!is_string($locale) || !array_key_exists($locale, $this->languageOptions())) {
                continue;
            }

            foreach ($catalog as $key) {
                $rows[] = [
                    'locale' => $locale,
                    'key' => $key,
                    'value' => $this->translateKeyForLocale($locale, $key),
                ];
            }
        }

        return $this->sanitizeCustomTranslations($rows);
    }

    private function translateKeyForLocale(string $locale, string $key): string
    {
        if ($locale === 'en') {
            return $key;
        }

        $starter = $this->starterPackMapForLocale($locale);
        if (isset($starter[$key]) && trim((string) $starter[$key]) !== '') {
            return (string) $starter[$key];
        }

        $fromGlossary = $this->translateWithGlossary($locale, $key);
        if ($fromGlossary !== $key) {
            return $fromGlossary;
        }

        // Keep placeholders intact while still ensuring a full language pack row exists.
        return $key;
    }

    private function translateWithGlossary(string $locale, string $text): string
    {
        $glossary = $this->translationGlossary($locale);
        if (empty($glossary)) {
            return $text;
        }

        $parts = preg_split('/([\\s\\/\\-\\(\\):,]+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($parts)) {
            return $text;
        }

        $translated = [];
        foreach ($parts as $part) {
            if ($part === '' || preg_match('/^[\\s\\/\\-\\(\\):,]+$/u', $part)) {
                $translated[] = $part;
                continue;
            }

            if (str_starts_with($part, ':') || str_starts_with($part, '{') || str_starts_with($part, '}')) {
                $translated[] = $part;
                continue;
            }

            $lookup = mb_strtolower($part);
            $translated[] = $glossary[$lookup] ?? $part;
        }

        return implode('', $translated);
    }

    /**
     * @return array<string, string>
     */
    private function translationGlossary(string $locale): array
    {
        return match ($locale) {
            'fr' => [
                'student' => 'étudiant', 'students' => 'étudiants', 'manager' => 'gestionnaire', 'managers' => 'gestionnaires',
                'hostel' => 'foyer', 'hostels' => 'foyers', 'room' => 'chambre', 'rooms' => 'chambres', 'bed' => 'lit', 'beds' => 'lits',
                'booking' => 'réservation', 'bookings' => 'réservations', 'payment' => 'paiement', 'payments' => 'paiements',
                'complaint' => 'plainte', 'complaints' => 'plaintes', 'asset' => 'actif', 'assets' => 'actifs',
                'create' => 'créer', 'edit' => 'modifier', 'delete' => 'supprimer', 'view' => 'voir', 'import' => 'importer', 'export' => 'exporter',
                'approve' => 'approuver', 'reject' => 'rejeter', 'approved' => 'approuvé', 'rejected' => 'rejeté', 'pending' => 'en attente',
                'settings' => 'paramètres', 'system' => 'système', 'content' => 'contenu', 'image' => 'image', 'history' => 'historique',
            ],
            'es' => [
                'student' => 'estudiante', 'students' => 'estudiantes', 'manager' => 'gestor', 'managers' => 'gestores',
                'hostel' => 'hostal', 'hostels' => 'hostales', 'room' => 'habitación', 'rooms' => 'habitaciones', 'bed' => 'cama', 'beds' => 'camas',
                'booking' => 'reserva', 'bookings' => 'reservas', 'payment' => 'pago', 'payments' => 'pagos',
                'complaint' => 'queja', 'complaints' => 'quejas', 'asset' => 'activo', 'assets' => 'activos',
                'create' => 'crear', 'edit' => 'editar', 'delete' => 'eliminar', 'view' => 'ver', 'import' => 'importar', 'export' => 'exportar',
                'approve' => 'aprobar', 'reject' => 'rechazar', 'approved' => 'aprobado', 'rejected' => 'rechazado', 'pending' => 'pendiente',
                'settings' => 'configuración', 'system' => 'sistema', 'content' => 'contenido', 'image' => 'imagen', 'history' => 'historial',
            ],
            'de' => [
                'student' => 'student', 'students' => 'studenten', 'manager' => 'manager', 'managers' => 'manager',
                'hostel' => 'wohnheim', 'hostels' => 'wohnheime', 'room' => 'zimmer', 'rooms' => 'zimmer', 'bed' => 'bett', 'beds' => 'betten',
                'booking' => 'buchung', 'bookings' => 'buchungen', 'payment' => 'zahlung', 'payments' => 'zahlungen',
                'complaint' => 'beschwerde', 'complaints' => 'beschwerden', 'asset' => 'anlage', 'assets' => 'anlagen',
                'create' => 'erstellen', 'edit' => 'bearbeiten', 'delete' => 'löschen', 'view' => 'anzeigen', 'import' => 'importieren', 'export' => 'exportieren',
                'approve' => 'genehmigen', 'reject' => 'ablehnen', 'approved' => 'genehmigt', 'rejected' => 'abgelehnt', 'pending' => 'ausstehend',
                'settings' => 'einstellungen', 'system' => 'system', 'content' => 'inhalt', 'image' => 'bild', 'history' => 'verlauf',
            ],
            'pt' => [
                'student' => 'estudante', 'students' => 'estudantes', 'manager' => 'gerente', 'managers' => 'gerentes',
                'hostel' => 'alojamento', 'hostels' => 'alojamentos', 'room' => 'quarto', 'rooms' => 'quartos', 'bed' => 'cama', 'beds' => 'camas',
                'booking' => 'reserva', 'bookings' => 'reservas', 'payment' => 'pagamento', 'payments' => 'pagamentos',
                'complaint' => 'reclamação', 'complaints' => 'reclamações', 'asset' => 'ativo', 'assets' => 'ativos',
                'create' => 'criar', 'edit' => 'editar', 'delete' => 'excluir', 'view' => 'ver', 'import' => 'importar', 'export' => 'exportar',
                'approve' => 'aprovar', 'reject' => 'rejeitar', 'approved' => 'aprovado', 'rejected' => 'rejeitado', 'pending' => 'pendente',
                'settings' => 'configurações', 'system' => 'sistema', 'content' => 'conteúdo', 'image' => 'imagem', 'history' => 'histórico',
            ],
            'ar' => [
                'student' => 'طالب', 'students' => 'طلاب', 'manager' => 'مدير', 'managers' => 'مديرون',
                'hostel' => 'سكن', 'hostels' => 'سكنات', 'room' => 'غرفة', 'rooms' => 'غرف', 'bed' => 'سرير', 'beds' => 'أسرة',
                'booking' => 'حجز', 'bookings' => 'حجوزات', 'payment' => 'دفع', 'payments' => 'مدفوعات',
                'complaint' => 'شكوى', 'complaints' => 'شكاوى', 'asset' => 'أصل', 'assets' => 'أصول',
                'create' => 'إنشاء', 'edit' => 'تعديل', 'delete' => 'حذف', 'view' => 'عرض', 'import' => 'استيراد', 'export' => 'تصدير',
                'approve' => 'اعتماد', 'reject' => 'رفض', 'approved' => 'معتمد', 'rejected' => 'مرفوض', 'pending' => 'انتظار',
                'settings' => 'إعدادات', 'system' => 'النظام', 'content' => 'محتوى', 'image' => 'صورة', 'history' => 'سجل',
            ],
            'he' => [
                'student' => 'סטודנט', 'students' => 'סטודנטים', 'manager' => 'מנהל', 'managers' => 'מנהלים',
                'hostel' => 'מעון', 'hostels' => 'מעונות', 'room' => 'חדר', 'rooms' => 'חדרים', 'bed' => 'מיטה', 'beds' => 'מיטות',
                'booking' => 'הזמנה', 'bookings' => 'הזמנות', 'payment' => 'תשלום', 'payments' => 'תשלומים',
                'complaint' => 'תלונה', 'complaints' => 'תלונות', 'asset' => 'נכס', 'assets' => 'נכסים',
                'create' => 'יצירה', 'edit' => 'עריכה', 'delete' => 'מחיקה', 'view' => 'צפייה', 'import' => 'ייבוא', 'export' => 'ייצוא',
                'approve' => 'אישור', 'reject' => 'דחייה', 'approved' => 'מאושר', 'rejected' => 'נדחה', 'pending' => 'ממתין',
                'settings' => 'הגדרות', 'system' => 'מערכת', 'content' => 'תוכן', 'image' => 'תמונה', 'history' => 'היסטוריה',
            ],
            'ur' => [
                'student' => 'طالب علم', 'students' => 'طلبہ', 'manager' => 'منیجر', 'managers' => 'منیجرز',
                'hostel' => 'ہاسٹل', 'hostels' => 'ہاسٹلز', 'room' => 'کمرہ', 'rooms' => 'کمرے', 'bed' => 'بیڈ', 'beds' => 'بیڈز',
                'booking' => 'بکنگ', 'bookings' => 'بکنگز', 'payment' => 'ادائیگی', 'payments' => 'ادائیگیاں',
                'complaint' => 'شکایت', 'complaints' => 'شکایات', 'asset' => 'اثاثہ', 'assets' => 'اثاثے',
                'create' => 'بنائیں', 'edit' => 'ترمیم', 'delete' => 'حذف', 'view' => 'دیکھیں', 'import' => 'درآمد', 'export' => 'برآمد',
                'approve' => 'منظور', 'reject' => 'مسترد', 'approved' => 'منظور شدہ', 'rejected' => 'مسترد', 'pending' => 'زیر التوا',
                'settings' => 'سیٹنگز', 'system' => 'سسٹم', 'content' => 'مواد', 'image' => 'تصویر', 'history' => 'تاریخچہ',
            ],
            'hi' => [
                'student' => 'छात्र', 'students' => 'छात्र', 'manager' => 'प्रबंधक', 'managers' => 'प्रबंधक',
                'hostel' => 'छात्रावास', 'hostels' => 'छात्रावास', 'room' => 'कमरा', 'rooms' => 'कमरे', 'bed' => 'बेड', 'beds' => 'बेड',
                'booking' => 'बुकिंग', 'bookings' => 'बुकिंग्स', 'payment' => 'भुगतान', 'payments' => 'भुगतान',
                'complaint' => 'शिकायत', 'complaints' => 'शिकायतें', 'asset' => 'संपत्ति', 'assets' => 'संपत्तियां',
                'create' => 'बनाएं', 'edit' => 'संपादित', 'delete' => 'हटाएं', 'view' => 'देखें', 'import' => 'आयात', 'export' => 'निर्यात',
                'approve' => 'स्वीकृत', 'reject' => 'अस्वीकृत', 'approved' => 'स्वीकृत', 'rejected' => 'अस्वीकृत', 'pending' => 'लंबित',
                'settings' => 'सेटिंग्स', 'system' => 'सिस्टम', 'content' => 'सामग्री', 'image' => 'छवि', 'history' => 'इतिहास',
            ],
            'zh_CN' => [
                'student' => '学生', 'students' => '学生', 'manager' => '经理', 'managers' => '经理',
                'hostel' => '宿舍', 'hostels' => '宿舍', 'room' => '房间', 'rooms' => '房间', 'bed' => '床位', 'beds' => '床位',
                'booking' => '预订', 'bookings' => '预订', 'payment' => '支付', 'payments' => '支付',
                'complaint' => '投诉', 'complaints' => '投诉', 'asset' => '资产', 'assets' => '资产',
                'create' => '创建', 'edit' => '编辑', 'delete' => '删除', 'view' => '查看', 'import' => '导入', 'export' => '导出',
                'approve' => '批准', 'reject' => '拒绝', 'approved' => '已批准', 'rejected' => '已拒绝', 'pending' => '待处理',
                'settings' => '设置', 'system' => '系统', 'content' => '内容', 'image' => '图片', 'history' => '历史',
            ],
            default => [],
        };
    }

    public function installFullLanguagePacks(): void
    {
        $state = $this->form->getState();
        $enabled = collect($state['enabled_locales'] ?? [])
            ->filter(fn ($locale) => is_string($locale) && array_key_exists($locale, $this->languageOptions()))
            ->unique()
            ->values()
            ->all();

        if (empty($enabled)) {
            $enabled = ['en'];
        }

        $rows = $this->fullLanguagePackRowsForLocales($enabled);
        $current = $this->sanitizeCustomTranslations($state['custom_translations'] ?? []);
        $merged = $this->mergeTranslationRows($rows, $current);

        $locale = (string) ($state['translation_locale'] ?? $state['app_locale'] ?? 'en');
        $search = (string) ($state['translation_search'] ?? '');
        $this->data['custom_translations'] = $this->subsetCustomTranslationsForUi($merged, $locale);
        $this->data['translation_editor'] = $this->buildTranslationEditorRows($locale, $merged, $search);
        SystemTranslationStore::write($merged);

        Notification::make()
            ->success()
            ->title(__('Full language pack installed'))
            ->body(__('All discovered keys are now available for enabled languages.'))
            ->send();
    }

    /**
     * @return array<string, string>
     */
    private function starterPackMapForLocale(string $locale): array
    {
        $core = match ($locale) {
            'fr' => [
                'Dashboard' => 'Tableau de bord',
                'Settings' => 'Paramètres',
                'Profile' => 'Profil',
                'Password' => 'Mot de passe',
                'Appearance' => 'Apparence',
                'Log in' => 'Connexion',
                'Sign up' => 'Inscription',
                'Forgot password' => 'Mot de passe oublié',
                'Reset password' => 'Réinitialiser le mot de passe',
                'Save' => 'Enregistrer',
                'Log Out' => 'Déconnexion',
            ],
            'es' => [
                'Dashboard' => 'Panel',
                'Settings' => 'Configuración',
                'Profile' => 'Perfil',
                'Password' => 'Contraseña',
                'Appearance' => 'Apariencia',
                'Log in' => 'Iniciar sesión',
                'Sign up' => 'Registrarse',
                'Forgot password' => 'Olvidé mi contraseña',
                'Reset password' => 'Restablecer contraseña',
                'Save' => 'Guardar',
                'Log Out' => 'Cerrar sesión',
            ],
            'de' => [
                'Dashboard' => 'Dashboard',
                'Settings' => 'Einstellungen',
                'Profile' => 'Profil',
                'Password' => 'Passwort',
                'Appearance' => 'Darstellung',
                'Log in' => 'Anmelden',
                'Sign up' => 'Registrieren',
                'Forgot password' => 'Passwort vergessen',
                'Reset password' => 'Passwort zurücksetzen',
                'Save' => 'Speichern',
                'Log Out' => 'Abmelden',
            ],
            'pt' => [
                'Dashboard' => 'Painel',
                'Settings' => 'Configurações',
                'Profile' => 'Perfil',
                'Password' => 'Senha',
                'Appearance' => 'Aparência',
                'Log in' => 'Entrar',
                'Sign up' => 'Registrar-se',
                'Forgot password' => 'Esqueci a senha',
                'Reset password' => 'Redefinir senha',
                'Save' => 'Salvar',
                'Log Out' => 'Sair',
            ],
            'ar' => [
                'Dashboard' => 'لوحة التحكم',
                'Settings' => 'الإعدادات',
                'Profile' => 'الملف الشخصي',
                'Password' => 'كلمة المرور',
                'Appearance' => 'المظهر',
                'Log in' => 'تسجيل الدخول',
                'Sign up' => 'إنشاء حساب',
                'Forgot password' => 'نسيت كلمة المرور',
                'Reset password' => 'إعادة تعيين كلمة المرور',
                'Save' => 'حفظ',
                'Log Out' => 'تسجيل الخروج',
            ],
            'he' => [
                'Dashboard' => 'לוח בקרה',
                'Settings' => 'הגדרות',
                'Profile' => 'פרופיל',
                'Password' => 'סיסמה',
                'Appearance' => 'מראה',
                'Log in' => 'התחברות',
                'Sign up' => 'הרשמה',
                'Forgot password' => 'שכחתי סיסמה',
                'Reset password' => 'איפוס סיסמה',
                'Save' => 'שמירה',
                'Log Out' => 'התנתקות',
            ],
            'ur' => [
                'Dashboard' => 'ڈیش بورڈ',
                'Settings' => 'ترتیبات',
                'Profile' => 'پروفائل',
                'Password' => 'پاس ورڈ',
                'Appearance' => 'ظاہری شکل',
                'Log in' => 'لاگ ان',
                'Sign up' => 'سائن اپ',
                'Forgot password' => 'پاس ورڈ بھول گئے',
                'Reset password' => 'پاس ورڈ ری سیٹ کریں',
                'Save' => 'محفوظ کریں',
                'Log Out' => 'لاگ آؤٹ',
            ],
            'hi' => [
                'Dashboard' => 'डैशबोर्ड',
                'Settings' => 'सेटिंग्स',
                'Profile' => 'प्रोफाइल',
                'Password' => 'पासवर्ड',
                'Appearance' => 'रूप',
                'Log in' => 'लॉग इन',
                'Sign up' => 'साइन अप',
                'Forgot password' => 'पासवर्ड भूल गए',
                'Reset password' => 'पासवर्ड रीसेट करें',
                'Save' => 'सहेजें',
                'Log Out' => 'लॉग आउट',
            ],
            'zh_CN' => [
                'Dashboard' => '仪表板',
                'Settings' => '设置',
                'Profile' => '个人资料',
                'Password' => '密码',
                'Appearance' => '外观',
                'Log in' => '登录',
                'Sign up' => '注册',
                'Forgot password' => '忘记密码',
                'Reset password' => '重置密码',
                'Save' => '保存',
                'Log Out' => '退出登录',
            ],
            default => [],
        };

        return array_merge($core, $this->phaseTwoLocaleMap($locale));
    }

    /**
     * @return array<int, string>
     */
    private function phaseTwoBaseTerms(): array
    {
        return [
            'Menu',
            'Navigation',
            'Home',
            'Admin',
            'Manager',
            'Managers',
            'Student',
            'Students',
            'User',
            'Users',
            'Hostel',
            'Hostels',
            'Room',
            'Rooms',
            'Bed Space',
            'Booking',
            'Bookings',
            'Pending Bookings',
            'Completed Bookings',
            'Payment',
            'Payments',
            'Manual Payments',
            'Complaints',
            'Open Complaints',
            'System Settings',
            'General',
            'Site Configuration',
            'Registration Form',
            'Localization',
            'Website Content',
            'SMS Configuration',
            'Email Configuration',
            'Payment Gateways',
            'Integrations',
            'Cron / Scheduler',
            'Notifications',
            'Webhook',
            'Webhook URL',
            'Webhook Events',
            'API',
            'API Documentation',
            'API Access Key',
            'Addon',
            'Addons',
            'Asset',
            'Assets',
            'Asset Management',
            'Asset Number',
            'Asset Issue',
            'Supplier',
            'Invoice/Reference',
            'Maintenance Schedule',
            'Subscription',
            'Subscription Expiry',
            'Expiry Date',
            'From Date',
            'To Date',
            'Status',
            'Active',
            'Inactive',
            'Approved',
            'Rejected',
            'Pending',
            'Enable',
            'Disable',
            'Enabled',
            'Disabled',
            'Save Settings',
            'Test Webhook',
            'Test Gateway Connection',
            'Gateway Diagnostics',
            'Currency',
            'Amount',
            'Total Paid',
            'Price',
            'Acquisition Cost',
            'Reference',
            'Description',
            'Name',
            'Phone Number',
            'Address',
            'Email Address',
            'Role',
            'Actions',
            'Create',
            'Add New',
            'Edit',
            'Update',
            'Delete',
            'View',
            'Search',
            'Filter',
            'Export',
            'Import',
            'Backup',
            'Backups',
            'Restore',
            'File Manager',
            'Upload',
            'Image',
            'Images',
            'Logo',
            'Favicon',
            'Dark Mode',
            'Light Mode',
            'Theme',
            'Language',
            'Default Language',
            'Fallback Language',
            'Enabled Languages',
            'Notification Bell',
            'Mark as Read',
            'Mark all as read',
            'No notifications',
            'Welcome',
            'Book Room',
            'Browse Rooms',
            'Current Stay',
            'Check-In',
            'Check-Out',
            'Hostel Name',
            'Room Number',
            'Bed Number',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function phaseTwoBResourceTerms(): array
    {
        return [
            'Academic Session',
            'Academic Sessions',
            'Session Name',
            'Start Year',
            'End Year',
            'Semester',
            'Semesters',
            'Allocation',
            'Allocations',
            'Allocation Details',
            'Bed Information',
            'Bed Images',
            'Currently Occupied',
            'Occupying Student',
            'Approved for Student Booking',
            'Complaint Details',
            'Manager Response',
            'Assign to Manager',
            'Hostel Change Requests',
            'Current Hostel',
            'Requested Hostel',
            'Manager Note',
            'Admin Note',
            'Footer Links',
            'Link Information',
            'Link Title',
            'Sort Order',
            'General Settings',
            'Payment Information',
            'Payment Gateway',
            'Booking History',
            'View Receipt',
            'Room Information',
            'Room Images',
            'Room Cover Image (Optional)',
            'Student Information',
            'Guardian Information',
            'Additional Registration Data',
            'Custom Registration Fields',
            'Export Excel',
            'Import Excel',
            'Student Excel File',
            'Addon ZIP Package',
            'Package Upload',
            'Addon Details',
            'Uploaded By',
            'Activation failed',
            'Deactivation failed',
            'Delete failed',
            'Send SMS',
            'Send Email',
            'Email Subject',
            'Email Message',
            'SMS Broadcast',
            'Template Name',
            'Value Editor',
            'Content Definition',
            'Content Key',
            'Text Value',
            'HTML Content',
            'Section Content',
            'Section Image',
            'Asset Image',
            'Issue Reports',
            'Move Hostel',
            'Destination Hostel',
            'Days Left',
            'Requested By',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resourceSpecificTermCatalog(): array
    {
        if (is_array($this->resourceTermCatalogCache)) {
            return $this->resourceTermCatalogCache;
        }
        $cachedTerms = Cache::get('settings.resource_term_catalog.v2');
        if (is_array($cachedTerms) && !empty($cachedTerms)) {
            $this->resourceTermCatalogCache = $cachedTerms;
            return $cachedTerms;
        }

        $paths = [
            app_path('Filament/Resources'),
            app_path('Filament/Pages'),
        ];
        $patterns = [
            "/->label\\('([^']+)'\\)/",
            "/->title\\('([^']+)'\\)/",
            "/->description\\('([^']+)'\\)/",
            "/->placeholder\\('([^']+)'\\)/",
            "/Tab::make\\('([^']+)'\\)/",
            "/Section::make\\('([^']+)'\\)/",
            "/navigationLabel\\s*=\\s*'([^']+)'/",
            "/modelLabel\\s*=\\s*'([^']+)'/",
            "/pluralModelLabel\\s*=\\s*'([^']+)'/",
        ];

        $terms = [];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $content = @file_get_contents($file->getPathname());
                if ($content === false) {
                    continue;
                }

                foreach ($patterns as $pattern) {
                    if (!preg_match_all($pattern, $content, $matches)) {
                        continue;
                    }

                    foreach (($matches[1] ?? []) as $raw) {
                        $value = trim(str_replace(["\\'", '\\"'], ["'", '"'], (string) $raw));
                        if ($value !== '' && !str_contains($value, '{{')) {
                            $terms[] = $value;
                        }
                    }
                }
            }
        }

        $terms = array_values(array_unique($terms));
        sort($terms);
        Cache::put('settings.resource_term_catalog.v2', $terms, now()->addHours(6));
        $this->resourceTermCatalogCache = $terms;

        return $terms;
    }

    /**
     * @return array<string, string>
     */
    private function phaseTwoLocaleMap(string $locale): array
    {
        return match ($locale) {
            'fr' => [
                'Menu' => 'Menu',
                'Navigation' => 'Navigation',
                'Home' => 'Accueil',
                'Admin' => 'Administrateur',
                'Manager' => 'Gestionnaire',
                'Managers' => 'Gestionnaires',
                'Student' => 'Étudiant',
                'Students' => 'Étudiants',
                'User' => 'Utilisateur',
                'Users' => 'Utilisateurs',
                'Hostel' => 'Foyer',
                'Hostels' => 'Foyers',
                'Room' => 'Chambre',
                'Rooms' => 'Chambres',
                'Bed Space' => 'Espace lit',
                'Booking' => 'Réservation',
                'Bookings' => 'Réservations',
                'Payment' => 'Paiement',
                'Payments' => 'Paiements',
                'System Settings' => 'Paramètres système',
                'General' => 'Général',
                'Localization' => 'Localisation',
                'Website Content' => 'Contenu du site',
                'Payment Gateways' => 'Passerelles de paiement',
                'Integrations' => 'Intégrations',
                'Notifications' => 'Notifications',
                'Webhook' => 'Webhook',
                'API' => 'API',
                'Addons' => 'Modules complémentaires',
                'Asset Management' => 'Gestion des actifs',
                'Assets' => 'Actifs',
                'Backup' => 'Sauvegarde',
                'Restore' => 'Restaurer',
                'File Manager' => 'Gestionnaire de fichiers',
                'Upload' => 'Téléverser',
                'Dark Mode' => 'Mode sombre',
                'Light Mode' => 'Mode clair',
                'Language' => 'Langue',
                'Default Language' => 'Langue par défaut',
                'Enabled Languages' => 'Langues activées',
                'Search' => 'Rechercher',
                'Filter' => 'Filtrer',
                'Create' => 'Créer',
                'Edit' => 'Modifier',
                'Delete' => 'Supprimer',
                'View' => 'Voir',
                'Status' => 'Statut',
                'Approved' => 'Approuvé',
                'Rejected' => 'Rejeté',
                'Pending' => 'En attente',
            ],
            'es' => [
                'Menu' => 'Menú',
                'Navigation' => 'Navegación',
                'Home' => 'Inicio',
                'Admin' => 'Administrador',
                'Manager' => 'Gestor',
                'Managers' => 'Gestores',
                'Student' => 'Estudiante',
                'Students' => 'Estudiantes',
                'User' => 'Usuario',
                'Users' => 'Usuarios',
                'Hostel' => 'Hostal',
                'Hostels' => 'Hostales',
                'Room' => 'Habitación',
                'Rooms' => 'Habitaciones',
                'Bed Space' => 'Espacio de cama',
                'Booking' => 'Reserva',
                'Bookings' => 'Reservas',
                'Payment' => 'Pago',
                'Payments' => 'Pagos',
                'System Settings' => 'Configuración del sistema',
                'General' => 'General',
                'Localization' => 'Localización',
                'Website Content' => 'Contenido del sitio',
                'Payment Gateways' => 'Pasarelas de pago',
                'Integrations' => 'Integraciones',
                'Notifications' => 'Notificaciones',
                'Webhook' => 'Webhook',
                'API' => 'API',
                'Addons' => 'Complementos',
                'Asset Management' => 'Gestión de activos',
                'Assets' => 'Activos',
                'Backup' => 'Copia de seguridad',
                'Restore' => 'Restaurar',
                'File Manager' => 'Gestor de archivos',
                'Upload' => 'Subir',
                'Dark Mode' => 'Modo oscuro',
                'Light Mode' => 'Modo claro',
                'Language' => 'Idioma',
                'Default Language' => 'Idioma predeterminado',
                'Enabled Languages' => 'Idiomas habilitados',
                'Search' => 'Buscar',
                'Filter' => 'Filtrar',
                'Create' => 'Crear',
                'Edit' => 'Editar',
                'Delete' => 'Eliminar',
                'View' => 'Ver',
                'Status' => 'Estado',
                'Approved' => 'Aprobado',
                'Rejected' => 'Rechazado',
                'Pending' => 'Pendiente',
            ],
            'de' => [
                'Menu' => 'Menü',
                'Navigation' => 'Navigation',
                'Home' => 'Startseite',
                'Admin' => 'Administrator',
                'Manager' => 'Manager',
                'Managers' => 'Manager',
                'Student' => 'Student',
                'Students' => 'Studenten',
                'User' => 'Benutzer',
                'Users' => 'Benutzer',
                'Hostel' => 'Wohnheim',
                'Hostels' => 'Wohnheime',
                'Room' => 'Zimmer',
                'Rooms' => 'Zimmer',
                'Bed Space' => 'Bettplatz',
                'Booking' => 'Buchung',
                'Bookings' => 'Buchungen',
                'Payment' => 'Zahlung',
                'Payments' => 'Zahlungen',
                'System Settings' => 'Systemeinstellungen',
                'General' => 'Allgemein',
                'Localization' => 'Lokalisierung',
                'Website Content' => 'Website-Inhalte',
                'Payment Gateways' => 'Zahlungsgateways',
                'Integrations' => 'Integrationen',
                'Notifications' => 'Benachrichtigungen',
                'Webhook' => 'Webhook',
                'API' => 'API',
                'Addons' => 'Erweiterungen',
                'Asset Management' => 'Asset-Management',
                'Assets' => 'Assets',
                'Backup' => 'Sicherung',
                'Restore' => 'Wiederherstellen',
                'File Manager' => 'Dateimanager',
                'Upload' => 'Hochladen',
                'Dark Mode' => 'Dunkler Modus',
                'Light Mode' => 'Heller Modus',
                'Language' => 'Sprache',
                'Default Language' => 'Standardsprache',
                'Enabled Languages' => 'Aktivierte Sprachen',
                'Search' => 'Suchen',
                'Filter' => 'Filter',
                'Create' => 'Erstellen',
                'Edit' => 'Bearbeiten',
                'Delete' => 'Löschen',
                'View' => 'Ansehen',
                'Status' => 'Status',
                'Approved' => 'Genehmigt',
                'Rejected' => 'Abgelehnt',
                'Pending' => 'Ausstehend',
            ],
            'pt' => [
                'Menu' => 'Menu',
                'Navigation' => 'Navegação',
                'Home' => 'Início',
                'Admin' => 'Administrador',
                'Manager' => 'Gerente',
                'Managers' => 'Gerentes',
                'Student' => 'Estudante',
                'Students' => 'Estudantes',
                'User' => 'Usuário',
                'Users' => 'Usuários',
                'Hostel' => 'Alojamento',
                'Hostels' => 'Alojamentos',
                'Room' => 'Quarto',
                'Rooms' => 'Quartos',
                'Bed Space' => 'Espaço de cama',
                'Booking' => 'Reserva',
                'Bookings' => 'Reservas',
                'Payment' => 'Pagamento',
                'Payments' => 'Pagamentos',
                'System Settings' => 'Configurações do sistema',
                'General' => 'Geral',
                'Localization' => 'Localização',
                'Website Content' => 'Conteúdo do site',
                'Payment Gateways' => 'Gateways de pagamento',
                'Integrations' => 'Integrações',
                'Notifications' => 'Notificações',
                'Webhook' => 'Webhook',
                'API' => 'API',
                'Addons' => 'Complementos',
                'Asset Management' => 'Gestão de ativos',
                'Assets' => 'Ativos',
                'Backup' => 'Backup',
                'Restore' => 'Restaurar',
                'File Manager' => 'Gerenciador de arquivos',
                'Upload' => 'Enviar',
                'Dark Mode' => 'Modo escuro',
                'Light Mode' => 'Modo claro',
                'Language' => 'Idioma',
                'Default Language' => 'Idioma padrão',
                'Enabled Languages' => 'Idiomas habilitados',
                'Search' => 'Pesquisar',
                'Filter' => 'Filtrar',
                'Create' => 'Criar',
                'Edit' => 'Editar',
                'Delete' => 'Excluir',
                'View' => 'Ver',
                'Status' => 'Status',
                'Approved' => 'Aprovado',
                'Rejected' => 'Rejeitado',
                'Pending' => 'Pendente',
            ],
            'ar' => [
                'Menu' => 'القائمة',
                'Navigation' => 'التنقل',
                'Home' => 'الرئيسية',
                'Admin' => 'المسؤول',
                'Manager' => 'المدير',
                'Managers' => 'المديرون',
                'Student' => 'الطالب',
                'Students' => 'الطلاب',
                'User' => 'المستخدم',
                'Users' => 'المستخدمون',
                'Hostel' => 'السكن',
                'Hostels' => 'السكنات',
                'Room' => 'الغرفة',
                'Rooms' => 'الغرف',
                'Bed Space' => 'مساحة السرير',
                'Booking' => 'الحجز',
                'Bookings' => 'الحجوزات',
                'Payment' => 'الدفع',
                'Payments' => 'المدفوعات',
                'System Settings' => 'إعدادات النظام',
                'General' => 'عام',
                'Localization' => 'الترجمة',
                'Website Content' => 'محتوى الموقع',
                'Payment Gateways' => 'بوابات الدفع',
                'Integrations' => 'التكاملات',
                'Notifications' => 'الإشعارات',
                'Webhook' => 'ويب هوك',
                'API' => 'واجهة API',
                'Addons' => 'الإضافات',
                'Asset Management' => 'إدارة الأصول',
                'Assets' => 'الأصول',
                'Backup' => 'نسخة احتياطية',
                'Restore' => 'استعادة',
                'File Manager' => 'مدير الملفات',
                'Upload' => 'رفع',
                'Dark Mode' => 'الوضع الداكن',
                'Light Mode' => 'الوضع الفاتح',
                'Language' => 'اللغة',
                'Default Language' => 'اللغة الافتراضية',
                'Enabled Languages' => 'اللغات المفعلة',
                'Search' => 'بحث',
                'Filter' => 'تصفية',
                'Create' => 'إنشاء',
                'Edit' => 'تعديل',
                'Delete' => 'حذف',
                'View' => 'عرض',
                'Status' => 'الحالة',
                'Approved' => 'تمت الموافقة',
                'Rejected' => 'مرفوض',
                'Pending' => 'قيد الانتظار',
            ],
            'he' => [
                'Menu' => 'תפריט',
                'Navigation' => 'ניווט',
                'Home' => 'בית',
                'Admin' => 'מנהל מערכת',
                'Manager' => 'מנהל',
                'Managers' => 'מנהלים',
                'Student' => 'סטודנט',
                'Students' => 'סטודנטים',
                'User' => 'משתמש',
                'Users' => 'משתמשים',
                'Hostel' => 'מעון',
                'Hostels' => 'מעונות',
                'Room' => 'חדר',
                'Rooms' => 'חדרים',
                'Bed Space' => 'מקום מיטה',
                'Booking' => 'הזמנה',
                'Bookings' => 'הזמנות',
                'Payment' => 'תשלום',
                'Payments' => 'תשלומים',
                'System Settings' => 'הגדרות מערכת',
                'General' => 'כללי',
                'Localization' => 'לוקליזציה',
                'Website Content' => 'תוכן אתר',
                'Payment Gateways' => 'שערי תשלום',
                'Integrations' => 'אינטגרציות',
                'Notifications' => 'התראות',
                'Webhook' => 'Webhook',
                'API' => 'API',
                'Addons' => 'תוספים',
                'Asset Management' => 'ניהול נכסים',
                'Assets' => 'נכסים',
                'Backup' => 'גיבוי',
                'Restore' => 'שחזור',
                'File Manager' => 'מנהל קבצים',
                'Upload' => 'העלה',
                'Dark Mode' => 'מצב כהה',
                'Light Mode' => 'מצב בהיר',
                'Language' => 'שפה',
                'Default Language' => 'שפת ברירת מחדל',
                'Enabled Languages' => 'שפות פעילות',
                'Search' => 'חיפוש',
                'Filter' => 'סינון',
                'Create' => 'יצירה',
                'Edit' => 'עריכה',
                'Delete' => 'מחיקה',
                'View' => 'צפייה',
                'Status' => 'סטטוס',
                'Approved' => 'מאושר',
                'Rejected' => 'נדחה',
                'Pending' => 'ממתין',
            ],
            'ur' => [
                'Menu' => 'مینو',
                'Navigation' => 'نیویگیشن',
                'Home' => 'ہوم',
                'Admin' => 'ایڈمن',
                'Manager' => 'منیجر',
                'Managers' => 'منیجرز',
                'Student' => 'طالب علم',
                'Students' => 'طلبہ',
                'User' => 'صارف',
                'Users' => 'صارفین',
                'Hostel' => 'ہاسٹل',
                'Hostels' => 'ہاسٹلز',
                'Room' => 'کمرہ',
                'Rooms' => 'کمرے',
                'Bed Space' => 'بیڈ اسپیس',
                'Booking' => 'بکنگ',
                'Bookings' => 'بکنگز',
                'Payment' => 'ادائیگی',
                'Payments' => 'ادائیگیاں',
                'System Settings' => 'سسٹم سیٹنگز',
                'General' => 'عمومی',
                'Localization' => 'لوکلائزیشن',
                'Website Content' => 'ویب سائٹ مواد',
                'Payment Gateways' => 'ادائیگی گیٹ ویز',
                'Integrations' => 'انٹیگریشنز',
                'Notifications' => 'نوٹیفکیشنز',
                'Webhook' => 'ویب ہُک',
                'API' => 'API',
                'Addons' => 'ایڈونز',
                'Asset Management' => 'اثاثہ مینجمنٹ',
                'Assets' => 'اثاثے',
                'Backup' => 'بیک اپ',
                'Restore' => 'بحال کریں',
                'File Manager' => 'فائل مینیجر',
                'Upload' => 'اپ لوڈ',
                'Dark Mode' => 'ڈارک موڈ',
                'Light Mode' => 'لائٹ موڈ',
                'Language' => 'زبان',
                'Default Language' => 'ڈیفالٹ زبان',
                'Enabled Languages' => 'فعال زبانیں',
                'Search' => 'تلاش',
                'Filter' => 'فلٹر',
                'Create' => 'بنائیں',
                'Edit' => 'ترمیم',
                'Delete' => 'حذف',
                'View' => 'دیکھیں',
                'Status' => 'اسٹیٹس',
                'Approved' => 'منظور شدہ',
                'Rejected' => 'مسترد',
                'Pending' => 'زیر التوا',
            ],
            'hi' => [
                'Menu' => 'मेनू',
                'Navigation' => 'नेविगेशन',
                'Home' => 'होम',
                'Admin' => 'एडमिन',
                'Manager' => 'प्रबंधक',
                'Managers' => 'प्रबंधक',
                'Student' => 'छात्र',
                'Students' => 'छात्र',
                'User' => 'उपयोगकर्ता',
                'Users' => 'उपयोगकर्ता',
                'Hostel' => 'छात्रावास',
                'Hostels' => 'छात्रावास',
                'Room' => 'कमरा',
                'Rooms' => 'कमरे',
                'Bed Space' => 'बेड स्पेस',
                'Booking' => 'बुकिंग',
                'Bookings' => 'बुकिंग्स',
                'Payment' => 'भुगतान',
                'Payments' => 'भुगतान',
                'System Settings' => 'सिस्टम सेटिंग्स',
                'General' => 'सामान्य',
                'Localization' => 'लोकलाइज़ेशन',
                'Website Content' => 'वेबसाइट सामग्री',
                'Payment Gateways' => 'पेमेंट गेटवे',
                'Integrations' => 'इंटीग्रेशन',
                'Notifications' => 'सूचनाएं',
                'Webhook' => 'वेबहुक',
                'API' => 'API',
                'Addons' => 'ऐडऑन',
                'Asset Management' => 'एसेट मैनेजमेंट',
                'Assets' => 'एसेट्स',
                'Backup' => 'बैकअप',
                'Restore' => 'रीस्टोर',
                'File Manager' => 'फाइल मैनेजर',
                'Upload' => 'अपलोड',
                'Dark Mode' => 'डार्क मोड',
                'Light Mode' => 'लाइट मोड',
                'Language' => 'भाषा',
                'Default Language' => 'डिफ़ॉल्ट भाषा',
                'Enabled Languages' => 'सक्षम भाषाएं',
                'Search' => 'खोजें',
                'Filter' => 'फ़िल्टर',
                'Create' => 'बनाएं',
                'Edit' => 'संपादित करें',
                'Delete' => 'हटाएं',
                'View' => 'देखें',
                'Status' => 'स्थिति',
                'Approved' => 'स्वीकृत',
                'Rejected' => 'अस्वीकृत',
                'Pending' => 'लंबित',
            ],
            'zh_CN' => [
                'Menu' => '菜单',
                'Navigation' => '导航',
                'Home' => '首页',
                'Admin' => '管理员',
                'Manager' => '经理',
                'Managers' => '经理',
                'Student' => '学生',
                'Students' => '学生',
                'User' => '用户',
                'Users' => '用户',
                'Hostel' => '宿舍',
                'Hostels' => '宿舍',
                'Room' => '房间',
                'Rooms' => '房间',
                'Bed Space' => '床位',
                'Booking' => '预订',
                'Bookings' => '预订',
                'Payment' => '支付',
                'Payments' => '支付',
                'System Settings' => '系统设置',
                'General' => '常规',
                'Localization' => '本地化',
                'Website Content' => '网站内容',
                'Payment Gateways' => '支付网关',
                'Integrations' => '集成',
                'Notifications' => '通知',
                'Webhook' => 'Webhook',
                'API' => 'API',
                'Addons' => '插件',
                'Asset Management' => '资产管理',
                'Assets' => '资产',
                'Backup' => '备份',
                'Restore' => '恢复',
                'File Manager' => '文件管理器',
                'Upload' => '上传',
                'Dark Mode' => '深色模式',
                'Light Mode' => '浅色模式',
                'Language' => '语言',
                'Default Language' => '默认语言',
                'Enabled Languages' => '启用的语言',
                'Search' => '搜索',
                'Filter' => '筛选',
                'Create' => '创建',
                'Edit' => '编辑',
                'Delete' => '删除',
                'View' => '查看',
                'Status' => '状态',
                'Approved' => '已批准',
                'Rejected' => '已拒绝',
                'Pending' => '待处理',
            ],
            default => [],
        };
    }

    /**
     * @return array<int, array{locale: string, key: string, value: string}>
     */
    private function starterPackRowsForLocale(string $locale): array
    {
        $map = $this->starterPackMapForLocale($locale);
        $rows = [];
        foreach ($map as $key => $value) {
            $rows[] = [
                'locale' => $locale,
                'key' => $key,
                'value' => $value,
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array{locale: string, key: string, value: string}>
     */
    private function starterPackRowsForAllLocales(): array
    {
        $rows = [];
        foreach (array_keys($this->languageOptions()) as $locale) {
            if ($locale === 'en') {
                continue;
            }
            $rows = array_merge($rows, $this->starterPackRowsForLocale($locale));
        }

        return $rows;
    }

    public function exportTranslationsJson()
    {
        $rows = $this->sanitizeCustomTranslations(SystemTranslationStore::read());
        $payload = [
            'exported_at' => now()->toIso8601String(),
            'app_locale' => (string) SystemSetting::getSetting('app_locale', config('app.locale', 'en')),
            'translations' => $rows,
        ];
        $fileName = 'translations-export-' . now()->format('Ymd_His') . '.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    /**
     * @param array{translations_json: string, replace_existing?: bool} $data
     */
    public function importTranslationsJson(array $data): void
    {
        $raw = trim((string) ($data['translations_json'] ?? ''));
        if ($raw === '') {
            Notification::make()
                ->danger()
                ->title(__('Import Failed'))
                ->body(__('Translation JSON content is empty.'))
                ->send();
            return;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            Notification::make()
                ->danger()
                ->title(__('Import Failed'))
                ->body(__('Invalid JSON format.'))
                ->send();
            return;
        }

        $rows = $decoded['translations'] ?? $decoded;
        if (!is_array($rows)) {
            Notification::make()
                ->danger()
                ->title(__('Import Failed'))
                ->body(__('JSON must contain a "translations" array or an array of translation rows.'))
                ->send();
            return;
        }

        $incoming = $this->sanitizeCustomTranslations($rows);
        if (empty($incoming)) {
            Notification::make()
                ->warning()
                ->title(__('Nothing Imported'))
                ->body(__('No valid translation rows found.'))
                ->send();
            return;
        }

        $existing = $this->sanitizeCustomTranslations($this->data['custom_translations'] ?? []);
        $persisted = $this->sanitizeCustomTranslations(SystemTranslationStore::read());
        $existing = $this->mergeTranslationRows($persisted, $existing);
        $replace = (bool) ($data['replace_existing'] ?? false);
        $merged = $replace ? $incoming : $this->mergeTranslationRows($existing, $incoming);
        $locale = (string) ($this->data['translation_locale'] ?? $this->data['app_locale'] ?? 'en');

        $this->data['custom_translations'] = $this->subsetCustomTranslationsForUi($merged, $locale);
        $this->data['translation_editor'] = $this->buildTranslationEditorRows($locale, $merged);
        SystemTranslationStore::write($merged);

        Notification::make()
            ->success()
            ->title(__('Translations Imported'))
            ->body(__('Translation JSON imported successfully.'))
            ->send();
    }

    private function translationFileHealthSummary(): HtmlString
    {
        $path = SystemTranslationStore::path();
        $exists = is_file($path);
        $readable = $exists && is_readable($path);
        $writable = $exists ? is_writable($path) : is_writable(dirname($path));

        $rows = SystemTranslationStore::read();
        $jsonValid = !$exists ? false : !empty($rows);
        $jsonState = !$exists
            ? __('Missing')
            : ($jsonValid ? __('Valid') : __('Invalid or Empty'));

        $statusColor = ($exists && $readable && $writable) ? '#15803d' : '#b45309';
        $statusLabel = ($exists && $readable && $writable)
            ? __('Healthy')
            : __('Needs attention');

        $html = sprintf(
            '<div style="line-height:1.5"><strong style="color:%s">%s</strong><br><small>%s</small><br><small>%s: %s</small><br><small>%s: %s</small><br><small>%s: %s</small><br><small>%s: %d</small></div>',
            e($statusColor),
            e($statusLabel),
            e($path),
            e(__('File')),
            e($exists ? __('Exists') : __('Missing')),
            e(__('Readable / Writable')),
            e(($readable ? __('Yes') : __('No')) . ' / ' . ($writable ? __('Yes') : __('No'))),
            e(__('JSON')),
            e($jsonState),
            e(__('Loaded translation rows')),
            count($rows)
        );

        return new HtmlString($html);
    }

    private function getDefaultCustomCss(): string
    {
        return <<<'CSS'
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
    }
}
