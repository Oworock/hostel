@php
    $base = 'menu-card flex items-center gap-3 font-semibold text-base transition-colors';
    $active = 'is-active text-blue-700 dark:text-blue-200';
    $idle = 'text-slate-700 dark:text-slate-200';
    $operationsOpen = request()->routeIs('manager.students.*')
        || request()->routeIs('manager.rooms.*')
        || request()->routeIs('manager.bookings.*')
        || request()->routeIs('manager.payments.*')
        || request()->routeIs('manager.complaints.*')
        || request()->routeIs('notifications.*')
        || request()->routeIs('manager.files.*');
    $requestsOpen = request()->routeIs('manager.hostel-change.*') || request()->routeIs('manager.room-change.*');
    $servicesOpen = request()->routeIs('manager.assets.*') || request()->routeIs('manager.staff.*');
@endphp

<div class="pb-6 space-y-4">
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/50 p-3">
        <a href="{{ route('dashboard') }}" class="{{ $base }} {{ request()->routeIs('dashboard') ? $active : $idle }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l-4-4m0 0l-4 4m4-4v4m8-11l2 1"></path>
            </svg>
            <span class="menu-label">{{ __('Dashboard') }}</span>
        </a>
    </div>

    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/50 p-3">
        <details {{ $operationsOpen ? 'open' : '' }} class="group">
            <summary class="menu-card cursor-pointer list-none {{ $operationsOpen ? 'is-active text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">
                <span class="menu-label">{{ __('Operations') }}</span>
            </summary>
            <div class="mt-2 space-y-2 pl-2">
                <a href="{{ route('manager.students.index') }}" class="{{ $base }} {{ request()->routeIs('manager.students.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Students') }}</span>
                </a>
                <a href="{{ route('manager.rooms.index') }}" class="{{ $base }} {{ request()->routeIs('manager.rooms.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Rooms') }}</span>
                </a>
                <a href="{{ route('manager.rooms.create') }}" class="{{ $base }} {{ request()->routeIs('manager.rooms.create') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Add New Room') }}</span>
                </a>
                <a href="{{ route('manager.bookings.index') }}" class="{{ $base }} {{ request()->routeIs('manager.bookings.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Bookings') }}</span>
                </a>
                <a href="{{ route('manager.payments.index') }}" class="{{ $base }} {{ request()->routeIs('manager.payments.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Payments') }}</span>
                </a>
                <a href="{{ route('manager.complaints.index') }}" class="{{ $base }} {{ request()->routeIs('manager.complaints.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Complaints Queue') }}</span>
                </a>
                <a href="{{ route('notifications.index') }}" class="{{ $base }} {{ request()->routeIs('notifications.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Notifications') }}</span>
                </a>
                <a href="{{ route('manager.files.index') }}" class="{{ $base }} {{ request()->routeIs('manager.files.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('File Manager') }}</span>
                </a>
            </div>
        </details>

        <details {{ $requestsOpen ? 'open' : '' }} class="group mt-3">
            <summary class="menu-card cursor-pointer list-none {{ $requestsOpen ? 'is-active text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">
                <span class="menu-label">{{ __('Requests') }}</span>
            </summary>
            <div class="mt-2 space-y-2 pl-2">
                <a href="{{ route('manager.hostel-change.index') }}" class="{{ $base }} {{ request()->routeIs('manager.hostel-change.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Hostel Change') }}</span>
                </a>
                <a href="{{ route('manager.room-change.index') }}" class="{{ $base }} {{ request()->routeIs('manager.room-change.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Room Change') }}</span>
                </a>
            </div>
        </details>

        <details {{ $servicesOpen ? 'open' : '' }} class="group mt-3">
            <summary class="menu-card cursor-pointer list-none {{ $servicesOpen ? 'is-active text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">
                <span class="menu-label">{{ __('Services') }}</span>
            </summary>
            <div class="mt-2 space-y-2 pl-2">
                @if(\App\Models\Addon::isActive('asset-management'))
                    <a href="{{ route('manager.assets.index') }}" class="{{ $base }} {{ request()->routeIs('manager.assets.*') ? $active : $idle }}">
                        <span class="menu-label">{{ __('Assets') }}</span>
                    </a>
                    <a href="{{ route('manager.assets.create') }}" class="{{ $base }} {{ request()->routeIs('manager.assets.create') ? $active : $idle }}">
                        <span class="menu-label">{{ __('Add Asset') }}</span>
                    </a>
                @endif
                @if(\App\Models\Addon::isActive('staff-payroll'))
                    <a href="{{ route('manager.staff.index') }}" class="{{ $base }} {{ request()->routeIs('manager.staff.*') ? $active : $idle }}">
                        <span class="menu-label">{{ __('Staff Directory') }}</span>
                    </a>
                @endif
            </div>
        </details>
    </div>

    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/50 p-3">
        <details open class="group">
            <summary class="menu-card cursor-pointer list-none {{ request()->routeIs('manager.profile.*') ? 'is-active text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">
                <span class="menu-label">{{ __('Account') }}</span>
            </summary>
            <div class="mt-2 space-y-2 pl-2">
                <a href="{{ route('manager.profile.edit') }}" class="{{ $base }} {{ request()->routeIs('manager.profile.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Profile Settings') }}</span>
                </a>
            </div>
        </details>
    </div>
</div>
