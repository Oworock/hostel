<?php

namespace App\Filament\Resources\SMSBroadcastResource\Pages;

use Filament\Forms;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\User;
use App\Models\Hostel;
use App\Filament\Resources\SMSBroadcastResource;
use Illuminate\Support\Facades\Http;

class SendSMS extends Page
{
    protected static string $resource = SMSBroadcastResource::class;

    protected static string $view = 'filament.pages.send-sms';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Send SMS to Students')
                    ->description('Send SMS notifications to students')
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
                            ->options(Hostel::pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => $get('recipient_type') === 'hostel'),
                        
                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->options(User::where('role', 'student')->pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => $get('recipient_type') === 'student'),
                        
                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->maxLength(160)
                            ->rows(4)
                            ->helperText('SMS is limited to 160 characters'),
                        
                        Forms\Components\Checkbox::make('save_template')
                            ->label('Save as Template')
                            ->default(false),
                        
                        Forms\Components\TextInput::make('template_name')
                            ->label('Template Name')
                            ->visible(fn (Forms\Get $get) => $get('save_template'))
                            ->required(fn (Forms\Get $get) => $get('save_template')),
                    ]),
            ]);
    }

    public function sendSMS(): void
    {
        $data = $this->form->getState();

        try {
            $smsProvider = config('services.sms.provider');
            
            if ($smsProvider === 'none' || !config('services.sms.url')) {
                Notification::make()
                    ->warning()
                    ->title('SMS Not Configured')
                    ->body('Please configure SMS settings before sending.')
                    ->send();
                return;
            }

            $recipients = $this->getRecipients($data['recipient_type'], $data);
            
            if ($recipients->isEmpty()) {
                Notification::make()
                    ->warning()
                    ->title('No Recipients')
                    ->body('No students found for the selected criteria.')
                    ->send();
                return;
            }

            $failedCount = 0;
            $successCount = 0;

            foreach ($recipients as $recipient) {
                try {
                    $response = Http::post(config('services.sms.url'), [
                        'api_key' => config('services.sms.api_key'),
                        'sender_id' => config('services.sms.sender_id'),
                        'to' => $recipient->phone,
                        'message' => $data['message'],
                    ]);

                    if ($response->successful()) {
                        $successCount++;
                    } else {
                        $failedCount++;
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                }
            }

            Notification::make()
                ->success()
                ->title('SMS Sent')
                ->body("Successfully sent to $successCount students. Failed: $failedCount")
                ->send();

            $this->form->fill();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body($e->getMessage())
                ->send();
        }
    }

    private function getRecipients($type, $data)
    {
        switch ($type) {
            case 'all':
                return User::where('role', 'student')->whereNotNull('phone')->get();
            
            case 'hostel':
                return User::whereHas('bookings.room.hostel', function ($query) use ($data) {
                    $query->where('id', $data['hostel_id']);
                })
                ->where('role', 'student')
                ->whereNotNull('phone')
                ->distinct()
                ->get();
            
            case 'student':
                return User::where('id', $data['student_id'])
                    ->where('role', 'student')
                    ->whereNotNull('phone')
                    ->get();
            
            default:
                return collect();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('send')
                ->label('Send SMS')
                ->color('success')
                ->action('sendSMS'),
        ];
    }
}
