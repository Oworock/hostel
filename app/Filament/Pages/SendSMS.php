<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class SendSMS extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Send SMS';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.send-sms';
    
    protected static ?string $slug = 'send-sms';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('recipient_type')
                    ->label('Send To')
                    ->options([
                        'all' => 'All Students',
                        'hostel' => 'Specific Hostel',
                        'student' => 'Specific Student',
                    ])
                    ->required()
                    ->live(),

                Forms\Components\Select::make('hostel_id')
                    ->label('Hostel')
                    ->relationship('hostel', 'name')
                    ->visible(fn(Forms\Get $get) => $get('recipient_type') === 'hostel')
                    ->required(),

                Forms\Components\Select::make('student_id')
                    ->label('Student')
                    ->relationship('student', 'full_name')
                    ->visible(fn(Forms\Get $get) => $get('recipient_type') === 'student')
                    ->required(),

                Forms\Components\Textarea::make('message')
                    ->label('Message (Max 160 characters)')
                    ->required()
                    ->maxLength(160)
                    ->live()
                    ->helperText(fn(Forms\Get $get) => (160 - strlen($get('message') ?? '')) . ' characters remaining'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        try {
            $this->sendSMS($data);
            
            Notification::make()
                ->title('Success')
                ->body('SMS sent successfully!')
                ->success()
                ->send();

            $this->form->fill();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to send SMS: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function sendSMS(array $data): void
    {
        $settings = get_system_settings();
        
        if (empty($settings['sms_url'])) {
            throw new \Exception('SMS gateway not configured');
        }

        $recipients = $this->getRecipients($data['recipient_type'], $data);

        foreach ($recipients as $phone) {
            Http::post($settings['sms_url'], [
                'phone' => $phone,
                'message' => $data['message'],
                'sender_id' => $settings['sms_sender_id'] ?? 'Hostel App',
                'api_key' => $settings['sms_api_key'] ?? '',
            ]);
        }
    }

    protected function getRecipients(string $type, array $data): array
    {
        $phones = [];

        if ($type === 'all') {
            $users = User::where('role', 'student')->pluck('phone')->filter()->toArray();
        } elseif ($type === 'hostel') {
            $users = User::whereHas('booking', function ($query) use ($data) {
                $query->where('hostel_id', $data['hostel_id']);
            })->where('role', 'student')->pluck('phone')->filter()->toArray();
        } else {
            $user = User::find($data['student_id']);
            $users = $user ? [$user->phone] : [];
        }

        return array_filter($users);
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('submit')
                ->label('Send SMS')
                ->submit('submit')
                ->color('primary'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin' || auth()->user()?->role === 'manager';
    }
}
