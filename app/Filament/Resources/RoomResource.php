<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationGroup = 'Hostel Management';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Room Information')
                    ->schema([
                        Forms\Components\Select::make('hostel_id')
                            ->relationship('hostel', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Hostel'),
                        
                        Forms\Components\TextInput::make('room_number')
                            ->required()
                            ->maxLength(255)
                            ->label('Room Number'),
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'single' => 'Single Occupancy',
                                'double' => 'Double Occupancy',
                                'triple' => 'Triple Occupancy',
                                'quad' => 'Quad Occupancy',
                                'dormitory' => 'Dormitory',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->label('Bed Capacity'),
                        
                        Forms\Components\TextInput::make('price_per_month')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->label('Price per Month (₦)'),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(500),
                        
                        Forms\Components\Toggle::make('is_available')
                            ->default(true)
                            ->label('Available for Booking'),
                    ]),
                
                Forms\Components\Section::make('Room Images')
                    ->description('Add up to 10 images of the room. First image will be the cover image.')
                    ->schema([
                        Forms\Components\Repeater::make('images')
                            ->relationship('images')
                            ->minItems(0)
                            ->maxItems(10)
                            ->reorderable(true)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Image')
                                    ->image()
                                    ->directory('room-images')
                                    ->maxSize(5120)
                                    ->required(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hostel.name')
                    ->searchable()
                    ->sortable()
                    ->label('Hostel'),
                
                Tables\Columns\TextColumn::make('room_number')
                    ->searchable()
                    ->sortable()
                    ->label('Room'),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'info' => 'single',
                        'info' => 'double',
                        'warning' => 'triple',
                        'warning' => 'quad',
                        'secondary' => 'dormitory',
                    ]),
                
                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->sortable()
                    ->label('Beds'),
                
                Tables\Columns\TextColumn::make('price_per_month')
                    ->formatStateUsing(function ($state) {
                        $currency = config('app.currency', 'NGN');
                        $currencySymbol = self::getCurrencySymbol($currency);
                        return $currencySymbol . number_format($state, 2);
                    })
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('is_available')
                    ->getStateUsing(fn ($record) => $record->is_available ? 'Available' : 'Unavailable')
                    ->colors([
                        'success' => 'Available',
                        'danger' => 'Unavailable',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('hostel_id')
                    ->relationship('hostel', 'name'),
                
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'single' => 'Single',
                        'double' => 'Double',
                        'triple' => 'Triple',
                        'quad' => 'Quad',
                        'dormitory' => 'Dormitory',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_available'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
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
