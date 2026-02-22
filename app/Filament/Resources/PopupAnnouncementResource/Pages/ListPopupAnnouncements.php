<?php

namespace App\Filament\Resources\PopupAnnouncementResource\Pages;

use App\Filament\Resources\PopupAnnouncementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPopupAnnouncements extends ListRecords
{
    protected static string $resource = PopupAnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
