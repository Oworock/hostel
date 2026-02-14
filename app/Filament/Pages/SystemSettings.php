<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
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
        $this->form->fill([
            'app_name' => SystemSetting::getSetting('app_name', config('app.name')),
            'app_email' => SystemSetting::getSetting('app_email', config('mail.from.address')),
            'app_phone' => SystemSetting::getSetting('app_phone', config('app.phone')),
            'sms_provider' => SystemSetting::getSetting('sms_provider', 'none'),
            'sms_url' => SystemSetting::getSetting('sms_url', ''),
            'sms_api_key' => SystemSetting::getSetting('sms_api_key', ''),
            'sms_sender_id' => SystemSetting::getSetting('sms_sender_id', ''),
            'sms_message_template' => SystemSetting::getSetting('sms_message_template', ''),
            'test_phone' => '',
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
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        
                                        Forms\Components\TextInput::make('sms_api_key')
                                            ->label('API Key')
                                            ->password()
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        
                                        Forms\Components\TextInput::make('sms_sender_id')
                                            ->label('Sender ID/Name')
                                            ->maxLength(20)
                                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom'),
                                        
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

        Notification::make()
            ->success()
            ->title('Settings Saved')
            ->body('Your system settings have been updated successfully.')
            ->send();
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
