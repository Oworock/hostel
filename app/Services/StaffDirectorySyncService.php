<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class StaffDirectorySyncService
{
    public function syncCoreUsers(): void
    {
        if (!Addon::isActive('staff-payroll') || !Schema::hasTable('staff_members')) {
            return;
        }

        $hasUserId = Schema::hasColumn('staff_members', 'user_id');
        $hasSourceRole = Schema::hasColumn('staff_members', 'source_role');
        $hasRegisteredViaLink = Schema::hasColumn('staff_members', 'registered_via_link');
        $hasIsGeneralStaff = Schema::hasColumn('staff_members', 'is_general_staff');
        $hasAssignedHostelId = Schema::hasColumn('staff_members', 'assigned_hostel_id');
        if (!$hasUserId && !$hasSourceRole) {
            return;
        }

        User::query()
            ->whereIn('role', ['admin', 'super_admin', 'manager'])
            ->get()
            ->each(function (User $user) use ($hasUserId, $hasSourceRole, $hasRegisteredViaLink, $hasIsGeneralStaff, $hasAssignedHostelId): void {
                $payload = [
                    'full_name' => (string) ($user->name ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))),
                    'email' => (string) $user->email,
                    'phone' => (string) ($user->phone ?? ''),
                    'department' => 'Management',
                    'job_title' => ucfirst((string) $user->role),
                    'status' => $user->is_active ? 'active' : 'inactive',
                ];

                if ($hasSourceRole) {
                    $payload['source_role'] = (string) $user->role;
                }
                if ($hasRegisteredViaLink) {
                    $payload['registered_via_link'] = false;
                }
                if ($hasIsGeneralStaff) {
                    $payload['is_general_staff'] = $user->role === 'manager' ? false : true;
                }
                if ($hasAssignedHostelId) {
                    $payload['assigned_hostel_id'] = $user->role === 'manager' ? ($user->hostel_id ?: null) : null;
                }

                if ($hasUserId) {
                    StaffMember::updateOrCreate(['user_id' => $user->id], $payload);

                    return;
                }

                StaffMember::updateOrCreate(['email' => $user->email], $payload);
            });
    }
}
