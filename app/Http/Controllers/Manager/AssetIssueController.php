<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetIssue;
use App\Models\AssetMovement;
use App\Models\Hostel;
use App\Models\User;
use App\Notifications\SystemEventNotification;
use App\Services\AssetNotificationService;
use App\Services\OutboundWebhookService;
use Illuminate\Http\Request;

class AssetIssueController extends Controller
{
    public function createAsset()
    {
        return view('manager.assets.create');
    }

    public function index()
    {
        $user = auth()->user();
        $hostelIds = $user->managedHostelIds();

        $assets = Asset::query()
            ->whereIn('hostel_id', $hostelIds)
            ->with('hostel')
            ->withCount([
                'issues as open_issues_count' => fn ($q) => $q->whereIn('status', ['open', 'in_progress']),
            ])
            ->orderBy('name')
            ->get();

        $availableHostels = Hostel::query()
            ->whereNotIn('id', $hostelIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $recentIssues = AssetIssue::query()
            ->whereIn('hostel_id', $hostelIds)
            ->with(['asset', 'hostel'])
            ->latest()
            ->limit(20)
            ->get();

        $incomingMovements = AssetMovement::query()
            ->where('receiving_manager_id', $user->id)
            ->where('status', 'pending_receiving_manager')
            ->with(['asset', 'fromHostel', 'toHostel', 'requester'])
            ->latest()
            ->get();

        $pendingAdminMovements = AssetMovement::query()
            ->whereIn('from_hostel_id', $hostelIds)
            ->whereIn('status', ['pending_receiving_manager', 'pending_admin'])
            ->with(['asset', 'toHostel', 'requester'])
            ->latest()
            ->limit(20)
            ->get();

        return view('manager.assets.index', compact('assets', 'recentIssues', 'availableHostels', 'incomingMovements', 'pendingAdminMovements'));
    }

    public function storeAsset(Request $request)
    {
        $user = auth()->user();
        $hostelIds = $user->managedHostelIds();

        $data = $request->validate([
            'hostel_id' => ['required', 'integer', 'exists:hostels,id'],
            'name' => ['required', 'string', 'max:255'],
            'asset_number' => ['nullable', 'string', 'max:255'],
            'asset_code' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'invoice_reference' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'condition' => ['required', 'in:excellent,good,fair,poor'],
            'acquisition_cost' => ['nullable', 'numeric', 'min:0'],
            'maintenance_schedule' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'warranty_expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        if (!$hostelIds->contains((int) $data['hostel_id'])) {
            abort(403, 'You can only add assets to hostels you manage.');
        }

        $payload = $data;
        unset($payload['image']);

        if ($request->hasFile('image')) {
            $payload['image_path'] = $request->file('image')->store('assets', 'public');
        }

        $payload['created_by'] = $user->id;
        $payload['status'] = 'active';

        $asset = Asset::create($payload);

        app(AssetNotificationService::class)->notifyManagersAssetCreated($asset, $user);

        app(OutboundWebhookService::class)->dispatch('asset.created', [
            'asset_id' => $asset->id,
            'hostel_id' => $asset->hostel_id,
            'manager_id' => $user->id,
            'asset_name' => $asset->name,
        ]);

        return redirect()->route('manager.assets.index')->with('success', 'Asset added successfully.');
    }

    public function requestMovement(Request $request, Asset $asset)
    {
        $user = auth()->user();
        $hostelIds = $user->managedHostelIds();

        if (!$hostelIds->contains($asset->hostel_id)) {
            abort(403, 'Unauthorized asset movement request.');
        }

        $data = $request->validate([
            'to_hostel_id' => ['required', 'integer', 'exists:hostels,id'],
            'request_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ((int) $data['to_hostel_id'] === (int) $asset->hostel_id) {
            return back()->withErrors(['to_hostel_id' => 'Destination hostel must be different from current hostel.']);
        }

        $receivingManagerId = User::query()
            ->where('role', 'manager')
            ->whereHas('managedHostels', fn ($q) => $q->where('hostels.id', $data['to_hostel_id']))
            ->orderBy('id')
            ->value('id');

        $movement = AssetMovement::create([
            'asset_id' => $asset->id,
            'from_hostel_id' => $asset->hostel_id,
            'to_hostel_id' => $data['to_hostel_id'],
            'requested_by' => $user->id,
            'receiving_manager_id' => $receivingManagerId,
            'request_note' => $data['request_note'] ?? null,
            'status' => $receivingManagerId ? 'pending_receiving_manager' : 'pending_admin',
        ]);

        app(OutboundWebhookService::class)->dispatch('asset.movement_requested', [
            'asset_movement_id' => $movement->id,
            'asset_id' => $asset->id,
            'from_hostel_id' => $asset->hostel_id,
            'to_hostel_id' => (int) $data['to_hostel_id'],
            'manager_id' => $user->id,
            'status' => $movement->status,
        ]);

        $admins = User::query()->whereIn('role', ['admin', 'super_admin'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new SystemEventNotification(
                event: 'asset_movement_requested',
                title: 'Asset Movement Requested',
                message: sprintf(
                    '%s requested movement of asset %s to another hostel.',
                    $user->name,
                    $asset->name
                ),
                payload: [
                    'asset_id' => $asset->id,
                    'from_hostel_id' => $asset->hostel_id,
                    'to_hostel_id' => (int) $data['to_hostel_id'],
                ],
            ));
        }

        if ($receivingManagerId) {
            $receivingManager = User::find($receivingManagerId);
            if ($receivingManager) {
                $receivingManager->notify(new SystemEventNotification(
                    event: 'asset_movement_incoming',
                    title: 'Incoming Asset Movement',
                    message: sprintf('Please review movement request for asset %s.', $asset->name),
                    payload: [
                        'asset_id' => $asset->id,
                        'from_hostel_id' => $asset->hostel_id,
                        'to_hostel_id' => (int) $data['to_hostel_id'],
                    ],
                ));
            }
        }

        return back()->with('success', 'Movement request submitted and awaiting approvals.');
    }

    public function respondMovement(Request $request, AssetMovement $movement)
    {
        $user = auth()->user();

        if ($movement->receiving_manager_id !== $user->id || $movement->status !== 'pending_receiving_manager') {
            abort(403, 'You cannot decide this movement request.');
        }

        $data = $request->validate([
            'decision' => ['required', 'in:accept,reject'],
            'receiving_manager_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $movement->forceFill([
            'receiving_manager_decided_by' => $user->id,
            'receiving_manager_decided_at' => now(),
            'receiving_manager_note' => $data['receiving_manager_note'] ?? null,
            'status' => $data['decision'] === 'accept' ? 'pending_admin' : 'rejected_by_receiving_manager',
        ])->save();

        app(OutboundWebhookService::class)->dispatch('asset.movement_receiving_decision', [
            'asset_movement_id' => $movement->id,
            'asset_id' => $movement->asset_id,
            'receiving_manager_id' => $user->id,
            'decision' => $data['decision'],
            'status' => $movement->status,
        ]);

        $admins = User::query()->whereIn('role', ['admin', 'super_admin'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new SystemEventNotification(
                event: 'asset_movement_receiving_manager_decision',
                title: 'Asset Movement Decision',
                message: sprintf(
                    'Receiving manager %s has %s movement request for asset %s.',
                    $user->name,
                    $data['decision'] === 'accept' ? 'accepted' : 'rejected',
                    $movement->asset?->name ?? 'N/A'
                ),
                payload: [
                    'asset_movement_id' => $movement->id,
                    'decision' => $data['decision'],
                ],
            ));
        }

        return back()->with('success', 'Movement decision saved.');
    }

    public function store(Request $request, Asset $asset)
    {
        $user = auth()->user();
        $hostelIds = $user->managedHostelIds();

        if (!$hostelIds->contains($asset->hostel_id)) {
            abort(403, 'Unauthorized asset reporting access.');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,medium,high,critical'],
        ]);

        $issue = AssetIssue::create([
            'asset_id' => $asset->id,
            'hostel_id' => $asset->hostel_id,
            'reported_by' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => 'open',
        ]);

        app(OutboundWebhookService::class)->dispatch('asset.issue_reported', [
            'asset_issue_id' => $issue->id,
            'asset_id' => $asset->id,
            'hostel_id' => $asset->hostel_id,
            'manager_id' => $user->id,
            'priority' => $issue->priority,
            'status' => $issue->status,
        ]);

        return back()->with('success', 'Asset issue reported successfully.');
    }
}
