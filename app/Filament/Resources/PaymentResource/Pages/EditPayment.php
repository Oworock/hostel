<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['booking_id'])) {
            $booking = Booking::find($data['booking_id']);
            if ($booking) {
                if (!empty($data['user_id']) && (int) $data['user_id'] !== (int) $booking->user_id) {
                    throw new \Exception('Selected booking does not belong to selected student.');
                }
                $data['user_id'] = $booking->user_id;
            }
        }

        if (auth()->user()?->isAdmin() && ($data['payment_method'] ?? null) === 'manual_admin') {
            $data['is_manual'] = true;
            $data['created_by_admin_id'] = $data['created_by_admin_id'] ?? auth()->id();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $booking = $this->record?->booking;
        if (!$booking) {
            return;
        }

        if ($booking->isFullyPaid() && $booking->status === 'pending') {
            $booking->update(['status' => 'approved']);
        }
    }
}
