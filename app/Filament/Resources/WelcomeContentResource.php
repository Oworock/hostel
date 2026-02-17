<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WelcomeContentResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WelcomeContentResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'Website Content';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('key', array_keys(static::keyDefinitions()));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Content Definition')
                    ->schema([
                        Forms\Components\Select::make('key')
                            ->label('Content Key')
                            ->options(static::keyOptions())
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->unique(SystemSetting::class, 'key', ignoreRecord: true)
                            ->helperText('Global header/footer keys apply across the system. Welcome body keys only affect the landing page.'),
                    ]),
                Forms\Components\Section::make('Value Editor')
                    ->schema([
                        Forms\Components\TextInput::make('value_text')
                            ->label('Text Value')
                            ->visible(fn (Forms\Get $get): bool => static::inputForKey($get('key')) === 'text'),
                        Forms\Components\RichEditor::make('value_html')
                            ->label('HTML Content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                                'link',
                                'h2',
                                'h3',
                            ])
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsVisibility('public')
                            ->fileAttachmentsDirectory('website-content/attachments')
                            ->visible(fn (Forms\Get $get): bool => static::inputForKey($get('key')) === 'html')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('value_logo')
                            ->label('Logo Image')
                            ->image()
                            ->directory('branding')
                            ->disk('public')
                            ->visibility('public')
                            ->deletable()
                            ->downloadable()
                            ->openable()
                            ->visible(fn (Forms\Get $get): bool => static::inputForKey($get('key')) === 'logo')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('value_email')
                            ->label('Email Address')
                            ->email()
                            ->visible(fn (Forms\Get $get): bool => static::inputForKey($get('key')) === 'email'),
                        Forms\Components\TextInput::make('value_phone')
                            ->label('Phone Number')
                            ->tel()
                            ->visible(fn (Forms\Get $get): bool => static::inputForKey($get('key')) === 'phone'),
                        Forms\Components\Hidden::make('value'),
                        Forms\Components\Hidden::make('type'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Field')
                    ->formatStateUsing(fn (string $state): string => static::keyDefinitions()[$state]['label'] ?? $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('scope')
                    ->label('Scope')
                    ->state(fn (SystemSetting $record): string => static::scopeForKey($record->key))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Global' ? 'info' : 'success'),
                Tables\Columns\TextColumn::make('format')
                    ->label('Format')
                    ->state(fn (SystemSetting $record): string => strtoupper(static::inputForKey($record->key)))
                    ->badge(),
                Tables\Columns\TextColumn::make('value')
                    ->limit(70)
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWelcomeContents::route('/'),
            'create' => Pages\CreateWelcomeContent::route('/create'),
            'edit' => Pages\EditWelcomeContent::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
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

    /**
     * @return array<string, array{label:string, input:string, type:string}>
     */
    public static function keyDefinitions(): array
    {
        return [
            // Global header (site-wide)
            'global_header_logo' => ['label' => 'Global Header: Logo', 'input' => 'logo', 'type' => 'string'],
            'global_header_favicon' => ['label' => 'Global Header: Favicon', 'input' => 'logo', 'type' => 'string'],
            'global_header_brand' => ['label' => 'Global Header: Brand Name', 'input' => 'text', 'type' => 'string'],
            'global_header_notice_html' => ['label' => 'Global Header: Notice HTML', 'input' => 'html', 'type' => 'text'],
            'global_header_contact_email' => ['label' => 'Global Header: Contact Email', 'input' => 'email', 'type' => 'string'],
            'global_header_contact_phone' => ['label' => 'Global Header: Contact Phone', 'input' => 'phone', 'type' => 'string'],
            'global_header_hero_title' => ['label' => 'Global Header: Welcome Hero Title', 'input' => 'text', 'type' => 'string'],
            'global_header_hero_subtitle' => ['label' => 'Global Header: Welcome Hero Subtitle', 'input' => 'text', 'type' => 'text'],
            'global_header_primary_button_text' => ['label' => 'Global Header: Guest Primary Button Text', 'input' => 'text', 'type' => 'string'],
            'global_header_primary_button_url' => ['label' => 'Global Header: Guest Primary Button URL', 'input' => 'text', 'type' => 'string'],
            'global_header_secondary_button_text' => ['label' => 'Global Header: Guest Secondary Button Text', 'input' => 'text', 'type' => 'string'],
            'global_header_secondary_button_url' => ['label' => 'Global Header: Guest Secondary Button URL', 'input' => 'text', 'type' => 'string'],
            'global_header_authenticated_cta_text' => ['label' => 'Global Header: Authenticated CTA Text', 'input' => 'text', 'type' => 'string'],

            // Welcome body (welcome page only)
            'welcome_body_student_title' => ['label' => 'Welcome Body: Student Card Title', 'input' => 'text', 'type' => 'string'],
            'welcome_body_student_description' => ['label' => 'Welcome Body: Student Card Description', 'input' => 'html', 'type' => 'text'],
            'welcome_body_manager_title' => ['label' => 'Welcome Body: Manager Card Title', 'input' => 'text', 'type' => 'string'],
            'welcome_body_manager_description' => ['label' => 'Welcome Body: Manager Card Description', 'input' => 'html', 'type' => 'text'],
            'welcome_body_admin_title' => ['label' => 'Welcome Body: Admin Card Title', 'input' => 'text', 'type' => 'string'],
            'welcome_body_admin_description' => ['label' => 'Welcome Body: Admin Card Description', 'input' => 'html', 'type' => 'text'],

            // Global footer (site-wide)
            'global_footer_title' => ['label' => 'Global Footer: Title', 'input' => 'text', 'type' => 'string'],
            'global_footer_description_html' => ['label' => 'Global Footer: Description HTML', 'input' => 'html', 'type' => 'text'],
            'global_footer_contact_email' => ['label' => 'Global Footer: Contact Email', 'input' => 'email', 'type' => 'string'],
            'global_footer_contact_phone' => ['label' => 'Global Footer: Contact Phone', 'input' => 'phone', 'type' => 'string'],
            'global_footer_copyright_html' => ['label' => 'Global Footer: Copyright HTML', 'input' => 'html', 'type' => 'text'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function keyOptions(): array
    {
        $options = [];
        foreach (static::keyDefinitions() as $key => $definition) {
            $options[$key] = $definition['label'];
        }

        return $options;
    }

    public static function inputForKey(?string $key): string
    {
        if (!$key || !isset(static::keyDefinitions()[$key])) {
            return 'text';
        }

        return static::keyDefinitions()[$key]['input'];
    }

    public static function typeForKey(?string $key): string
    {
        if (!$key || !isset(static::keyDefinitions()[$key])) {
            return 'string';
        }

        return static::keyDefinitions()[$key]['type'];
    }

    public static function scopeForKey(string $key): string
    {
        return str_starts_with($key, 'welcome_body_') ? 'Welcome Page Only' : 'Global';
    }

    public static function valueFromFormData(array $data, ?string $forcedKey = null): ?string
    {
        $key = $forcedKey ?? ($data['key'] ?? null);

        return match (static::inputForKey($key)) {
            'html' => $data['value_html'] ?? null,
            'logo' => $data['value_logo'] ?? null,
            'email' => $data['value_email'] ?? null,
            'phone' => $data['value_phone'] ?? null,
            default => $data['value_text'] ?? null,
        };
    }
}
