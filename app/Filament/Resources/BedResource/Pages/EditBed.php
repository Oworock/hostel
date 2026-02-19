<?php

namespace App\Filament\Resources\BedResource\Pages;

use App\Filament\Resources\BedResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord as EditRecord;

class EditBed extends EditRecord
{
    protected static string $resource = BedResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['is_occupied'])) {
            $data['user_id'] = null;
            $data['occupied_from'] = null;
        } elseif (empty($data['occupied_from'])) {
            $data['occupied_from'] = now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
