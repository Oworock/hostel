<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Booking Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Student'),
                        
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'room_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Room'),
                        
                        Forms\Components\Select::make('bed_id')
                            ->relationship('bed', 'bed_number')
                            ->getOptionLabelFromRecordUsing(fn ($record) => ($record->room?->room_number ?? 'Unknown') . ' - Bed ' . $record->bed_number)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Bed'),
                        
                        Forms\Components\DatePicker::make('check_in_date')
                            ->required()
                            ->label('Check-in Date'),
                        
                        Forms\Components\DatePicker::make('check_out_date')
                            ->label('Check-out Date'),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->step(0.01)
                            ->label('Total Amount'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Student'),
                
                Tables\Columns\TextColumn::make('room.room_number')
                    ->searchable()
                    ->sortable()
                    ->label('Room'),
                
                Tables\Columns\TextColumn::make('bed.bed_number')
                    ->searchable()
                    ->sortable()
                    ->label('Bed'),
                
                Tables\Columns\TextColumn::make('check_in_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('check_out_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'completed',
                        'secondary' => 'cancelled',
                    ]),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->formatStateUsing(function ($state) {
                        $currency = config('app.currency', 'NGN');
                        $currencySymbol = self::getCurrencySymbol($currency);
                        return $currencySymbol . number_format($state, 2);
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('logout')
                    ->icon('heroicon-m-arrow-left-on-rectangle')
                    ->color('danger')
                    ->tooltip('Logout student from this booking')
                    ->requiresConfirmation()
                    ->modalHeading('Logout Student')
                    ->modalDescription('Are you sure you want to logout this student from this booking? They will be unallocated from the bed.')
                    ->action(function (Booking $record) {
                        if ($record->bed) {
                            $record->bed->update([
                                'is_occupied' => false,
                                'user_id' => null,
                                'occupied_from' => null,
                            ]);
                        }
                        
                        $record->update(['status' => 'cancelled']);
                        
                        Notification::make()
                            ->success()
                            ->title('Student Logged Out')
                            ->body('Student has been successfully logged out from the booking.')
                            ->send();
                    })
                    ->visible(fn (Booking $record) => in_array($record->status, ['approved', 'completed'])),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
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