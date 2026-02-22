<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralAgentResource\Pages;
use App\Models\Addon;
use App\Models\ReferralAgent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class ReferralAgentResource extends Resource
{
    protected static ?string $model = ReferralAgent::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        return Addon::isActive('referral-system') && (auth()->user()?->isAdmin() ?? false);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Addon::isActive('referral-system') && (auth()->user()?->isAdmin() ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
            Forms\Components\TextInput::make('phone')->maxLength(20),
            Forms\Components\TextInput::make('password')
                ->password()
                ->revealable()
                ->dehydrated(fn ($state) => filled($state))
                ->dehydrateStateUsing(fn ($state) => Hash::make((string) $state))
                ->required(fn (string $operation): bool => $operation === 'create')
                ->label('Password'),
            Forms\Components\TextInput::make('referral_code')
                ->required()
                ->maxLength(20)
                ->helperText('Unique code used in referral links.'),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\Select::make('commission_type')
                ->options([
                    'percentage' => 'Percentage (%)',
                    'fixed' => 'Fixed amount',
                ])
                ->default((string) get_setting('referral_default_commission_type', 'percentage'))
                ->required(),
            Forms\Components\TextInput::make('commission_value')
                ->numeric()
                ->minValue(0)
                ->required()
                ->default((float) get_setting('referral_default_commission_value', 5)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('referral_code')
                    ->badge()
                    ->copyable(),
                Tables\Columns\TextColumn::make('referral_link')
                    ->label('Referral Link')
                    ->getStateUsing(fn (ReferralAgent $record) => $record->referralUrl())
                    ->copyable()
                    ->limit(35),
                Tables\Columns\TextColumn::make('commission_type')->badge(),
                Tables\Columns\TextColumn::make('commission_value'),
                Tables\Columns\TextColumn::make('total_earned'),
                Tables\Columns\TextColumn::make('balance'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralAgents::route('/'),
            'create' => Pages\CreateReferralAgent::route('/create'),
            'edit' => Pages\EditReferralAgent::route('/{record}/edit'),
        ];
    }
}
