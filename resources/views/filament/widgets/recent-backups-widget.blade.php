<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold">Recent Backups</h3>
                <p class="text-sm text-gray-500">Quick download for recently created backup archives.</p>
            </div>
            <x-filament::button
                size="sm"
                tag="a"
                color="gray"
                :href="route('filament.admin.pages.system.backups')"
            >
                Open Backup Manager
            </x-filament::button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b">
                    <tr>
                        <th class="py-2 pr-4 text-left font-semibold">File</th>
                        <th class="py-2 pr-4 text-left font-semibold">Size</th>
                        <th class="py-2 pr-4 text-left font-semibold">Created</th>
                        <th class="py-2 text-left font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($backups as $backup)
                        <tr>
                            <td class="py-3 pr-4 font-mono text-xs">{{ $backup['file'] }}</td>
                            <td class="py-3 pr-4">{{ $backup['size_human'] }}</td>
                            <td class="py-3 pr-4">{{ $backup['created_at']->format('M d, Y H:i') }}</td>
                            <td class="py-3">
                                <x-filament::button
                                    size="sm"
                                    color="gray"
                                    tag="a"
                                    :href="route('admin.backups.download', ['file' => $backup['file']])"
                                >
                                    Download
                                </x-filament::button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-gray-500">No backups yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
