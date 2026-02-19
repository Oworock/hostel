<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Addon;
use App\Models\Asset;
use App\Models\AssetMovement;
use App\Models\Hostel;
use App\Models\User;
use App\Services\OutboundWebhookService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Hostel Assets';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Addon::isActive('asset-management') && Schema::hasTable('assets');
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
                Forms\Components\FileUpload::make('image_path')
                    ->label('Asset Image')
                    ->disk('public')
                    ->directory('assets')
                    ->image()
                    ->imageEditor()
                    ->maxSize(4096)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('asset_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('asset_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('category')
                    ->maxLength(255),
                Forms\Components\TextInput::make('brand')
                    ->maxLength(255),
                Forms\Components\TextInput::make('model')
                    ->maxLength(255),
                Forms\Components\TextInput::make('serial_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('manufacturer')
                    ->maxLength(255),
                Forms\Components\TextInput::make('supplier')
                    ->maxLength(255),
                Forms\Components\TextInput::make('invoice_reference')
                    ->label('Invoice / Reference')
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'maintenance' => 'Maintenance',
                        'retired' => 'Retired',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\Select::make('condition')
                    ->options([
                        'excellent' => 'Excellent',
                        'good' => 'Good',
                        'fair' => 'Fair',
                        'poor' => 'Poor',
                    ])
                    ->default('good')
                    ->required(),
                Forms\Components\DatePicker::make('purchase_date'),
                Forms\Components\DatePicker::make('warranty_expiry_date'),
                Forms\Components\TextInput::make('acquisition_cost')
                    ->numeric()
                    ->prefix(getCurrencySymbol())
                    ->inputMode('decimal'),
                Forms\Components\TextInput::make('maintenance_schedule')
                    ->helperText('Example: Monthly check, Quarterly service'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/favicon.ico'))
                    ->label('Image'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('asset_number')->searchable(),
                Tables\Columns\TextColumn::make('asset_code')->searchable(),
                Tables\Columns\TextColumn::make('hostel.name')->label('Hostel')->searchable(),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('serial_number')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('location')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('acquisition_cost')
                    ->label('Cost')
                    ->formatStateUsing(fn ($state) => $state !== null ? formatCurrency((float) $state) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('condition')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('issues_count')
                    ->counts('issues')
                    ->label('Issue Reports'),
            ])
            ->actions([
                Tables\Actions\Action::make('move_now')
                    ->label('Move Hostel')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('to_hostel_id')
                            ->options(fn () => Hostel::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->label('Destination Hostel')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('admin_note')
                            ->label('Admin Comment')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (Asset $record, array $data): void {
                        if ((int) $data['to_hostel_id'] === (int) $record->hostel_id) {
                            Notification::make()->danger()->title('Destination hostel must be different')->send();
                            return;
                        }

                        $receivingManagerId = User::query()
                            ->where('role', 'manager')
                            ->whereHas('managedHostels', fn ($q) => $q->where('hostels.id', $data['to_hostel_id']))
                            ->orderBy('id')
                            ->value('id');

                        AssetMovement::create([
                            'asset_id' => $record->id,
                            'from_hostel_id' => $record->hostel_id,
                            'to_hostel_id' => $data['to_hostel_id'],
                            'requested_by' => auth()->id(),
                            'receiving_manager_id' => $receivingManagerId,
                            'request_note' => 'Admin direct movement',
                            'status' => 'approved',
                            'admin_decided_by' => auth()->id(),
                            'admin_decided_at' => now(),
                            'admin_note' => $data['admin_note'],
                            'moved_at' => now(),
                        ]);

                        $record->hostel_id = $data['to_hostel_id'];
                        $record->save();

                        app(OutboundWebhookService::class)->dispatch('asset.movement_approved', [
                            'asset_id' => $record->id,
                            'admin_id' => auth()->id(),
                            'to_hostel_id' => (int) $data['to_hostel_id'],
                            'status' => 'approved',
                        ]);

                        Notification::make()->success()->title('Asset moved successfully')->send();
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() && Addon::isActive('asset-management') && Schema::hasTable('assets');
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
