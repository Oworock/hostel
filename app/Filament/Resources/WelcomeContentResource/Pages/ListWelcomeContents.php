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
            Actions\CreateAction::make(),
        ];
    }
}
