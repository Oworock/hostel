<?php

namespace App\Filament\Resources\RoomChangeRequestResource\Pages;

use App\Filament\Resources\RoomChangeRequestResource;
use App\Filament\Resources\Pages\BaseEditRecord as EditRecord;

class EditRoomChangeRequest extends EditRecord
{
    protected static string $resource = RoomChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

