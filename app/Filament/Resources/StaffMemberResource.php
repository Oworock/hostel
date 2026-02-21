<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffMemberResource\Pages;
use App\Models\Addon;
use App\Models\Hostel;
use App\Models\StaffMember;
use App\Models\User;
use App\Services\StaffDirectorySyncService;
use App\Services\StaffIdCardService;
use App\Services\StaffPayrollNotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StaffMemberResource extends Resource
{
    protected static ?string $model = StaffMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('Staff');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Addon::isActive('staff-payroll') && Schema::hasTable('staff_members');
    }

    public static function getEloquentQuery(): Builder
    {
        try {
            app(StaffDirectorySyncService::class)->syncCoreUsers();
        } catch (Throwable $e) {
            // Keep the page available even when addon schema updates are still pending.
        }

        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(160),
                Forms\Components\Select::make('user_id')
                    ->label(__('Link Existing User (Admin / Manager)'))
                    ->options(
                        User::query()
                            ->whereIn('role', ['admin', 'super_admin', 'manager'])
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (User $user) => [$user->id => $user->name . ' (' . $user->role . ')'])
                            ->all()
                    )
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set): void {
                        if (!$state) {
                            return;
                        }
                        $user = User::find($state);
                        if (!$user) {
                            return;
                        }
                        $set('full_name', $user->name);
                        $set('email', $user->email);
                        $set('phone', $user->phone);
                        $set('source_role', $user->role);
                    })
                    ->visible(fn (): bool => Schema::hasColumn('staff_members', 'user_id')),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(160)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(32),
                Forms\Components\TextInput::make('bank_name')
                    ->maxLength(120)
                    ->visible(fn (): bool => Schema::hasColumn('staff_members', 'bank_name')),
                Forms\Components\TextInput::make('bank_account_name')
                    ->maxLength(160)
                    ->visible(fn (): bool => Schema::hasColumn('staff_members', 'bank_account_name')),
                Forms\Components\TextInput::make('bank_account_number')
                    ->maxLength(64)
                    ->visible(fn (): bool => Schema::hasColumn('staff_members', 'bank_account_number')),
                Forms\Components\TextInput::make('employee_code')
                    ->maxLength(64)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('department')
                    ->options(self::predefinedOptions('staff_payroll_departments_csv'))
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('value')->required(),
                    ])
                    ->createOptionUsing(fn (array $data): string => (string) ($data['value'] ?? ''))
                    ->native(false),
                Forms\Components\Select::make('category')
                    ->options(self::predefinedOptions('staff_payroll_categories_csv'))
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('value')->required(),
                    ])
                    ->createOptionUsing(fn (array $data): string => (string) ($data['value'] ?? ''))
                    ->native(false)
                    ->visible(fn (): bool => Schema::hasColumn('staff_members', 'category')),
                Forms\Components\TextInput::make('job_title')
                    ->maxLength(120),
                Forms\Components\TextInput::make('source_role')
                    ->maxLength(40)
                    ->visible(fn (): bool => Schema::hasColumn('staff_members', 'source_role')),
                Forms\Components\TextInput::make('base_salary')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix(getCurrencySymbol()),
                Forms\Components\DatePicker::make('joined_on'),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => __('Active'),
                        'inactive' => __('Inactive'),
                        'pending' => __('Pending'),
                        'suspended' => __('Suspended'),
                        'sacked' => __('Sacked'),
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\Toggle::make('is_general_staff')
                    ->label(__('General Staff (All Hostels)'))
                    ->default(true)
                    ->visible(fn (): bool => Schema::hasColumn('staff_members', 'is_general_staff')),
                Forms\Components\Select::make('assigned_hostel_id')
                    ->label(__('Assigned Hostel'))
                    ->options(fn () => Hostel::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->visible(fn (Forms\Get $get): bool => Schema::hasColumn('staff_members', 'assigned_hostel_id') && !((bool) $get('is_general_staff'))),
                Forms\Components\Textarea::make('address')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('profile_image')
                    ->label(__('Passport Photo'))
                    ->image()
                    ->disk('public')
                    ->directory('staff/passports')
                    ->maxSize(4096)
                    ->visible(fn (): bool => Schema::hasColumn('staff_members', 'profile_image')),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->label(__('Bank'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bank_account_name')
                    ->label(__('Account Name'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bank_account_number')
                    ->label(__('Account Number'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('employee_code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('department')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('job_title')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('source_role')
                    ->label(__('Source'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assignedHostel.name')
                    ->label(__('Assigned Hostel'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('base_salary')
                    ->label(__('Base Salary'))
                    ->formatStateUsing(fn ($state) => formatCurrency((float) $state, compact: false))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('joined_on')
                    ->date(),
                Tables\Columns\ImageColumn::make('profile_image')
                    ->disk('public')
                    ->label(__('Passport')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => __('Active'),
                        'inactive' => __('Inactive'),
                        'pending' => __('Pending'),
                        'suspended' => __('Suspended'),
                        'sacked' => __('Sacked'),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('download_id_card')
                    ->label(__('Download ID Card (SVG)'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(function (StaffMember $record): ?string {
                        if (empty($record->id_card_path)) {
                            return null;
                        }

                        return Storage::disk('public')->url((string) $record->id_card_path);
                    }, shouldOpenInNewTab: true)
                    ->visible(fn (StaffMember $record): bool => !empty($record->id_card_path)),
                Tables\Actions\Action::make('download_id_card_png')
                    ->label(__('Download ID Card (PNG)'))
                    ->icon('heroicon-o-photo')
                    ->url(function (StaffMember $record): ?string {
                        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
                            return null;
                        }

                        $path = app(StaffIdCardService::class)->generatePng($record);

                        return Storage::disk('public')->url($path);
                    }, shouldOpenInNewTab: true)
                    ->visible(fn (): bool => extension_loaded('gd') || extension_loaded('imagick')),
                Tables\Actions\Action::make('send_id_card')
                    ->label(__('Send ID Card'))
                    ->icon('heroicon-o-envelope')
                    ->action(function (StaffMember $record): void {
                        try {
                            if (Schema::hasColumn('staff_members', 'id_card_path') && empty($record->id_card_path)) {
                                $record->update(['id_card_path' => app(StaffIdCardService::class)->generate($record)]);
                                $record->refresh();
                            }

                            app(StaffPayrollNotificationService::class)->sendIdCard($record);
                            Notification::make()->success()->title(__('ID card sent'))->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->warning()->title($e->getMessage())->send();
                        } catch (\Throwable $e) {
                            report($e);
                            Notification::make()->danger()->title(__('Unable to send ID card right now.'))->send();
                        }
                    }),
                Tables\Actions\Action::make('suspend')
                    ->label(__('Suspend'))
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->visible(fn (StaffMember $record): bool => $record->status !== 'suspended')
                    ->action(function (StaffMember $record): void {
                        $record->update(['status' => 'suspended']);
                        if (self::canToggleLinkedUser($record) && $record->user) {
                            $record->user->update(['is_active' => false]);
                        }
                    }),
                Tables\Actions\Action::make('sack')
                    ->label(__('Sack'))
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (StaffMember $record): bool => $record->status !== 'sacked')
                    ->action(function (StaffMember $record): void {
                        $record->update(['status' => 'sacked']);
                        if (self::canToggleLinkedUser($record) && $record->user) {
                            $record->user->update(['is_active' => false]);
                        }
                    }),
                Tables\Actions\Action::make('reactivate')
                    ->label(__('Activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (StaffMember $record): bool => in_array($record->status, ['inactive', 'suspended'], true))
                    ->action(function (StaffMember $record): void {
                        $update = ['status' => 'active'];
                        if (Schema::hasColumn('staff_members', 'employee_code') && empty($record->employee_code)) {
                            $update['employee_code'] = self::nextFourDigitCode();
                        }
                        if (Schema::hasColumn('staff_members', 'approved_by')) {
                            $update['approved_by'] = auth()->id();
                        }
                        if (Schema::hasColumn('staff_members', 'approved_at')) {
                            $update['approved_at'] = now();
                        }

                        $record->update($update);
                        if (self::canToggleLinkedUser($record) && $record->user) {
                            $record->user->update(['is_active' => true]);
                        }
                    }),
                Tables\Actions\Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (StaffMember $record): bool => $record->status === 'pending')
                    ->action(function (StaffMember $record): void {
                        $update = ['status' => 'active'];
                        if (Schema::hasColumn('staff_members', 'employee_code') && empty($record->employee_code)) {
                            $update['employee_code'] = self::nextFourDigitCode();
                        }
                        if (Schema::hasColumn('staff_members', 'approved_by')) {
                            $update['approved_by'] = auth()->id();
                        }
                        if (Schema::hasColumn('staff_members', 'approved_at')) {
                            $update['approved_at'] = now();
                        }
                        $record->update($update);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffMembers::route('/'),
            'create' => Pages\CreateStaffMember::route('/create'),
            'edit' => Pages\EditStaffMember::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() && Addon::isActive('staff-payroll') && Schema::hasTable('staff_members');
    }

    private static function canToggleLinkedUser(StaffMember $record): bool
    {
        $role = strtolower(trim((string) ($record->source_role ?? '')));

        return $role === 'staff';
    }

    private static function nextFourDigitCode(): string
    {
        for ($i = 0; $i < 50; $i++) {
            $candidate = (string) random_int(1000, 9999);
            if (!StaffMember::query()->where('employee_code', $candidate)->exists()) {
                return $candidate;
            }
        }

        return (string) random_int(1000, 9999);
    }

    /**
     * @return array<string, string>
     */
    private static function predefinedOptions(string $key): array
    {
        $raw = (string) get_setting($key, '');
        $values = array_values(array_filter(array_map('trim', explode(',', $raw))));

        return collect($values)->mapWithKeys(fn (string $value): array => [$value => $value])->all();
    }
}
