<?php

namespace App\Filament\Resources\AssetSubscriptionResource\Pages;

use App\Filament\Resources\AssetSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssetSubscriptions extends ListRecords
{
    protected static string $resource = AssetSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
