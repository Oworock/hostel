<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopupAnnouncementResource\Pages;
use App\Models\PopupAnnouncement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PopupAnnouncementResource extends Resource
{
    protected static ?string $model = PopupAnnouncement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('body')
                    ->required()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('popup-announcements')
                    ->columnSpanFull(),
                Forms\Components\Select::make('target')
                    ->options([
                        'students' => 'Students only',
                        'managers' => 'Managers only',
                        'both' => 'Students and Managers',
                    ])
                    ->required()
                    ->default('both'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\DateTimePicker::make('start_at'),
                Forms\Components\DateTimePicker::make('end_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('target')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('start_at')->dateTime()->placeholder('-'),
                Tables\Columns\TextColumn::make('end_at')->dateTime()->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')->since(),
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
            'index' => Pages\ListPopupAnnouncements::route('/'),
            'create' => Pages\CreatePopupAnnouncement::route('/create'),
            'edit' => Pages\EditPopupAnnouncement::route('/{record}/edit'),
        ];
    }
}
