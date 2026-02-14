<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $booking->room->hostel_id === $user->hostel_id;
        }

        if ($user->isStudent()) {
            return $booking->user_id === $user->id;
        }

        return false;
    }

    public function update(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $booking->room->hostel_id === $user->hostel_id;
        }

        return false;
    }
}
