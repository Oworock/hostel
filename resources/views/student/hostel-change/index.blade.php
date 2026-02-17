<x-dashboard-layout title="Hostel Change Requests">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="uniform-page">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Request Hostel Change</h1>
            <p class="text-slate-600 dark:text-slate-300 mt-1">Requests require approval from the target hostel manager and then final admin approval.</p>
        </div>

        <section class="uniform-card p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">New Request</h2>
            <form action="{{ route('student.hostel-change.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="requested_hostel_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Choose New Hostel</label>
                    <select id="requested_hostel_id" name="requested_hostel_id" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100" required>
                        <option value="">Select hostel</option>
                        @foreach($availableHostels as $hostel)
                            <option value="{{ $hostel->id }}">{{ $hostel->name }} - {{ $hostel->city }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="reason" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Reason (optional)</label>
                    <textarea id="reason" name="reason" rows="3" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"></textarea>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 font-medium">Submit Request</button>
            </form>
        </section>

        <section class="uniform-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">My Requests</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Current Hostel</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Requested Hostel</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Manager Feedback</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($requests as $request)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $request->currentHostel->name ?? 'Not Assigned' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $request->requestedHostel->name }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        @if($request->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                        @elseif($request->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                        @elseif($request->status === 'pending_admin_approval') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 @endif">
                                        {{ ucwords(str_replace('_', ' ', $request->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $request->reason ?: '-' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    {{ $request->status === 'rejected' ? ($request->manager_note ?: '-') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $request->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-slate-600 dark:text-slate-300">No requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">{{ $requests->links() }}</div>
        </section>
    </div>
</x-dashboard-layout>
