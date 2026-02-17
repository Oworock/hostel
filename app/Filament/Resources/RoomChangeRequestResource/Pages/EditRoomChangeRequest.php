<?php

namespace App\Filament\Resources\RoomChangeRequestResource\Pages;

use App\Filament\Resources\RoomChangeRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditRoomChangeRequest extends EditRecord
{
    protected static string $resource = RoomChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

