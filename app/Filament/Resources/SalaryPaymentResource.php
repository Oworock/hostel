<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryPaymentResource\Pages;
use App\Models\Addon;
use App\Models\SalaryPayment;
use App\Models\StaffMember;
use App\Services\StaffDirectorySyncService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class SalaryPaymentResource extends Resource
{
    protected static ?string $model = SalaryPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?int $navigationSort = 11;

    public static function getNavigationLabel(): string
    {
        return __('Payroll');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Addon::isActive('staff-payroll') && Schema::hasTable('salary_payments');
    }

    public static function form(Form $form): Form
    {
        $monthOptions = self::monthOptions();
        try {
            app(StaffDirectorySyncService::class)->syncCoreUsers();
        } catch (\Throwable $e) {
            // Keep payroll form available even when staff sync fails.
        }

        return $form
            ->schema([
                Forms\Components\Select::make('staff_member_id')
                    ->options(function (): array {
                        try {
                            app(StaffDirectorySyncService::class)->syncCoreUsers();
                        } catch (\Throwable $e) {
                            // Keep options loading resilient.
                        }

                        return StaffMember::query()
                            ->orderBy('full_name')
                            ->pluck('full_name', 'id')
                            ->all();
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->prefix(getCurrencySymbol()),
                Forms\Components\Select::make('payment_month')
                    ->options($monthOptions)
                    ->required(),
                Forms\Components\TextInput::make('payment_year')
                    ->numeric()
                    ->required()
                    ->default((int) now()->year),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => __('Pending'),
                        'paid' => __('Paid'),
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\DateTimePicker::make('paid_at'),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'bank_transfer' => __('Bank Transfer'),
                        'cash' => __('Cash'),
                        'card' => __('Card'),
                        'mobile_money' => __('Mobile Money'),
                        'other' => __('Other'),
                    ])
                    ->default('bank_transfer'),
                Forms\Components\TextInput::make('reference')
                    ->maxLength(120),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staffMember.full_name')
                    ->label(__('Staff'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->formatStateUsing(fn ($state) => formatCurrency((float) $state, compact: false))
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_month')
                    ->label(__('Month'))
                    ->formatStateUsing(fn ($state) => self::monthOptions()[(int) $state] ?? '-'),
                Tables\Columns\TextColumn::make('payment_year')
                    ->label(__('Year')),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reference')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => __('Pending'),
                        'paid' => __('Paid'),
                    ]),
                Tables\Filters\SelectFilter::make('payment_year')
                    ->options(fn () => SalaryPayment::query()
                        ->select('payment_year')
                        ->distinct()
                        ->orderByDesc('payment_year')
                        ->pluck('payment_year', 'payment_year')
                        ->all()),
            ])
            ->actions([
                Tables\Actions\Action::make('view_payslip')
                    ->label(__('Payslip'))
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn (SalaryPayment $record): bool => $record->status === 'paid')
                    ->url(fn (SalaryPayment $record): string => URL::temporarySignedRoute(
                        'staff.payslips.show',
                        now()->addDays(7),
                        ['salaryPayment' => $record->id]
                    ), shouldOpenInNewTab: true),
                Tables\Actions\Action::make('mark_paid')
                    ->label(__('Mark Paid'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SalaryPayment $record): bool => $record->status !== 'paid')
                    ->action(function (SalaryPayment $record): void {
                        $record->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                            'processed_by' => auth()->id(),
                        ]);
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
            'index' => Pages\ListSalaryPayments::route('/'),
            'create' => Pages\CreateSalaryPayment::route('/create'),
            'edit' => Pages\EditSalaryPayment::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() && Addon::isActive('staff-payroll') && Schema::hasTable('salary_payments');
    }

    /**
     * @return array<int, string>
     */
    private static function monthOptions(): array
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }
}
