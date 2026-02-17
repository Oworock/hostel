<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        if ($payment->isDirty('status') && $payment->isPaid()) {
            $booking = $payment->booking;
            if ($booking && $booking->isPending()) {
                $booking->update(['status' => 'approved']);
                
                if ($booking->bed) {
                    $booking->bed->update([
                        'is_occupied' => true,
                        'user_id' => $booking->user_id,
                        'occupied_from' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }
}
