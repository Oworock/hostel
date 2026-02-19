<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddonResource\Pages;
use App\Models\Addon;
use App\Services\AddonDiscoveryService;
use App\Services\AddonLifecycleService;
use App\Services\AddonVisibilityService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AddonResource extends Resource
{
    protected static ?string $model = Addon::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        app(AddonDiscoveryService::class)->discover();

        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Package Upload')
                    ->schema([
                        Forms\Components\FileUpload::make('package_file')
                            ->label('Addon ZIP Package')
                            ->disk('local')
                            ->directory('addon-packages/uploads')
                            ->acceptedFileTypes([
                                'application/zip',
                                'application/x-zip-compressed',
                            ])
                            ->maxSize(25600)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (string $operation): bool => $operation === 'create')
                            ->helperText('Upload zip with addon.json at root. Manual folder: storage/app/private/addons/manual/<addon-slug>'),
                    ])
                    ->visible(fn (string $operation): bool => $operation === 'create'),
                Forms\Components\Section::make('Addon Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(fn (string $operation): bool => $operation === 'edit'),
                        Forms\Components\TextInput::make('slug')
                            ->disabled(),
                        Forms\Components\TextInput::make('version')
                            ->required(fn (string $operation): bool => $operation === 'edit'),
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Enabled')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('version')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('installed_at')
                    ->since()
                    ->label('Installed'),
            ])
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Addon $record): bool => !$record->is_active)
                    ->action(function (Addon $record, $livewire): void {
                        try {
                            app(AddonLifecycleService::class)->activate($record);
                            Notification::make()->success()->title('Addon activated')->send();
                            $livewire->redirect(self::getUrl('index'));
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title('Activation failed')->body($e->getMessage())->send();
                        }
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Addon $record): bool => $record->is_active)
                    ->action(function (Addon $record, $livewire): void {
                        try {
                            app(AddonLifecycleService::class)->deactivate($record);
                            Notification::make()->success()->title('Addon deactivated')->send();
                            $livewire->redirect(self::getUrl('index'));
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title('Deactivation failed')->body($e->getMessage())->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make('delete_with_data_warning')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Addon')
                    ->modalDescription('This will permanently remove the addon package and all addon data. For Asset Management addon, all assets, issues, movements, subscriptions, and uploaded asset images will be deleted.')
                    ->action(function (Addon $record): void {
                        try {
                            app(AddonLifecycleService::class)->purgeAddonData($record);
                            $slug = $record->slug;
                            $record->delete();
                            app(AddonVisibilityService::class)->ignore($slug);
                            Notification::make()->success()->title('Addon deleted')->send();
                        } catch (\Throwable $e) {
                            Notification::make()->danger()->title('Delete failed')->body($e->getMessage())->send();
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddons::route('/'),
            'create' => Pages\CreateAddon::route('/create'),
            'edit' => Pages\EditAddon::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->role === 'admin';
    }
}
