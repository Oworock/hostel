<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\Hostel;
use App\Models\User;
use App\Services\SmsGatewayService;
use App\Services\StudentAudienceService;

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
        $this->form->fill([
            'recipient_type' => 'all',
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
            [$successCount, $failedCount, $totalCount] = $this->sendSMS($data);
            
            Notification::make()
                ->title('Success')
                ->body("SMS processed for {$totalCount} recipient(s). Successful: {$successCount}, Failed: {$failedCount}.")
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

    /**
     * @return array{0:int,1:int,2:int}
     */
    protected function sendSMS(array $data): array
    {
        $sms = app(SmsGatewayService::class);
        if (!$sms->isConfigured()) {
            throw new \RuntimeException('SMS gateway is not configured. Configure SMS in system settings first.');
        }

        $recipients = $this->resolveRecipients($data);
        $totalCount = $recipients->count();
        if ($totalCount === 0) {
            throw new \RuntimeException('No recipient matched your current audience filter.');
        }

        $successCount = 0;
        $failedCount = 0;
        foreach ($recipients as $recipient) {
            if ($sms->send((string) $recipient->phone, (string) $data['message'])) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return [$successCount, $failedCount, $totalCount];
    }

    protected function resolveRecipients(array $data)
    {
        $segment = (string) ($data['recipient_type'] ?? 'all');
        if ($segment === 'all_managers') {
            return User::query()
                ->where('role', 'manager')
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get()
                ->unique('id')
                ->values();
        }
        if ($segment === 'specific_managers') {
            $managerIds = collect($data['manager_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values()->all();
            return User::query()
                ->where('role', 'manager')
                ->whereIn('id', $managerIds)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get()
                ->unique('id')
                ->values();
        }
        if ($segment === 'managers_hostel') {
            $hostelId = (int) ($data['hostel_id'] ?? 0);
            return User::query()
                ->where('role', 'manager')
                ->where(function ($q) use ($hostelId): void {
                    $q->where('hostel_id', $hostelId)
                        ->orWhereHas('managedHostels', fn ($mq) => $mq->where('hostels.id', $hostelId));
                })
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get()
                ->unique('id')
                ->values();
        }

        $students = app(StudentAudienceService::class)->resolve($segment, [
            'hostel_id' => $data['hostel_id'] ?? null,
            'student_ids' => $data['student_ids'] ?? [],
        ]);

        return $students
            ->filter(fn (User $user) => filled($user->phone))
            ->unique('id')
            ->values();
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
        return auth()->user()?->role === 'admin';
    }
}
