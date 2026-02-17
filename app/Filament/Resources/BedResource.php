<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BedResource\Pages;
use App\Filament\Resources\BedResource\RelationManagers;
use App\Models\Bed;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BedResource extends Resource
{
    protected static ?string $model = Bed::class;

    protected static ?string $navigationGroup = 'Hostel Management';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bed Information')
                    ->schema([
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'room_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Room'),
                        
                        Forms\Components\TextInput::make('bed_number')
                            ->required()
                            ->maxLength(255)
                            ->label('Bed Number/Name'),
                        
                        Forms\Components\TextInput::make('name')
                            ->maxLength(255)
                            ->label('Bed Label (Optional)'),
                        
                        Forms\Components\Toggle::make('is_occupied')
                            ->disabled()
                            ->default(false)
                            ->label('Currently Occupied'),
                    ]),
                
                Forms\Components\Section::make('Bed Images')
                    ->description('Add up to 10 images of the bed space.')
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
                                    ->directory('bed-images')
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
                Tables\Columns\TextColumn::make('room.hostel.name')
                    ->searchable()
                    ->sortable()
                    ->label('Hostel'),
                
                Tables\Columns\TextColumn::make('room.room_number')
                    ->sortable()
                    ->searchable()
                    ->label('Room'),
                
                Tables\Columns\TextColumn::make('bed_number')
                    ->searchable()
                    ->label('Bed Number'),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Label'),
                
                Tables\Columns\BadgeColumn::make('is_occupied')
                    ->getStateUsing(fn ($record) => $record->is_occupied ? 'Occupied' : 'Available')
                    ->colors([
                        'success' => 'Available',
                        'danger' => 'Occupied',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('available')
                    ->label('Available Only')
                    ->query(fn (Builder $query): Builder => $query->where('is_occupied', false))
                    ->toggle(),
                
                Tables\Filters\SelectFilter::make('is_occupied')
                    ->options([
                        0 => 'Available',
                        1 => 'Occupied',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('logout')
                    ->label('Remove Student')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->visible(fn ($record) => $record->is_occupied)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'is_occupied' => false,
                            'user_id' => null,
                            'occupied_from' => null,
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Student Removed')
                            ->body('Student has been removed from this bed.')
                            ->send();
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeds::route('/'),
            'create' => Pages\CreateBed::route('/create'),
            'edit' => Pages\EditBed::route('/{record}/edit'),
        ];
    }
}
