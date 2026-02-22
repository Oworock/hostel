<x-dashboard-layout :title="__('Staff Directory')">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

<div class="uniform-page">
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ __('Staff Directory') }}</h1>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    {{ __('Total') }}: {{ $summary['all'] ?? 0 }}
                </span>
                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-300">
                    {{ __('Active') }}: {{ $summary['active'] ?? 0 }}
                </span>
                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                    {{ __('Pending') }}: {{ $summary['pending'] ?? 0 }}
                </span>
                <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-1 font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                    {{ __('General Duty') }}: {{ $summary['general'] ?? 0 }}
                </span>
            </div>
        </div>
        <p class="mt-1 text-slate-600 dark:text-slate-300">
            {{ __('Assigned staff for your hostels and all general-duty staff.') }}
        </p>
    </div>

    <form method="GET" class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">{{ __('Search') }}</label>
            <input
                type="text"
                name="q"
                value="{{ $search ?? '' }}"
                placeholder="{{ __('Name, email, phone, department...') }}"
                class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm"
            >
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">{{ __('Status') }}</label>
            <select name="status" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                <option value="all" @selected(($status ?? 'all') === 'all')>{{ __('All') }}</option>
                <option value="active" @selected(($status ?? '') === 'active')>{{ __('Active') }}</option>
                <option value="pending" @selected(($status ?? '') === 'pending')>{{ __('Pending') }}</option>
                <option value="suspended" @selected(($status ?? '') === 'suspended')>{{ __('Suspended') }}</option>
                <option value="inactive" @selected(($status ?? '') === 'inactive')>{{ __('Inactive') }}</option>
                <option value="sacked" @selected(($status ?? '') === 'sacked')>{{ __('Sacked') }}</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">{{ __('Duty Scope') }}</label>
            <select name="scope" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm">
                <option value="all" @selected(($scope ?? 'all') === 'all')>{{ __('All') }}</option>
                <option value="general" @selected(($scope ?? '') === 'general')>{{ __('General') }}</option>
                <option value="assigned" @selected(($scope ?? '') === 'assigned')>{{ __('Assigned') }}</option>
            </select>
        </div>
        <div class="md:col-span-4 flex items-center gap-2">
            <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2">{{ __('Apply') }}</button>
            <a href="{{ route('manager.staff.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 dark:border-slate-700 text-sm font-semibold px-4 py-2">
                {{ __('Reset') }}
            </a>
        </div>
    </form>

    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/70">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('Contact') }}</th>
                        <th class="px-4 py-3">{{ __('Role') }}</th>
                        <th class="px-4 py-3">{{ __('Duty Scope') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($staff as $row)
                        @php
                            $isGeneral = (bool) ($row->is_general_staff ?? false);
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $row->full_name }}</p>
                                @if(!empty($row->employee_code))
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Staff ID') }}: {{ $row->employee_code }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                <p>{{ $row->email }}</p>
                                <p>{{ $row->phone ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $row->job_title ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                {{ $isGeneral ? __('General (All Hostels)') : ($row->assignedHostel?->name ?: __('Assigned')) }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($row->status === 'active') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                                    @elseif($row->status === 'pending') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300
                                    @else bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 @endif">
                                    {{ ucfirst((string) $row->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @if(!empty($row->email))
                                        <a href="mailto:{{ $row->email }}" class="inline-flex items-center rounded-md border border-slate-300 dark:border-slate-700 px-2 py-1 text-xs font-semibold">{{ __('Email') }}</a>
                                    @endif
                                    @if(!empty($row->phone))
                                        <a href="tel:{{ $row->phone }}" class="inline-flex items-center rounded-md border border-slate-300 dark:border-slate-700 px-2 py-1 text-xs font-semibold">{{ __('Call') }}</a>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', (string) $row->phone) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-md border border-slate-300 dark:border-slate-700 px-2 py-1 text-xs font-semibold">{{ __('WhatsApp') }}</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-600 dark:text-slate-300">
                                {{ __('No staff available for your hostels at the moment.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($staff, 'links'))
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
</div>
</x-dashboard-layout>
