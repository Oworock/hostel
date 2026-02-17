<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsProviderResource\Pages;
use App\Filament\Resources\SmsProviderResource\RelationManagers;
use App\Models\SmsProvider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsProviderResource extends Resource
{
    protected static ?string $model = SmsProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    
    protected static ?string $navigationGroup = 'SMS & Marketing';
    
    protected static ?int $navigationSort = 2;
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('e.g., Twilio, Termii, Africa\s Talking'),
                Forms\Components\TextInput::make('api_key')
                    ->required()
                    ->maxLength(500)
                    ->password()
                    ->revealable(),
                Forms\Components\TextInput::make('api_secret')
                    ->maxLength(500)
                    ->password()
                    ->revealable()
                    ->helperText('Leave empty if not required'),
                Forms\Components\TextInput::make('sender_id')
                    ->maxLength(20)
                    ->helperText('Your sender ID for SMS'),
                Forms\Components\KeyValue::make('config')
                    ->columnSpanFull()
                    ->helperText('Additional configuration as key-value pairs'),
                Forms\Components\Toggle::make('is_active')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sender_id'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ListSmsProviders::route('/'),
            'create' => Pages\CreateSmsProvider::route('/create'),
            'edit' => Pages\EditSmsProvider::route('/{record}/edit'),
        ];
    }
}
