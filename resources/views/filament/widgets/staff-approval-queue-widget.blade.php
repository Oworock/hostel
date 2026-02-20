<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Approval Queue') }}
        </x-slot>
        <x-slot name="description">
            {{ __('Quickly approve or reject pending staff registrations.') }}
        </x-slot>

        <div class="mb-3 grid grid-cols-1 md:grid-cols-3 gap-2">
            <div>
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="{{ __('Search name, email, phone...') }}"
                    class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm"
                />
            </div>
            <div>
                <select wire:model.live="scope" class="fi-select-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                    <option value="all">{{ __('All Scope') }}</option>
                    <option value="general">{{ __('General Staff') }}</option>
                    <option value="assigned">{{ __('Assigned Hostel') }}</option>
                </select>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-1 font-medium text-gray-700 dark:text-gray-200">
                    {{ __('Pending') }}: {{ $this->pendingStaff->count() }}
                </span>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($this->pendingStaff as $staff)
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                    <div class="space-y-1">
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $staff->full_name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $staff->email }} | {{ $staff->phone ?: '-' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-1">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">{{ __('Pending') }}</span>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                {{ !empty($staff->is_general_staff) ? __('General') : __('Assigned') }}
                            </span>
                            {{ $staff->job_title ?: __('No title') }}
                            @if(!empty($staff->department))
                                | {{ $staff->department }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-filament::button color="success" size="sm" wire:click="approve({{ $staff->id }})">
                            {{ __('Approve') }}
                        </x-filament::button>
                        <x-filament::button color="danger" size="sm" wire:click="reject({{ $staff->id }})">
                            {{ __('Reject') }}
                        </x-filament::button>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-600 dark:text-gray-300">
                    {{ __('No pending staff approvals at the moment.') }}
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
