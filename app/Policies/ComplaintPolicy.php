<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function view(User $user, Complaint $complaint): bool
    {
        return $user->id === $complaint->user_id || $user->role === 'admin' || ($user->role === 'manager' && $user->id === $complaint->assigned_to);
    }

    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    public function update(User $user, Complaint $complaint): bool
    {
        return $user->role === 'admin' || ($user->role === 'manager' && $user->id === $complaint->assigned_to);
    }

    public function delete(User $user, Complaint $complaint): bool
    {
        return $user->role === 'admin';
    }
}
