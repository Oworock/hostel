<?php

namespace App\Filament\Resources\AssetSubscriptionResource\Pages;

use App\Filament\Resources\AssetSubscriptionResource;
use App\Services\OutboundWebhookService;
use App\Filament\Resources\Pages\BaseCreateRecord as CreateRecord;

class CreateAssetSubscription extends CreateRecord
{
    protected static string $resource = AssetSubscriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        app(OutboundWebhookService::class)->dispatch('asset.subscription.created', [
            'asset_subscription_id' => $this->record->id,
            'hostel_id' => $this->record->hostel_id,
            'created_by' => auth()->id(),
            'status' => $this->record->status,
            'expires_at' => optional($this->record->expires_at)->toDateString(),
        ]);
    }
}
