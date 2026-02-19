<?php

namespace App\Filament\Resources\AssetMovementResource\Pages;

use App\Filament\Resources\AssetMovementResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord as EditRecord;

class EditAssetMovement extends EditRecord
{
    protected static string $resource = AssetMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
