<?php

namespace App\Filament\Resources\PopupAnnouncementResource\Pages;

use App\Filament\Resources\PopupAnnouncementResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePopupAnnouncement extends CreateRecord
{
    protected static string $resource = PopupAnnouncementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}

