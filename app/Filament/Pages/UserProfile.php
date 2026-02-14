<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class UserProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $title = 'Profile';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.user-profile';
    
    protected static ?string $slug = 'profile';

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'role' => $user->role,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Profile')
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
                                        
                                        Forms\Components\TextInput::make('role')
                                            ->label('User Type')
                                            ->disabled()
                                            ->formatStateUsing(fn () => ucfirst(auth()->user()->role)),
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
        ]);

        if (!empty($data['current_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                Notification::make()
                    ->danger()
                    ->title('Invalid Password')
                    ->body('The current password is incorrect.')
                    ->send();
                return;
            }

            $user->update(['password' => Hash::make($data['new_password'])]);
        }

        Notification::make()
            ->success()
            ->title('Profile Updated')
            ->body('Your profile has been updated successfully.')
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
