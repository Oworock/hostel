<x-dashboard-layout :title="__('Notifications')">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="uniform-page">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __("Notifications") }}</h1>
            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">{{ __("Mark all as read") }}</button>
            </form>
        </div>

        <section class="uniform-card p-4 space-y-3">
            @forelse($notifications as $notification)
                <div class="rounded-lg border p-4 {{ $notification->read_at ? 'border-slate-200 dark:border-slate-700' : 'border-blue-200 dark:border-blue-800 bg-blue-50/60 dark:bg-blue-900/10' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $notification->data['title'] ?? 'Notification' }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">{{ $notification->created_at?->diffForHumans() }}</p>
                        </div>
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-3 py-1 rounded-md">{{ __("Mark read") }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-slate-600 dark:text-slate-300">{{ __("No notifications available.") }}</p>
            @endforelse
        </section>

        <div>{{ $notifications->links() }}</div>
    </div>
</x-dashboard-layout>

