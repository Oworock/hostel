<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralPayoutRequestResource\Pages;
use App\Models\Addon;
use App\Models\ReferralPayoutRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralPayoutRequestResource extends Resource
{
    protected static ?string $model = ReferralPayoutRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?int $navigationSort = 8;

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
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'paid' => 'Paid',
                ])
                ->required(),
            Forms\Components\DateTimePicker::make('approved_at'),
            Forms\Components\DateTimePicker::make('paid_at'),
            Forms\Components\Textarea::make('note')->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agent.name')->label('Referral')->searchable(),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('bank_name')->placeholder('-'),
                Tables\Columns\TextColumn::make('account_name')->placeholder('-'),
                Tables\Columns\TextColumn::make('account_number')->placeholder('-'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralPayoutRequests::route('/'),
            'edit' => Pages\EditReferralPayoutRequest::route('/{record}/edit'),
        ];
    }
}
