<x-dashboard-layout title="Complaints">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="uniform-page">
        <section class="uniform-header">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Complaints Queue</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Only complaints from students in your assigned hostels are visible.</p>
            </div>
        </section>

        <section class="uniform-grid-4">
            <div class="uniform-card p-5">
                <p class="text-sm text-slate-500 dark:text-slate-400">Total</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $complaints->total() }}</p>
            </div>
            <div class="uniform-card p-5">
                <p class="text-sm text-slate-500 dark:text-slate-400">Open</p>
                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $complaints->where('status', 'open')->count() }}</p>
            </div>
            <div class="uniform-card p-5">
                <p class="text-sm text-slate-500 dark:text-slate-400">In Progress</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $complaints->where('status', 'in_progress')->count() }}</p>
            </div>
            <div class="uniform-card p-5">
                <p class="text-sm text-slate-500 dark:text-slate-400">Resolved/Closed</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $complaints->whereIn('status', ['resolved', 'closed'])->count() }}</p>
            </div>
        </section>

        <section class="uniform-grid-2">
            @forelse($complaints as $complaint)
                <article class="uniform-card p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $complaint->subject }}</h2>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                                {{ $complaint->user->name }} • {{ $complaint->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                        <span class="text-xs px-3 py-1 rounded-full font-semibold
                            @if($complaint->status === 'resolved') bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300
                            @elseif($complaint->status === 'in_progress') bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                            @elseif($complaint->status === 'closed') bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300
                            @else bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300
                            @endif">
                            {{ ucwords(str_replace('_', ' ', $complaint->status)) }}
                        </span>
                    </div>

                    <div class="mt-4 border-y border-slate-200 dark:border-slate-700 py-4">
                        <p class="text-slate-800 dark:text-slate-200 leading-relaxed">{{ $complaint->description }}</p>
                    </div>

                    <form method="POST" action="{{ route('manager.complaints.respond', $complaint) }}" class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3">
                        @csrf
                        @method('PATCH')
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Response</label>
                            <textarea name="response" rows="4" class="mt-1 w-full border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg px-3 py-2" required>{{ old('response', $complaint->response) }}</textarea>
                        </div>
                        <div class="md:col-span-2 flex flex-col gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
                                <select name="status" class="mt-1 w-full border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg px-3 py-2">
                                    @foreach(['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $complaint->status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-medium">
                                Save Response
                            </button>
                        </div>
                    </form>
                </article>
            @empty
                <div class="uniform-card p-8 text-center text-slate-600 dark:text-slate-300 md:col-span-2">
                    No complaints available for your hostels.
                </div>
            @endforelse
        </section>

        <div>
            {{ $complaints->links() }}
        </div>
    </div>
</x-dashboard-layout>
