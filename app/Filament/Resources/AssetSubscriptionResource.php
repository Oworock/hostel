<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetSubscriptionResource\Pages;
use App\Models\Addon;
use App\Models\AssetSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class AssetSubscriptionResource extends Resource
{
    protected static ?string $model = AssetSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return 'Intangible Assets';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Addon::isActive('asset-management') && Schema::hasTable('asset_subscriptions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('hostel_id')
                    ->relationship('hostel', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('service_type')
                    ->options([
                        'tv_subscription' => 'TV Subscription',
                        'internet' => 'Internet',
                        'software' => 'Software License',
                        'security' => 'Security Service',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->default('other'),
                Forms\Components\TextInput::make('provider')->maxLength(255),
                Forms\Components\TextInput::make('reference')->label('Invoice / Reference')->maxLength(255),
                Forms\Components\DatePicker::make('start_date'),
                Forms\Components\DatePicker::make('expires_at')->required(),
                Forms\Components\Select::make('billing_cycle')
                    ->options([
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'semi_annually' => 'Semi-Annually',
                        'annually' => 'Annually',
                        'custom' => 'Custom',
                    ])
                    ->required()
                    ->default('monthly'),
                Forms\Components\TextInput::make('cost')
                    ->numeric()
                    ->inputMode('decimal')
                    ->prefix(getCurrencySymbol()),
                Forms\Components\Toggle::make('auto_renew')->default(false),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expired',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('expires_at')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', (string) $state))),
                Tables\Columns\TextColumn::make('hostel.name')->label('Hostel')->searchable(),
                Tables\Columns\TextColumn::make('provider'),
                Tables\Columns\TextColumn::make('cost')
                    ->formatStateUsing(fn ($state) => $state !== null ? formatCurrency((float) $state) : '-'),
                Tables\Columns\TextColumn::make('expires_at')->date(),
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('Days Left')
                    ->getStateUsing(fn (AssetSubscription $record) => $record->daysRemaining())
                    ->badge()
                    ->color(fn (int $state) => $state < 0 ? 'danger' : ($state <= 7 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetSubscriptions::route('/'),
            'create' => Pages\CreateAssetSubscription::route('/create'),
            'edit' => Pages\EditAssetSubscription::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() && Addon::isActive('asset-management') && Schema::hasTable('asset_subscriptions');
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny();
    }
}
