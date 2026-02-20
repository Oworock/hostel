@php
    $base = 'menu-card flex items-center gap-3 font-semibold text-base transition-colors';
    $active = 'is-active text-blue-700 dark:text-blue-200';
    $idle = 'text-slate-700 dark:text-slate-200';
@endphp

<div class="pb-6 space-y-6">
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/50 p-3">
        <a href="{{ route('dashboard') }}" class="{{ $base }} {{ request()->routeIs('dashboard') ? $active : $idle }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l-4-4m0 0l-4 4m4-4v4m8-11l2 1"></path>
            </svg>
            <span class="menu-label">{{ __('Dashboard') }}</span>
        </a>
    </div>

    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/50 p-3">
        <p class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3 menu-section">{{ __('Accommodations') }}</p>

        <a href="{{ route('student.bookings.available') }}" class="{{ $base }} {{ request()->routeIs('student.bookings.available') || request()->routeIs('student.bookings.create') ? $active : $idle }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5.581m0 0H9m0 0h5.581M9 3h6"></path>
            </svg>
            <span class="menu-label">{{ __('Browse Rooms') }}</span>
        </a>

        <a href="{{ route('student.bookings.index') }}" class="{{ $base }} {{ request()->routeIs('student.bookings.index') || request()->routeIs('student.bookings.show') ? $active : $idle }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="menu-label">{{ __('My Bookings') }}</span>
        </a>

        <a href="{{ route('student.payments.index') }}" class="{{ $base }} {{ request()->routeIs('student.payments.*') ? $active : $idle }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="menu-label">{{ __('Payments') }}</span>
        </a>

        <a href="{{ route('student.complaints.index') }}" class="{{ $base }} {{ request()->routeIs('student.complaints.*') ? $active : $idle }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
            </svg>
            <span class="menu-label">{{ __('Complaints') }}</span>
        </a>

        <a href="{{ route('notifications.index') }}" class="{{ $base }} {{ request()->routeIs('notifications.*') ? $active : $idle }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9"></path>
            </svg>
            <span class="menu-label">{{ __('Notifications') }}</span>
        </a>

        <a href="{{ route('student.hostel-change.index') }}" class="{{ $base }} {{ request()->routeIs('student.hostel-change.*') ? $active : $idle }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
            <span class="menu-label">{{ __('Change Hostel') }}</span>
        </a>

        <a href="{{ route('student.room-change.index') }}" class="{{ $base }} {{ request()->routeIs('student.room-change.*') ? $active : $idle }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M4 12h16M8 17h12"></path>
            </svg>
            <span class="menu-label">{{ __('Change Room') }}</span>
        </a>
    </div>

    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/50 p-3">
        <a href="{{ route('student.profile.edit') }}" class="{{ $base }} {{ request()->routeIs('student.profile.*') ? $active : $idle }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="menu-label">{{ __('Profile Settings') }}</span>
        </a>
    </div>
</div>
