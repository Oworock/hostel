@php
    $base = 'menu-card flex items-center gap-3 font-semibold text-base transition-colors';
    $active = 'is-active text-blue-700 dark:text-blue-200';
    $idle = 'text-slate-700 dark:text-slate-200';
@endphp

<div class="pb-6 space-y-4">
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/50 p-3 space-y-2">
        <a href="{{ route('dashboard') }}" class="{{ $base }} {{ request()->routeIs('dashboard') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9" /></svg>
            <span class="menu-label">{{ __('Dashboard') }}</span>
        </a>

        <a href="{{ route('manager.students.index') }}" class="{{ $base }} {{ request()->routeIs('manager.students.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5-3.9M9 20H4v-2a4 4 0 015-3.9m8-6a3 3 0 11-6 0 3 3 0 016 0zm-8 0a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <span class="menu-label">{{ __('Students') }}</span>
        </a>

        <a href="{{ route('manager.rooms.index') }}" class="{{ $base }} {{ request()->routeIs('manager.rooms.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 10h6" /></svg>
            <span class="menu-label">{{ __('Rooms') }}</span>
        </a>

        <a href="{{ route('manager.rooms.create') }}" class="{{ $base }} {{ request()->routeIs('manager.rooms.create') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span class="menu-label">{{ __('Add New Room') }}</span>
        </a>

        <a href="{{ route('manager.bookings.index') }}" class="{{ $base }} {{ request()->routeIs('manager.bookings.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-6 4h6m-7 8h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            <span class="menu-label">{{ __('Bookings') }}</span>
        </a>

        <a href="{{ route('manager.payments.index') }}" class="{{ $base }} {{ request()->routeIs('manager.payments.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18v10H3V7zm0 3h18" /></svg>
            <span class="menu-label">{{ __('Payments') }}</span>
        </a>

        <a href="{{ route('manager.complaints.index') }}" class="{{ $base }} {{ request()->routeIs('manager.complaints.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8m-8 4h5m7 6l-4-4H6a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v14z" /></svg>
            <span class="menu-label">{{ __('Complaints Queue') }}</span>
        </a>

        <a href="{{ route('notifications.index') }}" class="{{ $base }} {{ request()->routeIs('notifications.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0h6z" /></svg>
            <span class="menu-label">{{ __('Notifications') }}</span>
        </a>

        <a href="{{ route('manager.files.index') }}" class="{{ $base }} {{ request()->routeIs('manager.files.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" /></svg>
            <span class="menu-label">{{ __('File Manager') }}</span>
        </a>

        <a href="{{ route('manager.hostel-change.index') }}" class="{{ $base }} {{ request()->routeIs('manager.hostel-change.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" /></svg>
            <span class="menu-label">{{ __('Hostel Change') }}</span>
        </a>

        <a href="{{ route('manager.room-change.index') }}" class="{{ $base }} {{ request()->routeIs('manager.room-change.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            <span class="menu-label">{{ __('Room Change') }}</span>
        </a>

        @if(\App\Models\Addon::isActive('asset-management'))
            <a href="{{ route('manager.assets.index') }}" class="{{ $base }} {{ request()->routeIs('manager.assets.*') ? $active : $idle }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8" /></svg>
                <span class="menu-label">{{ __('Assets') }}</span>
            </a>
            <a href="{{ route('manager.assets.create') }}" class="{{ $base }} {{ request()->routeIs('manager.assets.create') ? $active : $idle }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span class="menu-label">{{ __('Add Asset') }}</span>
            </a>
        @endif

        @if(\App\Models\Addon::isActive('staff-payroll'))
            <a href="{{ route('manager.staff.index') }}" class="{{ $base }} {{ request()->routeIs('manager.staff.*') ? $active : $idle }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5-3.9M9 20H4v-2a4 4 0 015-3.9m8-6a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span class="menu-label">{{ __('Staff Directory') }}</span>
            </a>
        @endif

        <a href="{{ route('manager.profile.edit') }}" class="{{ $base }} {{ request()->routeIs('manager.profile.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.1 19A9 9 0 1119 5.1M9 12a3 3 0 106 0 3 3 0 00-6 0z" /></svg>
            <span class="menu-label">{{ __('Profile Settings') }}</span>
        </a>
    </div>
</div>
