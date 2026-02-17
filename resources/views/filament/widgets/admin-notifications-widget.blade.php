<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold">System Notifications</h3>
                <p class="text-sm text-gray-500">Unread: {{ $unreadCount }}</p>
            </div>
            @if($unreadCount > 0)
                <x-filament::button size="sm" wire:click="markAllRead">
                    Mark all as read
                </x-filament::button>
            @endif
        </div>

        <div class="space-y-2">
            @forelse($notifications as $notification)
                <div class="rounded-lg border p-3 {{ $notification->read_at ? 'opacity-70' : 'bg-primary-50 border-primary-200' }}">
                    <div class="text-sm font-semibold">{{ $notification->data['title'] ?? 'Notification' }}</div>
                    <div class="text-sm text-gray-600">{{ $notification->data['message'] ?? '' }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ $notification->created_at?->diffForHumans() }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No notifications yet.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

