<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCommissionResource\Pages;
use App\Models\Addon;
use App\Models\ReferralCommission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralCommissionResource extends Resource
{
    protected static ?string $model = ReferralCommission::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?int $navigationSort = 7;

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
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                ])
                ->required(),
            Forms\Components\DateTimePicker::make('paid_at')
                ->visible(fn (Forms\Get $get) => $get('status') === 'paid'),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agent.name')->label('Referral')->searchable(),
                Tables\Columns\TextColumn::make('student.name')->label('Student')->searchable(),
                Tables\Columns\TextColumn::make('booking_id')->label('Booking'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('earned_at')->dateTime()->placeholder('-'),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->placeholder('-'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralCommissions::route('/'),
            'edit' => Pages\EditReferralCommission::route('/{record}/edit'),
        ];
    }
}
