<?php

namespace App\Filament\Widgets;

use App\Models\Addon;
use App\Models\StaffMember;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema;

class StaffApprovalQueueWidget extends Widget
{
    protected static string $view = 'filament.widgets.staff-approval-queue-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '30s';
    public string $search = '';
    public string $scope = 'all';

    public static function canView(): bool
    {
        return auth()->check()
            && auth()->user()?->isAdmin()
            && Addon::isActive('staff-payroll')
            && Schema::hasTable('staff_members');
    }

    public function getPendingStaffProperty()
    {
        $query = StaffMember::query()
            ->where('status', 'pending')
            ->latest();

        if ($this->search !== '') {
            $search = trim($this->search);
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        if (in_array($this->scope, ['general', 'assigned'], true) && Schema::hasColumn('staff_members', 'is_general_staff')) {
            $query->where('is_general_staff', $this->scope === 'general');
        }

        return $query->limit(15)->get();
    }

    public function approve(int $staffId): void
    {
        $staff = StaffMember::query()->find($staffId);
        if (!$staff) {
            return;
        }

        $update = ['status' => 'active'];
        if (Schema::hasColumn('staff_members', 'employee_code') && empty($staff->employee_code)) {
            $update['employee_code'] = $this->nextFourDigitCode();
        }
        if (Schema::hasColumn('staff_members', 'approved_by')) {
            $update['approved_by'] = auth()->id();
        }
        if (Schema::hasColumn('staff_members', 'approved_at')) {
            $update['approved_at'] = now();
        }

        $staff->update($update);

        Notification::make()->success()->title(__('Staff approved'))->send();
    }

    public function reject(int $staffId): void
    {
        $staff = StaffMember::query()->find($staffId);
        if (!$staff) {
            return;
        }

        $staff->update(['status' => 'inactive']);

        Notification::make()->warning()->title(__('Staff rejected'))->send();
    }

    private function nextFourDigitCode(): string
    {
        for ($i = 0; $i < 50; $i++) {
            $candidate = (string) random_int(1000, 9999);
            if (!StaffMember::query()->where('employee_code', $candidate)->exists()) {
                return $candidate;
            }
        }

        return (string) random_int(1000, 9999);
    }
}
