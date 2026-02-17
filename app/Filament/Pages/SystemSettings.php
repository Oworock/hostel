<?php

namespace App\Filament\Pages;

use App\Models\PaymentGateway;
use App\Services\PaymentGatewayDiagnosticsService;
use App\Services\NotificationTemplateService;
use App\Support\CurrencyCatalog;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
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
        $webhookEvents = json_decode(SystemSetting::getSetting('webhook_events_json', ''), true);
        $notificationTemplates = app(NotificationTemplateService::class)->forRepeater();
        
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
            'custom_css' => $currentCustomCss,
            'registration_fields' => is_array($registrationFields) ? $registrationFields : ['phone'],
            'registration_required_fields' => is_array($registrationRequiredFields) ? $registrationRequiredFields : [],
            'registration_custom_fields' => is_array($registrationCustomFields) ? $registrationCustomFields : [],
            'booking_period_type' => SystemSetting::getSetting('booking_period_type', 'months'),
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
            'smtp_host' => SystemSetting::getSetting('smtp_host', config('mail.mailers.smtp.host')),
            'smtp_port' => SystemSetting::getSetting('smtp_port', config('mail.mailers.smtp.port')),
            'smtp_username' => SystemSetting::getSetting('smtp_username', config('mail.mailers.smtp.username')),
            'smtp_password' => SystemSetting::getSetting('smtp_password', config('mail.mailers.smtp.password')),
            'smtp_encryption' => SystemSetting::getSetting('smtp_encryption', config('mail.mailers.smtp.encryption')),
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
            'webhook_secret' => SystemSetting::getSetting('webhook_secret', ''),
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
                        Forms\Components\Tabs\Tab::make('General')
                            ->icon('heroicon-m-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make('App Settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('app_name')
                                            ->label('Application Name')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('app_email')
                                            ->label('Admin Email')
                                            ->email()
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('app_phone')
                                            ->label('Admin Phone')
                                            ->tel(),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Site Configuration')
                            ->icon('heroicon-m-window')
                            ->schema([
                                Forms\Components\Section::make('Booking Configuration')
                                    ->schema([
                                        Forms\Components\Select::make('booking_period_type')
                                            ->label('Booking Period Type')
                                            ->options([
                                                'months' => 'Months',
                                                'semesters' => 'Semesters',
                                                'sessions' => 'Sessions',
                                            ])
                                            ->required(),
                                    ]),
                                
                                Forms\Components\Section::make('System Currency')
                                    ->schema([
                                        Forms\Components\Select::make('system_currency')
                                            ->label('Currency')
                                            ->options(CurrencyCatalog::options())
                                            ->searchable()
                                            ->required(),
                                    ]),

                                Forms\Components\Section::make('Website Theme')
                                    ->description('Choose one of five professional preset website layouts/design directions.')
                                    ->schema([
                                        Forms\Components\Toggle::make('homepage_enabled')
                                            ->label('Enable Welcome/Home Page')
                                            ->helperText('If disabled, the root URL redirects to Login and the welcome page is inaccessible.')
                                            ->default(true),
                                        Forms\Components\Select::make('website_theme')
                                            ->label('Theme Preset')
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

                                Forms\Components\Section::make('Custom Website CSS')
                                    ->description('Add custom CSS that will be applied globally to public and dashboard pages.')
                                    ->schema([
                                        Forms\Components\Textarea::make('custom_css')
                                            ->label('Custom CSS')
                                            ->rows(12)
                                            ->placeholder("/* Example */\n:root { --brand: #0f766e; }\n.btn-primary { border-radius: 10px; }")
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Registration Form')
                            ->icon('heroicon-m-user-plus')
                            ->schema([
                                Forms\Components\Section::make('Student Registration Fields')
                                    ->description('Choose optional fields to show on student signup and which are required.')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('registration_fields')
                                            ->label('Show These Optional Fields')
                                            ->options([
                                                'phone' => 'Phone Number',
                                                'id_number' => 'ID Number',
                                                'address' => 'Address',
                                                'guardian_name' => 'Guardian Name',
                                                'guardian_phone' => 'Guardian Phone',
                                            ])
                                            ->columns(2),
                                        Forms\Components\CheckboxList::make('registration_required_fields')
                                            ->label('Make Required')
                                            ->options([
                                                'phone' => 'Phone Number',
                                                'id_number' => 'ID Number',
                                                'address' => 'Address',
                                                'guardian_name' => 'Guardian Name',
                                                'guardian_phone' => 'Guardian Phone',
                                            ])
                                            ->columns(2)
                                            ->helperText('Only fields selected in "Show" are applied in registration.'),
                                        Forms\Components\Repeater::make('registration_custom_fields')
                                            ->label('Custom Fields')
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Field Name')
                                                    ->helperText('Use lowercase and underscore only, e.g. matric_no'),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Field Label'),
                                                Forms\Components\Select::make('type')
                                                    ->options([
                                                        'text' => 'Text',
                                                        'email' => 'Email',
                                                        'tel' => 'Phone',
                                                        'number' => 'Number',
                                                        'date' => 'Date',
                                                    ])
                                                    ->default('text'),
                                                Forms\Components\TextInput::make('placeholder')
                                                    ->label('Placeholder'),
                                                Forms\Components\Toggle::make('required')
                                                    ->label('Required')
                                                    ->default(false),
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->collapsible()
                                            ->reorderableWithButtons()
                                            ->helperText('Add custom registration fields to capture more information over time.'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Website Content')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Forms\Components\Section::make('Header, Footer, Welcome Page Content')
                                    ->schema([
                                        Forms\Components\Placeholder::make('website_content_note')
                                            ->content('Manage logo, header/footer content, and welcome-page body in Website Content manager.'),
                                        Forms\Components\Placeholder::make('website_content_link')
                                            ->content(new HtmlString('<a href="' . route('filament.admin.resources.welcome-contents.index') . '" class="text-primary-600 underline">Open Website Content Manager</a>')),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('SMS Configuration')
                            ->icon('heroicon-m-chat-bubble-left')
                            ->schema([
                                Forms\Components\Section::make('SMS Provider Settings')
                                    ->description('Configure your SMS provider for sending notifications')
                                    ->schema([
                                        Forms\Components\Select::make('sms_provider')
                                            ->label('SMS Provider')
                                            ->options([
                                                'custom' => 'Custom SMS Gateway',
                                                'none' => 'Disabled',
                                            ])
                                            ->required()
                                            ->live(),
                                        
                                        Forms\Components\TextInput::make('sms_url')
                                            ->label('SMS Gateway URL')
                                            ->placeholder('https://your-sms-provider.com/api/send')
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->required(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),

                                        Forms\Components\Select::make('sms_http_method')
                                            ->label('HTTP Method')
                                            ->options([
                                                'POST' => 'POST',
                                                'GET' => 'GET',
                                            ])
                                            ->default('POST')
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->required(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        
                                        Forms\Components\TextInput::make('sms_api_key')
                                            ->label('API Key')
                                            ->password()
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->required(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        
                                        Forms\Components\Textarea::make('sms_message_template')
                                            ->label('Message Template (Optional)')
                                            ->placeholder('Use {{name}}, {{message}}, {{hostel}} as placeholders')
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->columnSpanFull(),

                                        Forms\Components\KeyValue::make('sms_payload_template')
                                            ->label('SMS Payload Key-Value Template')
                                            ->helperText('Recommended placeholders: {to} recipient phone, {message} body text, {from} sender ID/Name, {api_key} API key. Backward-compatible: {phone}, {sender_id}.')
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->columnSpanFull(),

                                        Forms\Components\KeyValue::make('sms_custom_headers')
                                            ->label('Custom Request Headers')
                                            ->helperText('Optional HTTP headers sent with SMS requests.')
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->columnSpanFull(),

                                        Forms\Components\Placeholder::make('sms_key_guide')
                                            ->content(new HtmlString(
                                                '<div><strong>SMS placeholder guide:</strong><br>{to} = recipient phone number<br>{message} = SMS body<br>{from} = sender ID/name<br>{api_key} = API key/token<br>Legacy placeholders still supported: {phone}, {sender_id}</div>'
                                            ))
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('test_phone')
                                            ->label('Test Phone Number')
                                            ->tel()
                                            ->placeholder('+2349000000000')
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('test_sms_inline')
                                                ->label('Test SMS')
                                                ->color('info')
                                                ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                                ->action(fn () => $this->testSMS()),
                                        ]),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('SMTP Configuration')
                            ->icon('heroicon-m-envelope')
                            ->schema([
                                Forms\Components\Section::make('SMTP Mail Settings')
                                    ->description('Configure SMTP for sending emails')
                                    ->schema([
                                        Forms\Components\TextInput::make('smtp_host')
                                            ->label('SMTP Host')
                                            ->placeholder('smtp.gmail.com'),
                                        
                                        Forms\Components\TextInput::make('smtp_port')
                                            ->label('SMTP Port')
                                            ->numeric()
                                            ->placeholder('587'),
                                        
                                        Forms\Components\TextInput::make('smtp_username')
                                            ->label('Username/Email')
                                            ->placeholder('your-email@example.com'),
                                        
                                        Forms\Components\TextInput::make('smtp_password')
                                            ->label('Password')
                                            ->password()
                                            ->placeholder('••••••••'),
                                        
                                        Forms\Components\Select::make('smtp_encryption')
                                            ->label('Encryption')
                                            ->options([
                                                'tls' => 'TLS',
                                                'ssl' => 'SSL',
                                            ])
                                            ->default('tls'),
                                        Forms\Components\TextInput::make('smtp_test_email')
                                            ->label('Test Recipient Email')
                                            ->email()
                                            ->placeholder('you@example.com'),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('test_email_inline')
                                                ->label('Test Email')
                                                ->color('info')
                                                ->action(fn () => $this->testEmail()),
                                        ]),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Payment Gateways')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                Forms\Components\Section::make('Paystack Configuration')
                                    ->description('Configure Paystack for payment processing')
                                    ->schema([
                                        Forms\Components\Toggle::make('paystack_enabled')
                                            ->label('Enable Paystack'),
                                        Forms\Components\TextInput::make('paystack_public_key')
                                            ->label('Public Key')
                                            ->password()
                                            ->placeholder('pk_live_xxxxx'),
                                        
                                        Forms\Components\TextInput::make('paystack_secret_key')
                                            ->label('Secret Key')
                                            ->password()
                                            ->placeholder('sk_live_xxxxx'),
                                    ]),
                                
                                Forms\Components\Section::make('Flutterwave Configuration')
                                    ->description('Configure Flutterwave for payment processing')
                                    ->schema([
                                        Forms\Components\Toggle::make('flutterwave_enabled')
                                            ->label('Enable Flutterwave'),
                                        Forms\Components\TextInput::make('flutterwave_public_key')
                                            ->label('Public Key')
                                            ->password()
                                            ->placeholder('pk_test_xxxxx'),
                                        
                                        Forms\Components\TextInput::make('flutterwave_secret_key')
                                            ->label('Secret Key')
                                            ->password()
                                            ->placeholder('sk_test_xxxxx'),
                                    ]),

                                Forms\Components\Section::make('Stripe Configuration')
                                    ->description('Configure Stripe for payment processing')
                                    ->schema([
                                        Forms\Components\Toggle::make('stripe_enabled')
                                            ->label('Enable Stripe'),
                                        Forms\Components\TextInput::make('stripe_public_key')
                                            ->label('Publishable Key')
                                            ->password()
                                            ->placeholder('pk_live_xxxxx'),
                                        Forms\Components\TextInput::make('stripe_secret_key')
                                            ->label('Secret Key')
                                            ->password()
                                            ->placeholder('sk_live_xxxxx'),
                                    ]),

                                Forms\Components\Section::make('PayPal Configuration')
                                    ->description('Configure PayPal (Client ID + Client Secret)')
                                    ->schema([
                                        Forms\Components\Toggle::make('paypal_enabled')
                                            ->label('Enable PayPal'),
                                        Forms\Components\TextInput::make('paypal_public_key')
                                            ->label('Client ID')
                                            ->password()
                                            ->placeholder('PAYPAL_CLIENT_ID'),
                                        Forms\Components\TextInput::make('paypal_secret_key')
                                            ->label('Client Secret')
                                            ->password()
                                            ->placeholder('PAYPAL_CLIENT_SECRET'),
                                        Forms\Components\Select::make('paypal_environment')
                                            ->label('Environment')
                                            ->options([
                                                'live' => 'Live',
                                                'sandbox' => 'Sandbox',
                                            ])
                                            ->default('live'),
                                    ]),

                                Forms\Components\Section::make('Razorpay Configuration')
                                    ->description('Configure Razorpay (Key ID + Key Secret)')
                                    ->schema([
                                        Forms\Components\Toggle::make('razorpay_enabled')
                                            ->label('Enable Razorpay'),
                                        Forms\Components\TextInput::make('razorpay_public_key')
                                            ->label('Key ID')
                                            ->password()
                                            ->placeholder('rzp_live_xxxxx'),
                                        Forms\Components\TextInput::make('razorpay_secret_key')
                                            ->label('Key Secret')
                                            ->password()
                                            ->placeholder('xxxxxxxx'),
                                    ]),

                                Forms\Components\Section::make('Square Configuration')
                                    ->description('Configure Square (Application ID + Access Token)')
                                    ->schema([
                                        Forms\Components\Toggle::make('square_enabled')
                                            ->label('Enable Square'),
                                        Forms\Components\TextInput::make('square_public_key')
                                            ->label('Application ID')
                                            ->password()
                                            ->placeholder('sq0idp-xxxxxxxx'),
                                        Forms\Components\TextInput::make('square_secret_key')
                                            ->label('Access Token')
                                            ->password()
                                            ->placeholder('EAAAE...'),
                                        Forms\Components\TextInput::make('square_location_id')
                                            ->label('Location ID')
                                            ->placeholder('L123ABCDEF'),
                                        Forms\Components\Select::make('square_environment')
                                            ->label('Environment')
                                            ->options([
                                                'live' => 'Live',
                                                'sandbox' => 'Sandbox',
                                            ])
                                            ->default('live'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Integrations')
                            ->icon('heroicon-m-link')
                            ->schema([
                                Forms\Components\Section::make('Outgoing Webhooks')
                                    ->description('Send system events to Zapier, Make, n8n, or custom URLs.')
                                    ->schema([
                                        Forms\Components\Toggle::make('webhook_enabled')
                                            ->label('Enable Webhooks'),
                                        Forms\Components\TextInput::make('webhook_url')
                                            ->label('Webhook URL')
                                            ->url()
                                            ->placeholder('https://hooks.zapier.com/...'),
                                        Forms\Components\TextInput::make('webhook_secret')
                                            ->label('Signing Secret')
                                            ->password()
                                            ->helperText('Requests include X-Hostel-Signature (HMAC SHA256).'),
                                        Forms\Components\CheckboxList::make('webhook_events')
                                            ->label('Events to Send')
                                            ->options([
                                                'booking.created' => 'Booking Created',
                                                'booking.cancelled' => 'Booking Cancelled',
                                                'booking.manager_approved' => 'Manager Approved Booking',
                                                'booking.manager_rejected' => 'Manager Rejected Booking',
                                                'booking.manager_cancelled' => 'Manager Cancelled Booking',
                                                'payment.completed' => 'Payment Completed',
                                                'complaint.created' => 'Complaint Created',
                                                'complaint.responded' => 'Complaint Responded',
                                                'hostel_change.submitted' => 'Hostel Change Submitted',
                                                'hostel_change.manager_approved' => 'Hostel Change Manager Approved',
                                                'hostel_change.manager_rejected' => 'Hostel Change Manager Rejected',
                                                'hostel_change.admin_approved' => 'Hostel Change Admin Approved',
                                                'hostel_change.admin_rejected' => 'Hostel Change Admin Rejected',
                                                'system.webhook_test' => 'Webhook Test Event',
                                            ])
                                            ->columns(2),
                                    ]),
                                Forms\Components\Section::make('Third-Party API Access')
                                    ->description('Use this key as Bearer token or X-API-Key on /api/v1 routes.')
                                    ->schema([
                                        Forms\Components\Placeholder::make('api_docs_link')
                                            ->label('API Documentation')
                                            ->content(new HtmlString('<a href="' . route('admin.api.docs') . '" target="_blank" class="text-primary-600 underline">Open API Documentation</a>')),
                                        Forms\Components\Toggle::make('api_enabled')
                                            ->label('Enable Public API'),
                                        Forms\Components\TextInput::make('api_access_key')
                                            ->label('API Access Key')
                                            ->placeholder('generate-a-random-secret-key')
                                            ->helperText('Visible for admin. Keep private and rotate periodically.'),
                                        Forms\Components\Placeholder::make('api_access_key_copy')
                                            ->label('Quick Copy')
                                            ->content(new HtmlString('<button type="button" onclick="(function(){const i=document.querySelector(\'input[name=&quot;data[api_access_key]&quot;]\'); if(!i||!i.value){alert(\'No API key available.\');return;} navigator.clipboard.writeText(i.value).then(()=>alert(\'API key copied.\')).catch(()=>alert(\'Copy failed. Please copy manually.\'));})();" class="fi-btn fi-btn-size-sm fi-color-primary fi-btn-color-primary fi-ac-action fi-ac-btn-action">Copy API Key</button>')),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Notifications')
                            ->icon('heroicon-m-bell')
                            ->schema([
                                Forms\Components\Section::make('System Notification Message Templates')
                                    ->description('Customize in-app, email, and SMS message copy. Placeholders: {student_name}, {actor_name}, {current_hostel}, {requested_hostel}, {current_room}, {requested_room}, {status}, {reason}.')
                                    ->schema([
                                        Forms\Components\Repeater::make('notification_templates')
                                            ->label('Event Templates')
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

        if (trim((string) ($data['custom_css'] ?? '')) === '') {
            $data['custom_css'] = $this->getDefaultCustomCss();
        }

        $data['sms_payload_template_json'] = json_encode($data['sms_payload_template'] ?? []);
        $data['sms_custom_headers_json'] = json_encode($data['sms_custom_headers'] ?? []);
        $data['webhook_events_json'] = json_encode(array_values($data['webhook_events'] ?? []));
        $data['registration_fields_json'] = json_encode(array_values($data['registration_fields'] ?? []));
        $data['registration_required_fields_json'] = json_encode(array_values($data['registration_required_fields'] ?? []));
        $data['registration_custom_fields_json'] = json_encode(array_values($data['registration_custom_fields'] ?? []));
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

                if (!in_array($type, ['text', 'email', 'tel', 'number', 'date'], true)) {
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
            if (!in_array($key, ['test_phone', 'sms_payload_template', 'sms_custom_headers', 'webhook_events', 'registration_fields', 'registration_required_fields', 'registration_custom_fields', 'notification_templates'], true)) {
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
            ->title('Settings Saved')
            ->body('Your system settings have been updated successfully.')
            ->send();
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
            'app_email' => 'MAIL_FROM_ADDRESS',
            'system_currency' => 'APP_CURRENCY',
            'booking_period_type' => 'BOOKING_PERIOD_TYPE',
            'smtp_host' => 'MAIL_HOST',
            'smtp_port' => 'MAIL_PORT',
            'smtp_username' => 'MAIL_USERNAME',
            'smtp_password' => 'MAIL_PASSWORD',
            'smtp_encryption' => 'MAIL_ENCRYPTION',
        ];

        foreach ($envMappings as $formKey => $envKey) {
            if (isset($data[$formKey])) {
                $value = $data[$formKey];
                $envContent = $this->setEnvironmentValue($envContent, $envKey, $value);
            }
        }

        file_put_contents($envPath, $envContent);

        // Update runtime config
        Config::set('app.name', $data['app_name'] ?? config('app.name'));
        Config::set('app.currency', $data['system_currency'] ?? config('app.currency'));
        Config::set('app.booking_period_type', $data['booking_period_type'] ?? config('app.booking_period_type'));
        Config::set('mail.from.address', $data['app_email'] ?? config('mail.from.address'));
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
                ->title('Phone Required')
                ->body('Please enter a test phone number.')
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
                    ->title('SMS Sent Successfully')
                    ->body('Test SMS has been sent to ' . $data['test_phone'])
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title('SMS Failed')
                    ->body('Failed to send SMS. Error: ' . $response->body())
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Error testing SMS: ' . $e->getMessage())
                ->send();
        }
    }

    public function testEmail(): void
    {
        $data = $this->form->getState();
        $to = $data['smtp_test_email'] ?? null;

        if (empty($to)) {
            Notification::make()
                ->warning()
                ->title('Recipient Required')
                ->body('Enter a test recipient email under SMTP Configuration.')
                ->send();
            return;
        }

        try {
            Mail::raw('This is a test email from Hostel Management System SMTP settings.', function ($message) use ($to) {
                $message->to($to)
                    ->subject('SMTP Test Email');
            });

            Notification::make()
                ->success()
                ->title('Email Sent')
                ->body('Test email sent to ' . $to)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Email Test Failed')
                ->body($e->getMessage())
            ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateApiKey')
                ->label('Generate API Key')
                ->icon('heroicon-o-key')
                ->color('info')
                ->requiresConfirmation()
                ->action(fn () => $this->generateApiKey()),
            Action::make('testWebhook')
                ->label('Test Webhook')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->action(fn () => $this->testWebhook()),
            Action::make('testGatewayConnections')
                ->label('Test Gateway Connection')
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
            ->title('API Key Generated')
            ->body('A new API key has been generated and saved.')
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
        ]);

        Notification::make()
            ->title($ok ? 'Webhook Test Sent' : 'Webhook Test Failed')
            ->body($ok ? 'Webhook endpoint acknowledged the test event.' : 'Webhook request failed. Check URL/secret and logs.')
            ->color($ok ? 'success' : 'danger')
            ->send();
    }

    public function testGatewayConnections(): void
    {
        try {
            $results = app(PaymentGatewayDiagnosticsService::class)->testConfiguredGateways();

            $lines = [];
            $allReady = true;

            foreach ($results as $name => $result) {
                $status = ($result['initialization_ready'] && $result['verification_ready']) ? 'READY' : 'NOT READY';
                if ($status !== 'READY' && $result['active']) {
                    $allReady = false;
                }

                $lines[] = sprintf(
                    '%s: %s (init: %s, verify: %s) - %s',
                    strtoupper($name),
                    $status,
                    $result['initialization_ready'] ? 'ok' : 'fail',
                    $result['verification_ready'] ? 'ok' : 'fail',
                    $result['message']
                );
            }

            Notification::make()
                ->title($allReady ? 'Gateway Diagnostics Passed' : 'Gateway Diagnostics Found Issues')
                ->body(implode("\n", $lines))
                ->color($allReady ? 'success' : 'warning')
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Gateway Diagnostics Failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
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
