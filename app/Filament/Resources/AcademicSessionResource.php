<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicSessionResource\Pages;
use App\Filament\Resources\AcademicSessionResource\RelationManagers;
use App\Models\AcademicSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AcademicSessionResource extends Resource
{
    protected static ?string $model = AcademicSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'Academic Sessions';
    
    protected static ?int $navigationSort = 42;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('session_name')
                    ->label('Session Name')
                    ->placeholder('e.g., 2025/2026')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('start_year')
                    ->label('Start Year')
                    ->numeric()
                    ->required()
                    ->minValue(2000),
                Forms\Components\TextInput::make('end_year')
                    ->label('End Year')
                    ->numeric()
                    ->required()
                    ->minValue(2000),
                Forms\Components\Toggle::make('is_active')
                    ->label('Is Active')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('session_name')
                    ->label('Session Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_year')
                    ->label('Start Year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_year')
                    ->label('End Year')
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListAcademicSessions::route('/'),
            'create' => Pages\CreateAcademicSession::route('/create'),
            'edit' => Pages\EditAcademicSession::route('/{record}/edit'),
        ];
    }
}
