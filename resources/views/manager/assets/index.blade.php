<x-dashboard-layout title="Asset Management">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="uniform-page space-y-6">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Hostel Assets</h1>
            <p class="text-slate-600 dark:text-slate-300">Add assets, report issues, and request inter-hostel movement with approvals.</p>
        </div>

        <section class="uniform-card p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-slate-600 dark:text-slate-300">Need to register a new asset? Use the dedicated add page.</p>
                <a href="{{ route('manager.assets.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Open Add Asset Page</a>
            </div>
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-3">Managers can add assets but cannot edit any asset record after creation.</p>
        </section>

        <section class="uniform-card p-6">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-4">Incoming Movement Requests</h2>
            <div class="space-y-3">
                @forelse($incomingMovements as $movement)
                    <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $movement->asset?->name }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">From {{ $movement->fromHostel?->name }} to {{ $movement->toHostel?->name }} • Requested by {{ $movement->requester?->name }}</p>
                        <form method="POST" action="{{ route('manager.assets.movements.respond', $movement) }}" class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                            @csrf
                            <textarea name="receiving_manager_note" rows="2" placeholder="Comment" class="md:col-span-3 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900"></textarea>
                            <button name="decision" value="accept" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Accept</button>
                            <button name="decision" value="reject" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Reject</button>
                        </form>
                    </div>
                @empty
                    <p class="text-slate-600 dark:text-slate-300">No incoming movement requests.</p>
                @endforelse
            </div>
        </section>

        <section class="uniform-card p-6">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-4">Movement Requests Awaiting Approval</h2>
            <div class="space-y-3">
                @forelse($pendingAdminMovements as $movement)
                    <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $movement->asset?->name }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Destination: {{ $movement->toHostel?->name }} • Status: {{ ucwords(str_replace('_', ' ', $movement->status)) }}</p>
                    </div>
                @empty
                    <p class="text-slate-600 dark:text-slate-300">No pending movement requests.</p>
                @endforelse
            </div>
        </section>

        <section class="uniform-card p-6">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-4">Available Assets</h2>
            <div class="space-y-5">
                @forelse($assets as $asset)
                    <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-4">
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div class="flex items-start gap-4">
                                @if($asset->image_path)
                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($asset->image_path) }}"
                                        alt="{{ $asset->name }}"
                                        class="w-16 h-16 rounded-lg object-cover border border-slate-200 dark:border-slate-700"
                                    >
                                @endif
                                <div>
                                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $asset->name }}</h3>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ $asset->hostel?->name }} • {{ strtoupper($asset->condition) }} • Open issues: {{ $asset->open_issues_count }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Asset #: {{ $asset->asset_number ?: 'N/A' }} • Code: {{ $asset->asset_code ?: 'N/A' }} • Serial: {{ $asset->serial_number ?: 'N/A' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Category: {{ $asset->category ?: 'N/A' }} • Location: {{ $asset->location ?: 'N/A' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Cost: {{ $asset->acquisition_cost ? formatCurrency((float) $asset->acquisition_cost) : 'N/A' }} • Supplier: {{ $asset->supplier ?: 'N/A' }} • Invoice: {{ $asset->invoice_reference ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('manager.assets.movements.request', $asset) }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                            @csrf
                            <select name="to_hostel_id" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100" required>
                                <option value="">Request move to hostel</option>
                                @foreach($availableHostels as $hostel)
                                    <option value="{{ $hostel->id }}">{{ $hostel->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="request_note" placeholder="Movement note" class="md:col-span-2 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                            <button type="submit" class="md:col-span-3 bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700">Request Movement</button>
                        </form>

                        <form method="POST" action="{{ route('manager.assets.issues.store', $asset) }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                            @csrf
                            <input type="text" name="title" placeholder="Issue title" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100" required>
                            <select name="priority" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100" required>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Report Issue</button>
                            <textarea name="description" placeholder="Describe the issue" class="md:col-span-3 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100" rows="3" required></textarea>
                        </form>
                    </div>
                @empty
                    <p class="text-slate-600 dark:text-slate-300">No assets assigned to your managed hostels.</p>
                @endforelse
            </div>
        </section>

        <section class="uniform-card p-6">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-4">Recent Asset Issue Reports</h2>
            <div class="space-y-3">
                @forelse($recentIssues as $issue)
                    <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $issue->title }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Asset: {{ $issue->asset?->name }} • Hostel: {{ $issue->hostel?->name }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Priority: {{ ucfirst($issue->priority) }} • Status: {{ ucwords(str_replace('_',' ', $issue->status)) }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $issue->created_at?->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-slate-600 dark:text-slate-300">No issue reports yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-dashboard-layout>
