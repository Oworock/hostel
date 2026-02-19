<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetMovementResource\Pages;
use App\Models\Addon;
use App\Models\AssetMovement;
use App\Notifications\SystemEventNotification;
use App\Services\OutboundWebhookService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class AssetMovementResource extends Resource
{
    protected static ?string $model = AssetMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'Asset Movements';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Addon::isActive('asset-management') && Schema::hasTable('asset_movements');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('asset_id')->relationship('asset', 'name')->disabled(),
                Forms\Components\Select::make('from_hostel_id')->relationship('fromHostel', 'name')->disabled(),
                Forms\Components\Select::make('to_hostel_id')->relationship('toHostel', 'name')->disabled(),
                Forms\Components\Select::make('requested_by')->relationship('requester', 'name')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
                Forms\Components\Textarea::make('request_note')->disabled()->columnSpanFull(),
                Forms\Components\Textarea::make('receiving_manager_note')->disabled()->columnSpanFull(),
                Forms\Components\Textarea::make('admin_note')->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('asset.name')->label('Asset')->searchable(),
                Tables\Columns\TextColumn::make('fromHostel.name')->label('From')->searchable(),
                Tables\Columns\TextColumn::make('toHostel.name')->label('To')->searchable(),
                Tables\Columns\TextColumn::make('requester.name')->label('Requested By'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (AssetMovement $record): bool => in_array($record->status, ['pending_admin'], true))
                    ->form([
                        Forms\Components\Textarea::make('admin_note')
                            ->label('Admin Comment')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (AssetMovement $record, array $data): void {
                        $asset = $record->asset;
                        if (!$asset) {
                            return;
                        }

                        $asset->hostel_id = $record->to_hostel_id;
                        $asset->save();

                        $record->forceFill([
                            'status' => 'approved',
                            'admin_decided_by' => auth()->id(),
                            'admin_decided_at' => now(),
                            'admin_note' => $data['admin_note'],
                            'moved_at' => now(),
                        ])->save();

                        $record->requester?->notify(new SystemEventNotification(
                            event: 'asset_movement_admin_approved',
                            title: 'Asset Movement Approved',
                            message: sprintf('Admin approved movement for asset %s.', $record->asset?->name ?? 'N/A'),
                            payload: ['asset_movement_id' => $record->id]
                        ));

                        app(OutboundWebhookService::class)->dispatch('asset.movement_approved', [
                            'asset_movement_id' => $record->id,
                            'asset_id' => $record->asset_id,
                            'admin_id' => auth()->id(),
                            'status' => $record->status,
                            'to_hostel_id' => $record->to_hostel_id,
                        ]);

                        Notification::make()->success()->title('Movement approved and asset moved')->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (AssetMovement $record): bool => in_array($record->status, ['pending_admin'], true))
                    ->form([
                        Forms\Components\Textarea::make('admin_note')
                            ->label('Admin Comment')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (AssetMovement $record, array $data): void {
                        $record->forceFill([
                            'status' => 'rejected_by_admin',
                            'admin_decided_by' => auth()->id(),
                            'admin_decided_at' => now(),
                            'admin_note' => $data['admin_note'],
                        ])->save();

                        $record->requester?->notify(new SystemEventNotification(
                            event: 'asset_movement_admin_rejected',
                            title: 'Asset Movement Rejected',
                            message: sprintf('Admin rejected movement for asset %s.', $record->asset?->name ?? 'N/A'),
                            payload: ['asset_movement_id' => $record->id]
                        ));

                        app(OutboundWebhookService::class)->dispatch('asset.movement_rejected', [
                            'asset_movement_id' => $record->id,
                            'asset_id' => $record->asset_id,
                            'admin_id' => auth()->id(),
                            'status' => $record->status,
                        ]);

                        Notification::make()->success()->title('Movement rejected')->send();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetMovements::route('/'),
            'edit' => Pages\EditAssetMovement::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() && Addon::isActive('asset-management') && Schema::hasTable('asset_movements');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin();
    }
}
