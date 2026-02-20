<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\StaffMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StaffDirectoryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Addon::isActive('staff-payroll'), 404);
        abort_unless(Schema::hasTable('staff_members'), 404);

        $manager = auth()->user();
        $managedHostelIds = $manager->managedHostelIds();
        $search = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', 'all'));
        $scope = trim((string) $request->query('scope', 'all'));

        $query = StaffMember::query()->with(['assignedHostel']);
        if (Schema::hasColumn('staff_members', 'is_general_staff') && Schema::hasColumn('staff_members', 'assigned_hostel_id')) {
            $query->where(function ($q) use ($managedHostelIds): void {
                $q->where('is_general_staff', true)
                    ->orWhereIn('assigned_hostel_id', $managedHostelIds);
            });
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }
        if (in_array($status, ['active', 'pending', 'suspended', 'inactive', 'sacked'], true)) {
            $query->where('status', $status);
        }
        if (in_array($scope, ['general', 'assigned'], true) && Schema::hasColumn('staff_members', 'is_general_staff')) {
            $query->where('is_general_staff', $scope === 'general');
        }

        $staff = $query
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        $summaryBase = StaffMember::query();
        if (Schema::hasColumn('staff_members', 'is_general_staff') && Schema::hasColumn('staff_members', 'assigned_hostel_id')) {
            $summaryBase->where(function ($q) use ($managedHostelIds): void {
                $q->where('is_general_staff', true)
                    ->orWhereIn('assigned_hostel_id', $managedHostelIds);
            });
        }
        $summary = [
            'all' => (clone $summaryBase)->count(),
            'active' => (clone $summaryBase)->where('status', 'active')->count(),
            'pending' => (clone $summaryBase)->where('status', 'pending')->count(),
            'general' => Schema::hasColumn('staff_members', 'is_general_staff')
                ? (clone $summaryBase)->where('is_general_staff', true)->count()
                : 0,
        ];

        return view('manager.staff.index', compact('staff', 'summary', 'search', 'status', 'scope'));
    }
}
