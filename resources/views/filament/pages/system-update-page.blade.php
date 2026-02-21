<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::section>
            <div class="space-y-2">
                <h3 class="text-lg font-semibold">System Update Center</h3>
                <p class="text-sm text-gray-500">
                    Pull update and review the package preview before continuing.
                </p>
            </div>
            <div class="mt-4">
                {{ $this->form }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="space-y-3">
                <h3 class="text-lg font-semibold">Update Preview</h3>
                @if (!empty($previewReport))
                    @if(!empty($previewReport['manifest']['version']) || !empty($previewReport['manifest']['notes']))
                        <div class="rounded-lg border p-3 text-sm">
                            @if(!empty($previewReport['manifest']['version']))
                                <div><span class="font-medium">Incoming Version:</span> {{ $previewReport['manifest']['version'] }}</div>
                            @endif
                            @if(!empty($previewReport['manifest']['notes']))
                                <div class="mt-1 text-gray-600"><span class="font-medium">Notes:</span> {{ $previewReport['manifest']['notes'] }}</div>
                            @endif
                        </div>
                    @endif

                    <div class="grid gap-3 sm:grid-cols-4">
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-gray-500">Total Files</div>
                            <div class="text-lg font-semibold">{{ $previewReport['files_total'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-gray-500">New Files</div>
                            <div class="text-lg font-semibold text-success-700">{{ $previewReport['create'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-gray-500">Updated Files</div>
                            <div class="text-lg font-semibold text-warning-700">{{ $previewReport['update'] ?? 0 }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-xs text-gray-500">Unchanged</div>
                            <div class="text-lg font-semibold">{{ $previewReport['unchanged'] ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border">
                        <table class="w-full text-sm">
                            <thead class="border-b">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold">Status</th>
                                    <th class="px-3 py-2 text-left font-semibold">File</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach(($previewReport['affected'] ?? []) as $row)
                                    <tr>
                                        <td class="px-3 py-2">
                                            @php $status = (string) ($row['status'] ?? 'update'); @endphp
                                            @if ($status === 'create')
                                                <span class="inline-flex rounded bg-success-100 px-2 py-1 text-xs font-medium text-success-800">create</span>
                                            @elseif ($status === 'unchanged')
                                                <span class="inline-flex rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">unchanged</span>
                                            @else
                                                <span class="inline-flex rounded bg-warning-100 px-2 py-1 text-xs font-medium text-warning-800">update</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 font-mono text-xs">{{ $row['path'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">
                        Pull the update package first. Continue/Decline options appear in the form only after preview is ready.
                    </p>
                @endif
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="space-y-3">
                <h3 class="text-lg font-semibold">Update Audit Log</h3>
                <div class="overflow-x-auto rounded-lg border">
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">When</th>
                                <th class="px-3 py-2 text-left font-semibold">Admin</th>
                                <th class="px-3 py-2 text-left font-semibold">Action</th>
                                <th class="px-3 py-2 text-left font-semibold">Package</th>
                                <th class="px-3 py-2 text-left font-semibold">File Count</th>
                                <th class="px-3 py-2 text-left font-semibold">Applied</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($auditRows as $row)
                                <tr>
                                    <td class="px-3 py-2">{{ optional($row->applied_at)->format('M d, Y H:i') ?: '-' }}</td>
                                    <td class="px-3 py-2">{{ $row->user?->name ?: 'System' }}</td>
                                    <td class="px-3 py-2">{{ ucfirst((string) $row->action) }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $row->package_name ?: '-' }}</td>
                                    <td class="px-3 py-2">{{ (int) $row->files_total }}</td>
                                    <td class="px-3 py-2">{{ (int) $row->files_applied }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-gray-500">No update audit records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
