<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewayResource\Pages;
use App\Filament\Resources\PaymentGatewayResource\RelationManagers;
use App\Models\PaymentGateway;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationGroup = 'Payments';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('e.g., Paystack, Flutterwave'),
                Forms\Components\Textarea::make('public_key')
                    ->required()
                    ->maxLength(500)
                    ->label('Public Key')
                    ->helperText('Your payment gateway public key'),
                Forms\Components\TextInput::make('secret_key')
                    ->required()
                    ->maxLength(500)
                    ->label('Secret Key')
                    ->password()
                    ->revealable()
                    ->helperText('Your payment gateway secret key'),
                Forms\Components\TextInput::make('transaction_fee')
                    ->numeric()
                    ->default(0)
                    ->suffix('%')
                    ->helperText('Transaction fee percentage'),
                Forms\Components\Toggle::make('is_active')
                    ->default(false)
                    ->helperText('Enable this payment gateway'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('public_key')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_fee')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%'),
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
            'index' => Pages\ListPaymentGateways::route('/'),
            'create' => Pages\CreatePaymentGateway::route('/create'),
            'edit' => Pages\EditPaymentGateway::route('/{record}/edit'),
        ];
    }
}
