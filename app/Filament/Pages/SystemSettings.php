<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use App\Models\SystemSetting;

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
        
        $this->form->fill([
            'app_name' => SystemSetting::getSetting('app_name', config('app.name')),
            'app_email' => SystemSetting::getSetting('app_email', config('mail.from.address')),
            'app_phone' => SystemSetting::getSetting('app_phone', config('app.phone')),
            'app_logo' => SystemSetting::getSetting('app_logo', ''),
            'system_currency' => SystemSetting::getSetting('system_currency', 'NGN'),
            'booking_period_type' => SystemSetting::getSetting('booking_period_type', 'months'),
            'sms_provider' => SystemSetting::getSetting('sms_provider', 'none'),
            'sms_url' => SystemSetting::getSetting('sms_url', ''),
            'sms_api_key' => SystemSetting::getSetting('sms_api_key', ''),
            'sms_sender_id' => SystemSetting::getSetting('sms_sender_id', ''),
            'sms_message_template' => SystemSetting::getSetting('sms_message_template', ''),
            'test_phone' => '',
            'smtp_host' => SystemSetting::getSetting('smtp_host', config('mail.mailers.smtp.host')),
            'smtp_port' => SystemSetting::getSetting('smtp_port', config('mail.mailers.smtp.port')),
            'smtp_username' => SystemSetting::getSetting('smtp_username', config('mail.mailers.smtp.username')),
            'smtp_password' => SystemSetting::getSetting('smtp_password', config('mail.mailers.smtp.password')),
            'smtp_encryption' => SystemSetting::getSetting('smtp_encryption', config('mail.mailers.smtp.encryption')),
            'paystack_public_key' => SystemSetting::getSetting('paystack_public_key', ''),
            'paystack_secret_key' => SystemSetting::getSetting('paystack_secret_key', ''),
            'flutterwave_public_key' => SystemSetting::getSetting('flutterwave_public_key', ''),
            'flutterwave_secret_key' => SystemSetting::getSetting('flutterwave_secret_key', ''),
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
                                Forms\Components\Section::make('Logo & Branding')
                                    ->schema([
                                        Forms\Components\FileUpload::make('app_logo')
                                            ->label('Website Logo')
                                            ->image()
                                            ->directory('logos')
                                            ->maxSize(5120)
                                            ->previewable(true)
                                            ->columnSpanFull(),
                                    ]),
                                
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
                                            ->options([
                                                'NGN' => 'Nigerian Naira (₦)',
                                                'USD' => 'US Dollar ($)',
                                                'EUR' => 'Euro (€)',
                                                'GBP' => 'British Pound (£)',
                                                'GHS' => 'Ghanaian Cedis (₵)',
                                                'KES' => 'Kenyan Shilling (KES)',
                                                'ZAR' => 'South African Rand (R)',
                                            ])
                                            ->required(),
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
                                        
                                        Forms\Components\TextInput::make('sms_api_key')
                                            ->label('API Key')
                                            ->password()
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->required(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        
                                        Forms\Components\TextInput::make('sms_sender_id')
                                            ->label('Sender ID/Name')
                                            ->maxLength(20)
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->required(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        
                                        Forms\Components\Textarea::make('sms_message_template')
                                            ->label('Message Template (Optional)')
                                            ->placeholder('Use {{name}}, {{message}}, {{hostel}} as placeholders')
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('test_phone')
                                            ->label('Test Phone Number')
                                            ->tel()
                                            ->placeholder('+2349000000000')
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
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
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Payment Gateways')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                Forms\Components\Section::make('Paystack Configuration')
                                    ->description('Configure Paystack for payment processing')
                                    ->schema([
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
                                        Forms\Components\TextInput::make('flutterwave_public_key')
                                            ->label('Public Key')
                                            ->password()
                                            ->placeholder('pk_test_xxxxx'),
                                        
                                        Forms\Components\TextInput::make('flutterwave_secret_key')
                                            ->label('Secret Key')
                                            ->password()
                                            ->placeholder('sk_test_xxxxx'),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if ($value !== null && $key !== 'test_phone') {
                SystemSetting::setSetting($key, $value);
            }
        }

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
            $response = Http::post($data['sms_url'], [
                'api_key' => $data['sms_api_key'],
                'sender_id' => $data['sms_sender_id'],
                'to' => $data['test_phone'],
                'message' => 'Test SMS from Hostel Management System',
            ]);

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

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
            
            Action::make('testSMS')
                ->label('Test SMS')
                ->color('info')
                ->action('testSMS')
                ->visible(fn () => $this->form->getState()['sms_provider'] === 'custom'),
        ];
    }
}
