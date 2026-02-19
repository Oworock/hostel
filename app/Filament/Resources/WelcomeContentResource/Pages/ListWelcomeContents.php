<?php

namespace App\Filament\Resources\WelcomeContentResource\Pages;

use App\Filament\Resources\WelcomeContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWelcomeContents extends ListRecords
{
    protected static string $resource = WelcomeContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manage_sections')
                ->label('Manage Welcome Sections')
                ->icon('heroicon-o-rectangle-stack')
                ->url(route('filament.admin.resources.welcome-sections.index')),
            Actions\CreateAction::make(),
        ];
    }
}
