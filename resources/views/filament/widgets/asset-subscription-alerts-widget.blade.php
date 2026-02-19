<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Intangible Asset Expiry Alerts</h3>
                <span class="text-sm text-danger-600">Expired: {{ $expiredCount }}</span>
            </div>

            @forelse($expiring as $item)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                    <p class="font-semibold">{{ $item['name'] }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Hostel: {{ $item['hostel'] ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Expires: {{ $item['expires_at'] }} ({{ $item['days_remaining'] }} day(s) left)</p>
                </div>
            @empty
                <p class="text-sm text-gray-600 dark:text-gray-300">No subscriptions expiring in the next 7 days.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
