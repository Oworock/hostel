<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Booking;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('booking_id', null))
                            ->label('Student'),

                        Forms\Components\Select::make('booking_id')
                            ->options(function (Get $get) {
                                $userId = $get('user_id');
                                if (!$userId) {
                                    return [];
                                }

                                return Booking::where('user_id', $userId)
                                    ->with('room')
                                    ->latest()
                                    ->get()
                                    ->mapWithKeys(fn (Booking $booking) => [
                                        $booking->id => 'Booking #' . $booking->id . ' - Room ' . ($booking->room->room_number ?? 'N/A'),
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload(false)
                            ->required()
                            ->label('Booking')
                            ->helperText('Select student first, then choose one of their bookings.'),
                        
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->prefix(fn () => self::getCurrencySymbol(config('app.currency', 'NGN')))
                            ->step(0.01),
                        
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'card' => 'Credit/Debit Card',
                                'cash' => 'Cash',
                                'check' => 'Check',
                                'manual_admin' => 'Manual (Admin)',
                            ])
                            ->required(),
                        
                        Forms\Components\DateTimePicker::make('payment_date')
                            ->default(now())
                            ->required(),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending')
                            ->required(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking.room.hostel.name')
                    ->label('Hostel')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('booking.user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(function ($state) {
                        $currency = config('app.currency', 'NGN');
                        $currencySymbol = self::getCurrencySymbol($currency);
                        return $currencySymbol . number_format($state, 2);
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('payment_date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'card' => 'Credit/Debit Card',
                        'cash' => 'Cash',
                        'check' => 'Check',
                        'manual_admin' => 'Manual (Admin)',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
    
    private static function getCurrencySymbol(string $code): string
    {
        $symbols = [
            'NGN' => '₦',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            'ZAR' => 'R',
        ];
        
        return $symbols[$code] ?? $code;
    }
}
