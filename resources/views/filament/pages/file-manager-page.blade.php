<x-filament-panels::page>
    <style>
        @media (max-width: 375px) {
            .fm-list article {
                padding: 0.5rem 0.5rem;
                gap: 0.5rem;
            }

            .fm-list img {
                width: 2rem;
                height: 2rem;
            }

            .fm-list .fm-name {
                max-width: 8rem !important;
                font-size: 0.78rem;
            }

            .fm-list .fm-meta {
                font-size: 0.7rem;
                gap: 0.35rem;
            }
        }
    </style>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Upload New Image</x-slot>
            <form method="POST" action="{{ route('admin.files.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-700 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Allowed: JPG, PNG, WEBP only. Max 5MB.</p>
                <button type="submit" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Upload</button>
            </form>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">My Uploaded Images</x-slot>
            <form id="bulk-admin-files-form" method="POST" action="{{ route('admin.files.bulk-destroy') }}" onsubmit="return confirm('Delete selected files?');" class="mb-3 flex items-center gap-2">
                @csrf
                @method('DELETE')
                <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" id="select-all-admin-files" class="h-4 w-4 rounded border-gray-300 text-primary-600">
                    Select all
                </label>
                <select name="action" class="rounded-md border border-gray-300 px-2 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="delete">Delete Selected</option>
                </select>
                <button type="submit" class="inline-flex items-center rounded-md bg-danger-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-danger-500">Apply</button>
            </form>
            <div class="fm-list rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                @forelse($files as $index => $file)
                    <article class="px-3 sm:px-4 py-3 flex items-start gap-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        <input form="bulk-admin-files-form" type="checkbox" name="file_ids[]" value="{{ $file->id }}" class="h-4 w-4 rounded border-gray-300 text-primary-600">
                        <span class="text-xs text-gray-500 dark:text-gray-400 w-7 shrink-0">{{ ($files->firstItem() ?? 1) + $index }}</span>
                        <img src="{{ route('admin.files.show', $file) }}" alt="{{ $file->original_name }}" class="h-10 w-10 rounded object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                        <div class="min-w-0 flex-1">
                            <div class="min-w-0 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                                <span class="fm-name font-semibold text-gray-900 dark:text-gray-100 truncate max-w-[12rem]">{{ $file->original_name }}</span>
                                <span class="fm-meta text-gray-600 dark:text-gray-300">{{ $file->mime_type ?: '-' }}</span>
                                <span class="fm-meta text-gray-600 dark:text-gray-300">{{ number_format(($file->size ?? 0) / 1024, 2) }} KB</span>
                            </div>
                            <div class="mt-2 flex items-center gap-4 whitespace-nowrap">
                                <a href="{{ route('admin.files.show', $file) }}" target="_blank" class="text-primary-600 hover:underline text-sm">Preview</a>
                                <form method="POST" action="{{ route('admin.files.destroy', $file) }}" onsubmit="return confirm('Delete this file?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger-600 hover:underline text-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No files uploaded yet.</div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $files->links() }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">All System Images</x-slot>
            <div class="fm-list rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                @forelse($systemImages as $index => $image)
                    <article class="px-3 sm:px-4 py-3 flex items-start gap-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        <span class="text-xs text-gray-500 dark:text-gray-400 w-7 shrink-0">{{ ($systemImages->firstItem() ?? 1) + $index }}</span>
                        @if($image['exists'] && $image['disk'] === 'public')
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image['path']) }}" alt="{{ $image['label'] }}" class="h-10 w-10 rounded object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                        @elseif($image['source'] === 'managed_upload' && !empty($image['record_id']))
                            <img src="{{ route('admin.files.show', $image['record_id']) }}" alt="{{ $image['label'] }}" class="h-10 w-10 rounded object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                        @else
                            <div class="h-10 w-10 rounded border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 shrink-0"></div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="min-w-0 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                                <span class="fm-name font-semibold text-gray-900 dark:text-gray-100 truncate max-w-[12rem]">{{ $image['label'] }}</span>
                                <span class="fm-meta text-gray-600 dark:text-gray-300">{{ $image['source'] }}</span>
                                <span class="fm-meta text-gray-500 dark:text-gray-400">{{ $image['size'] !== null ? number_format(((int) $image['size']) / 1024, 2) . ' KB' : '-' }}</span>
                                <span class="fm-meta text-gray-500 dark:text-gray-400 truncate max-w-[14rem]" title="{{ $image['disk'] }}/{{ $image['path'] }}">{{ $image['disk'] }}/{{ $image['path'] }}</span>
                            </div>
                            <div class="mt-2 flex items-center gap-4 whitespace-nowrap">
                                @if($image['exists'] && $image['disk'] === 'public')
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image['path']) }}" target="_blank" class="text-primary-600 hover:underline text-sm">Preview</a>
                                @elseif($image['source'] === 'managed_upload' && !empty($image['record_id']))
                                    <a href="{{ route('admin.files.show', $image['record_id']) }}" target="_blank" class="text-primary-600 hover:underline text-sm">Preview</a>
                                @endif
                                <form method="POST" action="{{ route('admin.files.system-image.destroy') }}" onsubmit="return confirm('Delete this system image?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="source" value="{{ $image['source'] }}">
                                    <input type="hidden" name="disk" value="{{ $image['disk'] }}">
                                    <input type="hidden" name="path" value="{{ $image['path'] }}">
                                    @if(!empty($image['record_id']))
                                        <input type="hidden" name="record_id" value="{{ $image['record_id'] }}">
                                    @endif
                                    @if(!empty($image['setting_key']))
                                        <input type="hidden" name="key" value="{{ $image['setting_key'] }}">
                                    @endif
                                    <button type="submit" class="text-danger-600 hover:underline text-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No system images found.</div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $systemImages->onEachSide(1)->links() }}
            </div>
        </x-filament::section>
    </div>

    <script>
        (function () {
            const selectAll = document.getElementById('select-all-admin-files');
            if (!selectAll) return;
            const checkboxes = Array.from(document.querySelectorAll('input[name="file_ids[]"][form="bulk-admin-files-form"]'));
            selectAll.addEventListener('change', function () {
                checkboxes.forEach((cb) => { cb.checked = selectAll.checked; });
            });
        })();
    </script>
</x-filament-panels::page>
