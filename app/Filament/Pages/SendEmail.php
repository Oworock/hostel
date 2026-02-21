<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\Hostel;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Services\StudentAudienceService;

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
        $this->form->fill([
            'recipient_type' => 'all',
            'subject' => '',
            'message' => '',
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('recipient_type')
                    ->label('Send To')
                    ->options([
                        'all' => 'All Students',
                        'active' => 'Active Students',
                        'inactive' => 'Inactive Students',
                        'expired_booking' => 'Students With Expired Bookings',
                        'hostel' => 'Specific Hostel',
                        'specific' => 'Specific Students',
                        'all_managers' => 'All Managers',
                        'managers_hostel' => 'Managers In Specific Hostel',
                        'specific_managers' => 'Specific Managers',
                    ])
                    ->required()
                    ->live(),

                Forms\Components\Select::make('hostel_id')
                    ->label('Hostel')
                    ->options(fn () => Hostel::query()->orderBy('name')->pluck('name', 'id'))
                    ->visible(fn(Forms\Get $get) => in_array($get('recipient_type'), ['hostel', 'managers_hostel'], true))
                    ->required(fn(Forms\Get $get) => in_array($get('recipient_type'), ['hostel', 'managers_hostel'], true)),

                Forms\Components\Select::make('student_ids')
                    ->label('Students')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => User::query()->where('role', 'student')->orderBy('name')->pluck('name', 'id'))
                    ->visible(fn (Forms\Get $get) => $get('recipient_type') === 'specific')
                    ->required(fn (Forms\Get $get) => $get('recipient_type') === 'specific'),

                Forms\Components\Select::make('manager_ids')
                    ->label('Managers')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => User::query()->where('role', 'manager')->orderBy('name')->pluck('name', 'id'))
                    ->visible(fn (Forms\Get $get) => $get('recipient_type') === 'specific_managers')
                    ->required(fn (Forms\Get $get) => $get('recipient_type') === 'specific_managers'),

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
            $recipients = $this->resolveRecipients($data);
            $recipientCount = count($recipients);
            if ($recipientCount === 0) {
                throw new \RuntimeException('No recipient matched your current audience filter.');
            }

            foreach ($recipients as $email) {
                Mail::send('emails.bulk', [
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                ], function ($message) use ($email, $data) {
                    $message->to($email)
                        ->subject($data['subject']);
                });
            }
            
            Notification::make()
                ->title('Success')
                ->body('Email sent to ' . $recipientCount . ' recipient(s)!')
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

    protected function resolveRecipients(array $data): array
    {
        $segment = (string) ($data['recipient_type'] ?? 'all');
        if ($segment === 'all_managers') {
            return User::query()
                ->where('role', 'manager')
                ->pluck('email')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }
        if ($segment === 'specific_managers') {
            $managerIds = collect($data['manager_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values()->all();
            return User::query()
                ->where('role', 'manager')
                ->whereIn('id', $managerIds)
                ->pluck('email')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }
        if ($segment === 'managers_hostel') {
            $hostelId = (int) ($data['hostel_id'] ?? 0);
            return User::query()
                ->where('role', 'manager')
                ->where(function ($q) use ($hostelId): void {
                    $q->where('hostel_id', $hostelId)
                        ->orWhereHas('managedHostels', fn ($mq) => $mq->where('hostels.id', $hostelId));
                })
                ->pluck('email')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return app(StudentAudienceService::class)
            ->resolve($segment, [
                'hostel_id' => $data['hostel_id'] ?? null,
                'student_ids' => $data['student_ids'] ?? [],
            ])
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();
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
        return auth()->user()?->role === 'admin';
    }
}
