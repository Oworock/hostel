<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\HostelChangeRequest;
use App\Services\HostelChangeNotificationService;
use Illuminate\Http\Request;

class HostelChangeRequestController extends Controller
{
    public function __construct(private readonly HostelChangeNotificationService $notifier)
    {
    }

    public function index()
    {
        $student = auth()->user();

        $availableHostels = Hostel::query()
            ->where('is_active', true)
            ->when($student->hostel_id, fn ($q) => $q->where('id', '!=', $student->hostel_id))
            ->orderBy('name')
            ->get();

        $requests = HostelChangeRequest::where('student_id', $student->id)
            ->with(['currentHostel', 'requestedHostel', 'managerApprover', 'adminApprover'])
            ->latest()
            ->paginate(10);

        return view('student.hostel-change.index', compact('student', 'availableHostels', 'requests'));
    }

    public function store(Request $request)
    {
        $student = auth()->user();

        $validated = $request->validate([
            'requested_hostel_id' => ['required', 'exists:hostels,id'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if ((int) $validated['requested_hostel_id'] === (int) $student->hostel_id) {
            return back()->with('error', 'You are already assigned to this hostel.');
        }

        $hasPending = HostelChangeRequest::where('student_id', $student->id)
            ->whereIn('status', ['pending_manager_approval', 'pending_admin_approval'])
            ->exists();

        if ($hasPending) {
            return back()->with('error', 'You already have a pending hostel change request.');
        }

        $requestModel = HostelChangeRequest::create([
            'student_id' => $student->id,
            'current_hostel_id' => $student->hostel_id,
            'requested_hostel_id' => $validated['requested_hostel_id'],
            'status' => 'pending_manager_approval',
            'reason' => $validated['reason'] ?? null,
        ]);

        $this->notifier->submitted($requestModel);

        return back()->with('success', 'Hostel change request submitted. It now requires new hostel manager approval, then admin approval.');
    }
}
