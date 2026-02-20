<?php

namespace App\Observers;

use App\Models\StaffMember;
use App\Services\StaffIdCardService;
use App\Services\StaffPayrollNotificationService;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StaffMemberObserver
{
    public function created(StaffMember $staffMember): void
    {
        if (!Schema::hasColumn('staff_members', 'id_card_path')) {
            return;
        }

        if (empty($staffMember->id_card_path)) {
            try {
                $path = app(StaffIdCardService::class)->generate($staffMember);
                $staffMember->forceFill(['id_card_path' => $path])->saveQuietly();
            } catch (Throwable $e) {
                // Avoid blocking staff creation when ID card generation fails.
            }
        }
    }

    public function updated(StaffMember $staffMember): void
    {
        if (Schema::hasColumn('staff_members', 'id_card_path')
            && $staffMember->wasChanged(['full_name', 'email', 'phone', 'department', 'job_title', 'employee_code', 'profile_image', 'joined_on'])) {
            try {
                $path = app(StaffIdCardService::class)->generate($staffMember);
                $staffMember->forceFill(['id_card_path' => $path])->saveQuietly();
            } catch (Throwable $e) {
                // Avoid blocking staff updates when ID card generation fails.
            }
        }

        if ($staffMember->wasChanged('status')) {
            app(StaffPayrollNotificationService::class)->notifyStatusChanged($staffMember, (string) $staffMember->status);
        }
    }
}
