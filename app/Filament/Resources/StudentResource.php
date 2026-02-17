<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StudentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'User Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Student Information')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_image')
                            ->label('Profile Picture')
                            ->image()
                            ->directory('profile-images')
                            ->disk('public')
                            ->visibility('public')
                            ->deletable()
                            ->maxSize(5120)
                            ->previewable(true)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Full Name'),
                        
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->label('Password'),
                        
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        
                        Forms\Components\TextInput::make('address')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('id_number')
                            ->maxLength(50)
                            ->label('ID Number'),

                        Forms\Components\Select::make('hostel_id')
                            ->relationship('hostel', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Hostel'),
                    ]),
                
                Forms\Components\Section::make('Guardian Information')
                    ->schema([
                        Forms\Components\TextInput::make('guardian_name')
                            ->maxLength(255)
                            ->label('Guardian Name'),
                        
                        Forms\Components\TextInput::make('guardian_phone')
                            ->tel()
                            ->maxLength(20)
                            ->label('Guardian Phone'),
                    ]),
                Forms\Components\Section::make('Additional Registration Data')
                    ->schema([
                        Forms\Components\KeyValue::make('extra_data')
                            ->label('Custom Registration Fields')
                            ->helperText('Stores values submitted from dynamic custom registration form fields.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')
                    ->label('Avatar')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->sortable()
                    ->label('ID'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('guardian_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('extra_data')
                    ->label('Additional Data')
                    ->formatStateUsing(fn ($state) => is_array($state) ? collect($state)->filter()->map(fn ($v, $k) => $k . ': ' . $v)->join(', ') : '')
                    ->limit(40),

                Tables\Columns\TextColumn::make('hostel.name')
                    ->label('Hostel')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('hostel_id')
                    ->relationship('hostel', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('impersonate')
                    ->icon('heroicon-o-arrow-right')
                    ->label('Login As')
                    ->visible(fn () => auth()->user()?->role === 'admin')
                    ->action(function (User $record) {
                        session(['impersonator_id' => auth()->id()]);
                        Auth::login($record);
                        return redirect()->route('dashboard');
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Students';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'student');
    }
}
