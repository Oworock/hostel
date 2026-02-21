<?php

namespace App\Filament\Pages;

use App\Models\Addon;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ReferralSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?string $navigationLabel = 'Referral Settings';

    protected static ?int $navigationSort = 13;

    protected static ?string $title = 'Referral Settings';

    protected static string $view = 'filament.pages.referral-settings';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return Addon::isActive('referral-system') && (auth()->user()?->isAdmin() ?? false);
    }

    public function mount(): void
    {
        abort_unless(static::shouldRegisterNavigation(), 403);

        $inviteToken = trim((string) SystemSetting::getSetting('referral_partner_invite_token', ''));
        if ($inviteToken === '') {
            $inviteToken = Str::random(24);
            SystemSetting::setSetting('referral_partner_invite_token', $inviteToken);
        }

        $this->form->fill([
            'referral_enabled' => filter_var(SystemSetting::getSetting('referral_enabled', true), FILTER_VALIDATE_BOOL),
            'referral_students_can_be_agents' => filter_var(SystemSetting::getSetting('referral_students_can_be_agents', true), FILTER_VALIDATE_BOOL),
            'referral_default_commission_type' => (string) SystemSetting::getSetting('referral_default_commission_type', 'percentage'),
            'referral_default_commission_value' => (float) SystemSetting::getSetting('referral_default_commission_value', 5),
            'referral_min_payout' => (float) SystemSetting::getSetting('referral_min_payout', 0),
            'referral_notify_email' => filter_var(SystemSetting::getSetting('referral_notify_email', true), FILTER_VALIDATE_BOOL),
            'referral_notify_sms' => filter_var(SystemSetting::getSetting('referral_notify_sms', false), FILTER_VALIDATE_BOOL),
            'referral_notify_student_registered_email_subject' => (string) SystemSetting::getSetting('referral_notify_student_registered_email_subject', 'New referral registration'),
            'referral_notify_student_registered_email_template' => (string) SystemSetting::getSetting('referral_notify_student_registered_email_template', 'A new student registered with your referral link: {student_name} ({student_email}).'),
            'referral_notify_student_registered_sms_template' => (string) SystemSetting::getSetting('referral_notify_student_registered_sms_template', 'New referral signup: {student_name} ({student_email}).'),
            'referral_notify_commission_email_subject' => (string) SystemSetting::getSetting('referral_notify_commission_email_subject', 'Referral commission earned'),
            'referral_notify_commission_email_template' => (string) SystemSetting::getSetting('referral_notify_commission_email_template', 'A referred student completed payment. Commission earned: {commission_amount}. Booking #{booking_id}.'),
            'referral_notify_commission_sms_template' => (string) SystemSetting::getSetting('referral_notify_commission_sms_template', 'Commission earned: {commission_amount} for booking #{booking_id}.'),
            'referral_notify_payout_email_subject' => (string) SystemSetting::getSetting('referral_notify_payout_email_subject', 'Referral payout update'),
            'referral_notify_payout_email_template' => (string) SystemSetting::getSetting('referral_notify_payout_email_template', 'Your payout request for {payout_amount} is now {payout_status}.'),
            'referral_notify_payout_sms_template' => (string) SystemSetting::getSetting('referral_notify_payout_sms_template', 'Payout {payout_amount} status: {payout_status}.'),
            'referral_popup_enabled' => filter_var(SystemSetting::getSetting('referral_popup_enabled', false), FILTER_VALIDATE_BOOL),
            'referral_popup_title' => (string) SystemSetting::getSetting('referral_popup_title', ''),
            'referral_popup_body' => (string) SystemSetting::getSetting('referral_popup_body', ''),
            'referral_popup_start_at' => $this->settingAsDateTime('referral_popup_start_at'),
            'referral_popup_end_at' => $this->settingAsDateTime('referral_popup_end_at'),
            'referral_partner_invite_token' => $inviteToken,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('referral_settings_tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('General')
                            ->schema([
                                Forms\Components\Section::make('Referral Program')
                                    ->schema([
                                        Forms\Components\Toggle::make('referral_enabled')
                                            ->label('Enable referral program')
                                            ->default(true),
                                        Forms\Components\Toggle::make('referral_students_can_be_agents')
                                            ->label('Allow students as referral agents')
                                            ->default(true),
                                        Forms\Components\Select::make('referral_default_commission_type')
                                            ->label('Default commission type')
                                            ->options([
                                                'percentage' => 'Percentage (%)',
                                                'fixed' => 'Fixed amount',
                                            ])
                                            ->default('percentage')
                                            ->required(),
                                        Forms\Components\TextInput::make('referral_default_commission_value')
                                            ->label('Default commission value')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0),
                                        Forms\Components\TextInput::make('referral_min_payout')
                                            ->label('Minimum payout threshold')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0),
                                    ]),
                                Forms\Components\Section::make('Partner Signup')
                                    ->schema([
                                        Forms\Components\TextInput::make('referral_partner_invite_token')
                                            ->label('Referral partner invite token')
                                            ->required()
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('regenerate')
                                                    ->label('Regenerate')
                                                    ->action(function (Forms\Set $set): void {
                                                        $set('referral_partner_invite_token', Str::random(24));
                                                    })
                                            ),
                                        Forms\Components\Placeholder::make('partner_signup_link')
                                            ->label('Partner signup URL')
                                            ->content(function (Forms\Get $get): string {
                                                return url('/referrals/register?invite=' . trim((string) $get('referral_partner_invite_token')));
                                            }),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Notifications')
                            ->schema([
                                Forms\Components\Section::make('Channel Controls')
                                    ->schema([
                                        Forms\Components\Toggle::make('referral_notify_email')
                                            ->label('Enable email notifications')
                                            ->default(true),
                                        Forms\Components\Toggle::make('referral_notify_sms')
                                            ->label('Enable SMS notifications')
                                            ->default(false),
                                    ]),
                                Forms\Components\Section::make('Student Registered')
                                    ->schema([
                                        Forms\Components\TextInput::make('referral_notify_student_registered_email_subject')
                                            ->label('Email subject'),
                                        Forms\Components\Textarea::make('referral_notify_student_registered_email_template')
                                            ->label('Email message')
                                            ->rows(3)
                                            ->helperText('Available placeholders: {agent_name}, {student_name}, {student_email}, {student_phone}.'),
                                        Forms\Components\Textarea::make('referral_notify_student_registered_sms_template')
                                            ->label('SMS message')
                                            ->rows(2)
                                            ->helperText('Available placeholders: {agent_name}, {student_name}, {student_email}, {student_phone}.'),
                                    ]),
                                Forms\Components\Section::make('Commission Earned')
                                    ->schema([
                                        Forms\Components\TextInput::make('referral_notify_commission_email_subject')
                                            ->label('Email subject'),
                                        Forms\Components\Textarea::make('referral_notify_commission_email_template')
                                            ->label('Email message')
                                            ->rows(3)
                                            ->helperText('Available placeholders: {agent_name}, {commission_amount}, {booking_id}, {student_name}.'),
                                        Forms\Components\Textarea::make('referral_notify_commission_sms_template')
                                            ->label('SMS message')
                                            ->rows(2)
                                            ->helperText('Available placeholders: {agent_name}, {commission_amount}, {booking_id}, {student_name}.'),
                                    ]),
                                Forms\Components\Section::make('Payout Status Update')
                                    ->schema([
                                        Forms\Components\TextInput::make('referral_notify_payout_email_subject')
                                            ->label('Email subject'),
                                        Forms\Components\Textarea::make('referral_notify_payout_email_template')
                                            ->label('Email message')
                                            ->rows(3)
                                            ->helperText('Available placeholders: {agent_name}, {payout_amount}, {payout_status}.'),
                                        Forms\Components\Textarea::make('referral_notify_payout_sms_template')
                                            ->label('SMS message')
                                            ->rows(2)
                                            ->helperText('Available placeholders: {agent_name}, {payout_amount}, {payout_status}.'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Referral Popup')
                            ->schema([
                                Forms\Components\Section::make('Popup Notification')
                                    ->schema([
                                        Forms\Components\Toggle::make('referral_popup_enabled')
                                            ->label('Enable referral popup notification')
                                            ->default(false),
                                        Forms\Components\TextInput::make('referral_popup_title')
                                            ->label('Popup title')
                                            ->maxLength(255)
                                            ->visible(fn (Forms\Get $get): bool => (bool) $get('referral_popup_enabled')),
                                        Forms\Components\Textarea::make('referral_popup_body')
                                            ->label('Popup message')
                                            ->rows(4)
                                            ->visible(fn (Forms\Get $get): bool => (bool) $get('referral_popup_enabled')),
                                        Forms\Components\DateTimePicker::make('referral_popup_start_at')
                                            ->label('Start date/time')
                                            ->seconds(false)
                                            ->visible(fn (Forms\Get $get): bool => (bool) $get('referral_popup_enabled')),
                                        Forms\Components\DateTimePicker::make('referral_popup_end_at')
                                            ->label('End date/time')
                                            ->seconds(false)
                                            ->visible(fn (Forms\Get $get): bool => (bool) $get('referral_popup_enabled')),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SystemSetting::setSetting('referral_enabled', !empty($data['referral_enabled']) ? '1' : '0');
        SystemSetting::setSetting('referral_students_can_be_agents', !empty($data['referral_students_can_be_agents']) ? '1' : '0');
        SystemSetting::setSetting('referral_default_commission_type', (string) ($data['referral_default_commission_type'] ?? 'percentage'));
        SystemSetting::setSetting('referral_default_commission_value', (string) ((float) ($data['referral_default_commission_value'] ?? 5)));
        SystemSetting::setSetting('referral_min_payout', (string) ((float) ($data['referral_min_payout'] ?? 0)));
        SystemSetting::setSetting('referral_notify_email', !empty($data['referral_notify_email']) ? '1' : '0');
        SystemSetting::setSetting('referral_notify_sms', !empty($data['referral_notify_sms']) ? '1' : '0');
        SystemSetting::setSetting('referral_notify_student_registered_email_subject', (string) ($data['referral_notify_student_registered_email_subject'] ?? 'New referral registration'));
        SystemSetting::setSetting('referral_notify_student_registered_email_template', (string) ($data['referral_notify_student_registered_email_template'] ?? ''));
        SystemSetting::setSetting('referral_notify_student_registered_sms_template', (string) ($data['referral_notify_student_registered_sms_template'] ?? ''));
        SystemSetting::setSetting('referral_notify_commission_email_subject', (string) ($data['referral_notify_commission_email_subject'] ?? 'Referral commission earned'));
        SystemSetting::setSetting('referral_notify_commission_email_template', (string) ($data['referral_notify_commission_email_template'] ?? ''));
        SystemSetting::setSetting('referral_notify_commission_sms_template', (string) ($data['referral_notify_commission_sms_template'] ?? ''));
        SystemSetting::setSetting('referral_notify_payout_email_subject', (string) ($data['referral_notify_payout_email_subject'] ?? 'Referral payout update'));
        SystemSetting::setSetting('referral_notify_payout_email_template', (string) ($data['referral_notify_payout_email_template'] ?? ''));
        SystemSetting::setSetting('referral_notify_payout_sms_template', (string) ($data['referral_notify_payout_sms_template'] ?? ''));
        SystemSetting::setSetting('referral_popup_enabled', !empty($data['referral_popup_enabled']) ? '1' : '0');
        SystemSetting::setSetting('referral_popup_title', (string) ($data['referral_popup_title'] ?? ''));
        SystemSetting::setSetting('referral_popup_body', (string) ($data['referral_popup_body'] ?? ''));
        SystemSetting::setSetting('referral_popup_start_at', $this->toDateTimeSettingValue($data['referral_popup_start_at'] ?? null));
        SystemSetting::setSetting('referral_popup_end_at', $this->toDateTimeSettingValue($data['referral_popup_end_at'] ?? null));
        SystemSetting::setSetting('referral_partner_invite_token', trim((string) ($data['referral_partner_invite_token'] ?? '')));

        Notification::make()
            ->success()
            ->title('Referral settings saved')
            ->send();
    }

    private function settingAsDateTime(string $key): ?Carbon
    {
        $value = trim((string) SystemSetting::getSetting($key, ''));
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function toDateTimeSettingValue(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->toDateTimeString();
        }

        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return '';
        }

        try {
            return Carbon::parse($raw)->toDateTimeString();
        } catch (\Throwable $e) {
            return '';
        }
    }
}
