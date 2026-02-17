<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function view(User $user, Complaint $complaint): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'student') {
            return $user->id === $complaint->user_id;
        }

        if ($user->role === 'manager') {
            return $complaint->user && $user->managedHostelIds()->contains($complaint->user->hostel_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    public function update(User $user, Complaint $complaint): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'manager') {
            return $complaint->user && $user->managedHostelIds()->contains($complaint->user->hostel_id);
        }

        return false;
    }

    public function delete(User $user, Complaint $complaint): bool
    {
        return $user->role === 'admin';
    }
}
