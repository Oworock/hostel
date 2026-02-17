<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class ManagerSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static string $view = 'filament.pages.manager-settings';

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user() && auth()->user()->role === 'manager';
    }

    public function mount(): void
    {
        abort_if(auth()->user()?->role !== 'manager', 403);
        
        $user = auth()->user();
        
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'profile_image' => $user->profile_image,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Personal Information')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Forms\Components\Section::make('Basic Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Full Name')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email Address')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Phone Number')
                                            ->tel(),
                                        
                                        Forms\Components\TextInput::make('address')
                                            ->label('Address'),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Profile Picture')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                Forms\Components\Section::make('Upload Profile Picture')
                                    ->schema([
                                        Forms\Components\FileUpload::make('profile_image')
                                            ->label('Profile Picture')
                                            ->avatar()
                                            ->directory('profile-images')
                                            ->image(),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Security')
                            ->icon('heroicon-m-lock-closed')
                            ->schema([
                                Forms\Components\Section::make('Change Password')
                                    ->description('Update your account password')
                                    ->schema([
                                        Forms\Components\TextInput::make('current_password')
                                            ->label('Current Password')
                                            ->password()
                                            ->revealable(),
                                        
                                        Forms\Components\TextInput::make('new_password')
                                            ->label('New Password')
                                            ->password()
                                            ->revealable()
                                            ->minLength(8)
                                            ->different('current_password'),
                                        
                                        Forms\Components\TextInput::make('new_password_confirmation')
                                            ->label('Confirm Password')
                                            ->password()
                                            ->revealable()
                                            ->same('new_password'),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'profile_image' => $data['profile_image'] ?? $user->profile_image,
        ]);

        if (!empty($data['current_password'])) {
            if (!\Illuminate\Support\Facades\Hash::check($data['current_password'], $user->password)) {
                Notification::make()
                    ->danger()
                    ->title('Invalid Password')
                    ->body('The current password is incorrect.')
                    ->send();
                return;
            }

            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($data['new_password'])]);
        }

        Notification::make()
            ->success()
            ->title('Settings Updated')
            ->body('Your settings have been updated successfully.')
            ->send();

        $this->redirect(static::getUrl());
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Changes')
                ->submit('save')
                ->color('primary'),
        ];
    }
}
