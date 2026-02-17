<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;

class SystemCustomization extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    
    protected static ?string $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 2;
    
    protected static string $view = 'filament.pages.system-customization';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'app_name' => SystemSetting::getSetting('app_name', 'Hostel Management'),
            'app_logo' => SystemSetting::getSetting('app_logo', ''),
            'app_description' => SystemSetting::getSetting('app_description', ''),
            'primary_color' => SystemSetting::getSetting('primary_color', '#3b82f6'),
            'secondary_color' => SystemSetting::getSetting('secondary_color', '#10b981'),
            'footer_text' => SystemSetting::getSetting('footer_text', ''),
            'max_beds_per_room' => SystemSetting::getSetting('max_beds_per_room', '4'),
            'max_students_per_hostel' => SystemSetting::getSetting('max_students_per_hostel', '100'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Branding')
                    ->schema([
                        Forms\Components\TextInput::make('app_name')
                            ->label('Application Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('app_logo')
                            ->label('Logo')
                            ->image()
                            ->directory('logos')
                            ->maxSize(5120),
                        Forms\Components\Textarea::make('app_description')
                            ->label('Application Description')
                            ->rows(3),
                    ]),
                
                Forms\Components\Section::make('Colors')
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('Primary Color'),
                        Forms\Components\ColorPicker::make('secondary_color')
                            ->label('Secondary Color'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Footer')
                    ->schema([
                        Forms\Components\Textarea::make('footer_text')
                            ->label('Footer Text')
                            ->rows(3),
                    ]),
                
                Forms\Components\Section::make('System Limits')
                    ->schema([
                        Forms\Components\TextInput::make('max_beds_per_room')
                            ->label('Maximum Beds Per Room')
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\TextInput::make('max_students_per_hostel')
                            ->label('Maximum Students Per Hostel')
                            ->numeric()
                            ->minValue(1),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        foreach ($data as $key => $value) {
            if ($value !== null) {
                SystemSetting::setSetting($key, $value);
            }
        }

        $this->dispatch('notify', title: 'Settings saved', message: 'System settings have been updated successfully');
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Legacy page kept for backward compatibility; replaced by System Settings + Website Content CRUD.
        return false;
    }
}
