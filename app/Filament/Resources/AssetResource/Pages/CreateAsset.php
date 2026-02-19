<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Filament\Resources\Pages\BaseCreateRecord as CreateRecord;
use App\Services\AssetNotificationService;
use App\Services\OutboundWebhookService;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $actor = auth()->user();

        app(AssetNotificationService::class)->notifyManagersAssetCreated($this->record, $actor);

        app(OutboundWebhookService::class)->dispatch('asset.created', [
            'asset_id' => $this->record->id,
            'hostel_id' => $this->record->hostel_id,
            'manager_id' => $actor?->id,
            'asset_name' => $this->record->name,
        ]);
    }
}
