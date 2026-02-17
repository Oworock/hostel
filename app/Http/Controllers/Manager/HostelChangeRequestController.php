<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
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
        $manager = auth()->user();
        $hostelIds = $manager->managedHostelIds();

        $requests = HostelChangeRequest::whereIn('requested_hostel_id', $hostelIds)
            ->with(['student', 'currentHostel', 'requestedHostel'])
            ->latest()
            ->paginate(12);

        return view('manager.hostel-change.index', compact('requests'));
    }

    public function approve(Request $request, HostelChangeRequest $hostelChangeRequest)
    {
        $manager = auth()->user();
        $hostelIds = $manager->managedHostelIds();

        abort_unless($hostelIds->contains($hostelChangeRequest->requested_hostel_id), 403);

        if ($hostelChangeRequest->status !== 'pending_manager_approval') {
            return back()->with('error', 'Only pending manager approvals can be approved.');
        }

        $hostelChangeRequest->update([
            'status' => 'pending_admin_approval',
            'manager_approved_by' => $manager->id,
            'manager_approved_at' => now(),
            'manager_note' => $request->input('manager_note'),
        ]);
        $this->notifier->managerApproved($hostelChangeRequest->fresh(['student', 'requestedHostel', 'currentHostel']), $manager);

        return back()->with('success', 'Request approved and forwarded to admin for final approval.');
    }

    public function reject(Request $request, HostelChangeRequest $hostelChangeRequest)
    {
        $manager = auth()->user();
        $hostelIds = $manager->managedHostelIds();

        abort_unless($hostelIds->contains($hostelChangeRequest->requested_hostel_id), 403);

        if (!in_array($hostelChangeRequest->status, ['pending_manager_approval', 'pending_admin_approval'], true)) {
            return back()->with('error', 'This request can no longer be rejected.');
        }

        $hostelChangeRequest->update([
            'status' => 'rejected',
            'manager_approved_by' => $manager->id,
            'manager_approved_at' => now(),
            'manager_note' => $request->input('manager_note'),
        ]);
        $this->notifier->managerRejected($hostelChangeRequest->fresh(['student', 'requestedHostel', 'currentHostel']), $manager);

        return back()->with('success', 'Hostel change request rejected.');
    }
}
