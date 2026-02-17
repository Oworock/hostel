<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Services\OutboundWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ComplaintController extends Controller
{
    public function index()
    {
        $manager = auth()->user();

        if (!Schema::hasTable('complaints') || !Schema::hasColumn('complaints', 'user_id')) {
            $complaints = Complaint::query()->whereRaw('1 = 0')->paginate(15);
        } else {
            $complaints = Complaint::whereHas('user', function ($query) use ($manager) {
                $query->whereIn('hostel_id', $manager->managedHostelIds());
            })
                ->with('user')
                ->latest()
                ->paginate(15);
        }

        return view('manager.complaints.index', compact('complaints'));
    }

    public function respond(Request $request, Complaint $complaint)
    {
        $this->authorize('update', $complaint);

        $validated = $request->validate([
            'response' => ['required', 'string', 'max:1000'],
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $complaint->update([
            'response' => $validated['response'],
            'status' => $validated['status'],
            'assigned_to' => auth()->id(),
        ]);
        app(OutboundWebhookService::class)->dispatch('complaint.responded', [
            'complaint_id' => $complaint->id,
            'student_id' => $complaint->user_id,
            'manager_id' => auth()->id(),
            'status' => $complaint->status,
        ]);

        return redirect()->route('manager.complaints.index')->with('success', 'Complaint response saved.');
    }
}
