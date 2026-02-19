<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Booking;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseCreateRecord as CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['booking_id'])) {
            $booking = Booking::find($data['booking_id']);
            if (!$booking) {
                throw new \Exception('Invalid booking selected.');
            }

            if (!empty($data['user_id']) && (int) $data['user_id'] !== (int) $booking->user_id) {
                throw new \Exception('Selected booking does not belong to selected student.');
            }

            $data['user_id'] = $booking->user_id;
        }

        if (empty($data['user_id'])) {
            throw new \Exception('Please select a student and booking.');
        }

        if (auth()->user()?->isAdmin()) {
            $data['is_manual'] = true;
            $data['created_by_admin_id'] = auth()->id();
            if (($data['payment_method'] ?? null) === 'cash') {
                $data['payment_method'] = 'manual_admin';
            }
        }

        return $data;
    }

    protected function afterCreate(): void
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
