<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetIssueResource\Pages;
use App\Models\Addon;
use App\Models\AssetIssue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class AssetIssueResource extends Resource
{
    protected static ?string $model = AssetIssue::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Addons';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Asset Issues';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Addon::isActive('asset-management') && Schema::hasTable('asset_issues');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('asset_id')
                    ->relationship('asset', 'name')
                    ->disabled(),
                Forms\Components\Select::make('hostel_id')
                    ->relationship('hostel', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('title')
                    ->disabled(),
                Forms\Components\Textarea::make('description')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),
                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('resolution_note')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('asset.name')->label('Asset')->searchable(),
                Tables\Columns\TextColumn::make('hostel.name')->label('Hostel')->searchable(),
                Tables\Columns\TextColumn::make('reporter.name')->label('Reported By'),
                Tables\Columns\TextColumn::make('priority')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetIssues::route('/'),
            'edit' => Pages\EditAssetIssue::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() && Addon::isActive('asset-management') && Schema::hasTable('asset_issues');
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
