<?php

namespace App\Filament\Resources\AssetSubscriptionResource\Pages;

use App\Filament\Resources\AssetSubscriptionResource;
use App\Services\OutboundWebhookService;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord as EditRecord;

class EditAssetSubscription extends EditRecord
{
    protected static string $resource = AssetSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function (): void {
                    app(OutboundWebhookService::class)->dispatch('asset.subscription.deleted', [
                        'asset_subscription_id' => $this->record->id,
                        'deleted_by' => auth()->id(),
                    ]);
                }),
        ];
    }

    protected function afterSave(): void
    {
        app(OutboundWebhookService::class)->dispatch('asset.subscription.updated', [
            'asset_subscription_id' => $this->record->id,
            'hostel_id' => $this->record->hostel_id,
            'updated_by' => auth()->id(),
            'status' => $this->record->status,
            'expires_at' => optional($this->record->expires_at)->toDateString(),
        ]);
    }
}
