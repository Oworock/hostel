<?php

namespace App\Filament\Resources\BedResource\Pages;

use App\Filament\Resources\BedResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBed extends CreateRecord
{
    protected static string $resource = BedResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
