@php
    $user = auth()->user();
    $unread = $user?->unreadNotifications()->latest()->limit(10)->get() ?? collect();
@endphp

<div>
    @if($user && $user->isAdmin())
        <x-filament::dropdown placement="bottom-end" teleport>
            <x-slot name="trigger">
                <button
                    type="button"
                    class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    aria-label="Unread notifications"
                >
                    <x-heroicon-o-bell class="h-5 w-5" />
                    @if($unread->count() > 0)
                        <span class="absolute -right-1 -top-1 inline-flex min-h-[18px] min-w-[18px] items-center justify-center rounded-full bg-danger-600 px-1 text-[10px] font-semibold text-white">
                            {{ $unread->count() > 99 ? '99+' : $unread->count() }}
                        </span>
                    @endif
                </button>
            </x-slot>

            <div class="w-[25rem] max-w-[90vw] rounded-xl border border-gray-200 bg-white p-3 shadow-xl dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Unread Notifications</p>
                    @if($unread->count() > 0)
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="text-xs text-primary-600 hover:underline">Mark all as read</button>
                        </form>
                    @endif
                </div>

                <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                    @forelse($unread as $notification)
                        <article class="rounded-lg border border-primary-200 bg-primary-50 p-3 dark:border-primary-700/60 dark:bg-primary-900/20">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 leading-snug">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                </p>
                                <span class="shrink-0 text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <div class="mt-2 flex justify-end">
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="text-[11px] text-primary-600 hover:underline">Mark read</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <p class="py-4 text-center text-xs text-gray-500 dark:text-gray-400">No unread notifications.</p>
                    @endforelse
                </div>
            </div>
        </x-filament::dropdown>
    @endif
</div>
