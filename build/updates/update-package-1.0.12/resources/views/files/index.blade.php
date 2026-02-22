<x-dashboard-layout title="File Manager">
    @php
        $user = auth()->user();
        $routePrefix = $user->isAdmin() ? 'admin' : 'manager';
    @endphp

    @if($user->isManager())
        <x-slot name="sidebar">
            @include('components.manager-sidebar')
        </x-slot>
    @elseif($user->isStudent())
        <x-slot name="sidebar">
            @include('components.student-sidebar')
        </x-slot>
    @endif

    <div class="uniform-page space-y-6">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">File Manager</h1>
            <p class="text-slate-600 dark:text-slate-300">
                {{ $user->isAdmin() ? 'Manage all image uploads across the system.' : 'Upload and manage your own image files.' }}
            </p>
        </div>

        <section class="uniform-card p-6">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-4">Upload New Image</h2>
            <form method="POST" action="{{ route($routePrefix . '.files.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2">
                <p class="text-sm text-gray-500 dark:text-slate-400">Allowed: JPG, PNG, WEBP only. Max 5MB.</p>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700">Upload</button>
            </form>
        </section>

        <section class="uniform-card p-6">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-4">My Uploaded Images</h2>
            <form id="bulk-files-form" method="POST" action="{{ route($routePrefix . '.files.bulk-destroy') }}" onsubmit="return confirm('Delete selected files?');" class="mb-3 flex items-center gap-2">
                @csrf
                @method('DELETE')
                <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-slate-300">
                    <input type="checkbox" id="select-all-files" class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    Select all
                </label>
                <select name="action" class="border border-gray-300 dark:border-slate-600 rounded-md px-2 py-1.5 text-sm">
                    <option value="delete">Delete Selected</option>
                </select>
                <button type="submit" class="bg-red-600 text-white px-3 py-1.5 rounded-md text-sm hover:bg-red-700">Apply</button>
            </form>
            <div class="rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden w-full mt-3">
                @forelse($files as $index => $file)
                    <article class="px-3 sm:px-4 py-3 flex items-center gap-3 border-b border-gray-200 dark:border-slate-700 last:border-b-0">
                        <input form="bulk-files-form" type="checkbox" name="file_ids[]" value="{{ $file->id }}" class="h-4 w-4 rounded border-gray-300 text-blue-600">
                        <span class="text-xs text-gray-500 dark:text-slate-400 w-7 shrink-0">{{ ($files->firstItem() ?? 1) + $index }}</span>
                        <img src="{{ route($routePrefix . '.files.show', $file) }}" alt="{{ $file->original_name }}" class="h-10 w-10 rounded object-cover border border-gray-200 dark:border-slate-700 shrink-0">
                        <div class="min-w-0 flex-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                            <span class="font-semibold text-gray-900 dark:text-slate-100 truncate max-w-[12rem]">{{ $file->original_name }}</span>
                            <span class="text-gray-600 dark:text-slate-300">{{ $file->mime_type ?: '-' }}</span>
                            <span class="text-gray-600 dark:text-slate-300">{{ number_format(($file->size ?? 0) / 1024, 2) }} KB</span>
                            <span class="text-gray-500 dark:text-slate-400">{{ $file->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex items-center gap-4 text-sm whitespace-nowrap">
                            <a href="{{ route($routePrefix . '.files.show', $file) }}" target="_blank" class="text-blue-600 hover:text-blue-700">Preview</a>
                            <form method="POST" action="{{ route($routePrefix . '.files.update', $file) }}" enctype="multipart/form-data" class="inline-flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required class="text-xs max-w-[160px]">
                                <button type="submit" class="text-amber-600 hover:text-amber-700">Update File</button>
                            </form>
                            <form method="POST" action="{{ route($routePrefix . '.files.destroy', $file) }}" onsubmit="return confirm('Delete this file?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">No files uploaded yet.</div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $files->links() }}
            </div>
        </section>

        @if($user->isAdmin())
            <section class="uniform-card p-6">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-4">All System Images</h2>
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">Includes room images, hostel images, welcome content, logos, favicon, addon assets, and managed uploads.</p>
                <div class="rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
                    @forelse($systemImages as $image)
                        <article class="px-3 sm:px-4 py-3 flex items-start gap-3 border-b border-gray-200 dark:border-slate-700 last:border-b-0">
                            @if($image['exists'] && $image['disk'] === 'public')
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image['path']) }}" alt="{{ $image['label'] }}" class="h-10 w-10 rounded object-cover border border-gray-200 dark:border-slate-700 shrink-0">
                            @elseif($image['source'] === 'managed_upload' && !empty($image['record_id']))
                                <img src="{{ route('admin.files.show', $image['record_id']) }}" alt="{{ $image['label'] }}" class="h-10 w-10 rounded object-cover border border-gray-200 dark:border-slate-700 shrink-0">
                            @else
                                <div class="h-10 w-10 rounded border border-gray-200 dark:border-slate-700 bg-gray-100 dark:bg-slate-800 shrink-0"></div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="min-w-0 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                                    <span class="font-semibold text-gray-900 dark:text-slate-100 truncate max-w-[13rem]">{{ $image['label'] }}</span>
                                    <span class="text-gray-600 dark:text-slate-300">{{ $image['source'] }}</span>
                                    <span class="text-gray-500 dark:text-slate-400">{{ $image['size'] !== null ? number_format(((int) $image['size']) / 1024, 2) . ' KB' : '-' }}</span>
                                    <span class="text-gray-500 dark:text-slate-400 truncate max-w-[14rem]" title="{{ $image['disk'] }}/{{ $image['path'] }}">{{ $image['disk'] }}/{{ $image['path'] }}</span>
                                </div>
                                <div class="mt-2 flex items-center gap-4 whitespace-nowrap">
                                    @if($image['exists'] && $image['disk'] === 'public')
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image['path']) }}" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm">Preview</a>
                                    @elseif($image['source'] === 'managed_upload' && !empty($image['record_id']))
                                        <a href="{{ route('admin.files.show', $image['record_id']) }}" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm">Preview</a>
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
                                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">No system images found.</div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>

    <script>
        (function () {
            const selectAll = document.getElementById('select-all-files');
            if (!selectAll) return;
            const checkboxes = Array.from(document.querySelectorAll('input[name="file_ids[]"][form="bulk-files-form"]'));
            selectAll.addEventListener('change', function () {
                checkboxes.forEach((cb) => { cb.checked = selectAll.checked; });
            });
        })();
    </script>
</x-dashboard-layout>
