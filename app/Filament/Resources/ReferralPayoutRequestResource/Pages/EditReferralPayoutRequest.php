<?php

namespace App\Filament\Resources\ReferralPayoutRequestResource\Pages;

use App\Filament\Resources\ReferralPayoutRequestResource;
use App\Models\ReferralPayoutRequest;
use App\Services\ReferralNotificationService;
use Filament\Resources\Pages\EditRecord;

class EditReferralPayoutRequest extends EditRecord
{
    protected static string $resource = ReferralPayoutRequestResource::class;
    private ?string $oldStatus = null;
    private ?string $newStatus = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var ReferralPayoutRequest $record */
        $record = $this->record;
        $newStatus = (string) ($data['status'] ?? $record->status);
        $oldStatus = (string) $record->status;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;

        if ($newStatus === 'approved' && $oldStatus === 'pending' && empty($data['approved_at'])) {
            $data['approved_at'] = now();
        }

        if ($newStatus === 'paid' && $oldStatus !== 'paid' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
            $agent = $record->agent;
            if ($agent) {
                $amount = (float) $record->amount;
                $agent->total_paid = round(((float) $agent->total_paid) + $amount, 2);
                $agent->balance = round(max(0, ((float) $agent->balance) - $amount), 2);
                $agent->save();
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->newStatus === null || $this->oldStatus === $this->newStatus) {
            return;
        }

        /** @var ReferralPayoutRequest $record */
        $record = $this->record;
        app(ReferralNotificationService::class)->notifyPayoutStatus($record->fresh(['agent']));
    }
}
