<x-dashboard-layout :title="__('Hostel Change Requests')">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="uniform-page">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __("Hostel Change Requests") }}</h1>
            <p class="text-slate-600 dark:text-slate-300 mt-1">{{ __('Review student requests to move into hostels you manage.') }}</p>
        </div>

        <div class="uniform-grid-2">
            @forelse($requests as $request)
                <article class="uniform-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $request->student->name }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $request->student->email }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($request->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                            @elseif($request->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                            @elseif($request->status === 'pending_admin_approval') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                            @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 @endif">
                            {{ ucwords(str_replace('_', ' ', $request->status)) }}
                        </span>
                    </div>

                    <div class="mt-4 space-y-2 text-sm">
                        <p class="text-slate-700 dark:text-slate-200"><span class="font-medium">From:</span> {{ $request->currentHostel->name ?? 'Not Assigned' }}</p>
                        <p class="text-slate-700 dark:text-slate-200"><span class="font-medium">To:</span> {{ $request->requestedHostel->name }}</p>
                        <p class="text-slate-600 dark:text-slate-300"><span class="font-medium">Reason:</span> {{ $request->reason ?: '-' }}</p>
                        @if($request->manager_note)
                            <p class="text-slate-600 dark:text-slate-300"><span class="font-medium">Manager Note:</span> {{ $request->manager_note }}</p>
                        @endif
                    </div>

                    @if($request->status === 'pending_manager_approval')
                        <div class="mt-4 space-y-3">
                            <form action="{{ route('manager.hostel-change.approve', $request) }}" method="POST" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <textarea name="manager_note" rows="2" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="{{ __("Optional note for approval") }}"></textarea>
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm font-medium">{{ __("Approve") }}</button>
                            </form>
                            <form action="{{ route('manager.hostel-change.reject', $request) }}" method="POST" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <textarea name="manager_note" rows="2" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="{{ __("Reason for rejection (required)") }}"></textarea>
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm font-medium">{{ __("Reject") }}</button>
                            </form>
                        </div>
                    @endif
                </article>
            @empty
                <div class="col-span-full uniform-card p-8 text-center text-slate-600 dark:text-slate-300">{{ __("No hostel change requests found.") }}</div>
            @endforelse
        </div>

        <div>{{ $requests->links() }}</div>
    </div>
</x-dashboard-layout>
