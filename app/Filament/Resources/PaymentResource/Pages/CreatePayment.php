<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure user_id is populated from the booking
        if (!empty($data['booking_id'])) {
            $booking = Booking::with('user')->find($data['booking_id']);
            if ($booking && $booking->user) {
                $data['user_id'] = $booking->user->id;
            }
        }
        
        // If user_id is still not set, it will fail validation
        if (empty($data['user_id'])) {
            throw new \Exception('Unable to determine user for this payment. Please select a valid booking.');
        }
        
        return $data;
    }
}
