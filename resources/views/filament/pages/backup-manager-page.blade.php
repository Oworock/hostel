<x-filament-panels::page>
    <x-filament::section>
        <div class="space-y-4">
            @if (session('status'))
                <div class="rounded-lg border border-success-200 bg-success-50 p-3 text-sm text-success-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('restore'))
                <div class="rounded-lg border border-danger-200 bg-danger-50 p-3 text-sm text-danger-700">
                    {{ $errors->first('restore') }}
                </div>
            @endif

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold">Recent Backups</h3>
                    <p class="text-sm text-gray-500">Download older backup archives directly from this list.</p>
                </div>
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
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-filament::button
                                            size="sm"
                                            tag="a"
                                            color="gray"
                                            :href="route('admin.backups.download', ['file' => $backup['file']])"
                                        >
                                            Download
                                        </x-filament::button>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.backups.restore-database', ['file' => $backup['file']]) }}"
                                            onsubmit="return confirm('Restore DATABASE ONLY from this backup? This will overwrite current database tables/data.')"
                                        >
                                            @csrf
                                            <x-filament::button size="sm" type="submit" color="warning">
                                                Restore DB Only
                                            </x-filament::button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.backups.restore-files', ['file' => $backup['file']]) }}"
                                            onsubmit="return confirm('Restore FILES ONLY from this backup? This will overwrite current project files.')"
                                        >
                                            @csrf
                                            <x-filament::button size="sm" type="submit" color="info">
                                                Restore Files Only
                                            </x-filament::button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.backups.destroy', ['file' => $backup['file']]) }}"
                                            onsubmit="return confirm('Delete this backup permanently?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <x-filament::button size="sm" type="submit" color="danger">
                                                Delete
                                            </x-filament::button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-500">No backups found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
