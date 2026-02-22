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

        <a href="{{ route('student.bookings.available') }}" class="{{ $base }} {{ request()->routeIs('student.bookings.available') || request()->routeIs('student.bookings.create') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" /></svg>
            <span class="menu-label">{{ __('Browse Rooms') }}</span>
        </a>

        <a href="{{ route('student.bookings.index') }}" class="{{ $base }} {{ request()->routeIs('student.bookings.index') || request()->routeIs('student.bookings.show') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-6 4h6m-7 8h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            <span class="menu-label">{{ __('My Bookings') }}</span>
        </a>

        <a href="{{ route('student.payments.index') }}" class="{{ $base }} {{ request()->routeIs('student.payments.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18v10H3V7zm0 3h18" /></svg>
            <span class="menu-label">{{ __('Payments') }}</span>
        </a>

        <a href="{{ route('student.complaints.index') }}" class="{{ $base }} {{ request()->routeIs('student.complaints.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8m-8 4h5m7 6l-4-4H6a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v14z" /></svg>
            <span class="menu-label">{{ __('Complaints') }}</span>
        </a>

        <a href="{{ route('notifications.index') }}" class="{{ $base }} {{ request()->routeIs('notifications.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0h6z" /></svg>
            <span class="menu-label">{{ __('Notifications') }}</span>
        </a>

        <a href="{{ route('student.hostel-change.index') }}" class="{{ $base }} {{ request()->routeIs('student.hostel-change.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" /></svg>
            <span class="menu-label">{{ __('Change Hostel') }}</span>
        </a>

        <a href="{{ route('student.room-change.index') }}" class="{{ $base }} {{ request()->routeIs('student.room-change.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            <span class="menu-label">{{ __('Change Room') }}</span>
        </a>

        <a href="{{ route('student.id-card.show') }}" class="{{ $base }} {{ request()->routeIs('student.id-card.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16v10H4V7zm3 3h5m-5 3h3m5-3h2m-2 3h2" /></svg>
            <span class="menu-label">{{ __('My ID Card') }}</span>
        </a>

        @if(\App\Models\Addon::isActive('referral-system') && filter_var(get_setting('referral_enabled', true), FILTER_VALIDATE_BOOL) && filter_var(get_setting('referral_students_can_be_agents', true), FILTER_VALIDATE_BOOL))
            <a href="{{ route('student.referrals.index') }}" class="{{ $base }} {{ request()->routeIs('student.referrals.*') ? $active : $idle }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5-3.9M9 20H4v-2a4 4 0 015-3.9m8-6a3 3 0 11-6 0 3 3 0 016 0zm-8 0a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span class="menu-label">{{ __('Referrals') }}</span>
            </a>
        @endif

        <a href="{{ route('student.profile.edit') }}" class="{{ $base }} {{ request()->routeIs('student.profile.*') ? $active : $idle }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.1 19A9 9 0 1119 5.1M9 12a3 3 0 106 0 3 3 0 00-6 0z" /></svg>
            <span class="menu-label">{{ __('Profile Settings') }}</span>
        </a>
    </div>
</div>
