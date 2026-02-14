<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function view(User $user, Room $room): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $room->hostel_id === $user->hostel_id;
        }

        return true;
    }

    public function update(User $user, Room $room): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $room->hostel_id === $user->hostel_id;
        }

        return false;
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->update($user, $room);
    }
}
