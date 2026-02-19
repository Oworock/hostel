<?php

namespace App\Filament\Resources\BedResource\Pages;

use App\Filament\Resources\BedResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseCreateRecord as CreateRecord;

class CreateBed extends CreateRecord
{
    protected static string $resource = BedResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['is_occupied'])) {
            $data['user_id'] = null;
            $data['occupied_from'] = null;
        } elseif (empty($data['occupied_from'])) {
            $data['occupied_from'] = now();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
