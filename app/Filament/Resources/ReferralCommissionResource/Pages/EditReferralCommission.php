<?php

namespace App\Filament\Resources\ReferralCommissionResource\Pages;

use App\Filament\Resources\ReferralCommissionResource;
use App\Models\ReferralCommission;
use Filament\Resources\Pages\EditRecord;

class EditReferralCommission extends EditRecord
{
    protected static string $resource = ReferralCommissionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var ReferralCommission $record */
        $record = $this->record;
        $newStatus = (string) ($data['status'] ?? $record->status);
        $oldStatus = (string) $record->status;

        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            $data['paid_at'] = $data['paid_at'] ?? now();

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
}
