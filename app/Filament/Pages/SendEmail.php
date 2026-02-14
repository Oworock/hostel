<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\User;
use App\Mail\BulkEmail;
use Illuminate\Support\Facades\Mail;

class SendEmail extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Send Email';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.send-email';
    
    protected static ?string $slug = 'send-email';

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
                        'managers' => 'All Managers',
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

                Forms\Components\TextInput::make('subject')
                    ->label('Email Subject')
                    ->required(),

                Forms\Components\RichEditor::make('message')
                    ->label('Email Message')
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        try {
            $recipients = $this->getRecipients($data['recipient_type'], $data);

            foreach ($recipients as $email) {
                Mail::send('emails.bulk', [
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                ], function ($message) use ($email) {
                    $message->to($email)
                        ->subject($data['subject']);
                });
            }
            
            Notification::make()
                ->title('Success')
                ->body('Email sent to ' . count($recipients) . ' recipient(s)!')
                ->success()
                ->send();

            $this->form->fill();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to send email: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getRecipients(string $type, array $data): array
    {
        $emails = [];

        if ($type === 'all') {
            $users = User::where('role', 'student')->pluck('email')->filter()->toArray();
        } elseif ($type === 'hostel') {
            $users = User::whereHas('booking', function ($query) use ($data) {
                $query->where('hostel_id', $data['hostel_id']);
            })->where('role', 'student')->pluck('email')->filter()->toArray();
        } elseif ($type === 'managers') {
            $users = User::where('role', 'manager')->pluck('email')->filter()->toArray();
        } else {
            $user = User::find($data['student_id']);
            $users = $user ? [$user->email] : [];
        }

        return array_filter($users);
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('submit')
                ->label('Send Email')
                ->submit('submit')
                ->color('primary'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin' || auth()->user()?->role === 'manager';
    }
}
