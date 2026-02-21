@php
    $base = 'menu-card flex items-center gap-3 font-semibold text-base transition-colors';
    $active = 'is-active text-blue-700 dark:text-blue-200';
    $idle = 'text-slate-700 dark:text-slate-200';
    $operationsOpen = request()->routeIs('student.bookings.*')
        || request()->routeIs('student.payments.*')
        || request()->routeIs('student.complaints.*')
        || request()->routeIs('notifications.*');
    $requestsOpen = request()->routeIs('student.hostel-change.*') || request()->routeIs('student.room-change.*');
    $servicesOpen = request()->routeIs('student.id-card.*') || request()->routeIs('student.referrals.*');
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
                <a href="{{ route('student.bookings.available') }}" class="{{ $base }} {{ request()->routeIs('student.bookings.available') || request()->routeIs('student.bookings.create') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Browse Rooms') }}</span>
                </a>
                <a href="{{ route('student.bookings.index') }}" class="{{ $base }} {{ request()->routeIs('student.bookings.index') || request()->routeIs('student.bookings.show') ? $active : $idle }}">
                    <span class="menu-label">{{ __('My Bookings') }}</span>
                </a>
                <a href="{{ route('student.payments.index') }}" class="{{ $base }} {{ request()->routeIs('student.payments.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Payments') }}</span>
                </a>
                <a href="{{ route('student.complaints.index') }}" class="{{ $base }} {{ request()->routeIs('student.complaints.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Complaints') }}</span>
                </a>
                <a href="{{ route('notifications.index') }}" class="{{ $base }} {{ request()->routeIs('notifications.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Notifications') }}</span>
                </a>
            </div>
        </details>

        <details {{ $requestsOpen ? 'open' : '' }} class="group mt-3">
            <summary class="menu-card cursor-pointer list-none {{ $requestsOpen ? 'is-active text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">
                <span class="menu-label">{{ __('Requests') }}</span>
            </summary>
            <div class="mt-2 space-y-2 pl-2">
                <a href="{{ route('student.hostel-change.index') }}" class="{{ $base }} {{ request()->routeIs('student.hostel-change.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Change Hostel') }}</span>
                </a>
                <a href="{{ route('student.room-change.index') }}" class="{{ $base }} {{ request()->routeIs('student.room-change.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Change Room') }}</span>
                </a>
            </div>
        </details>

        <details {{ $servicesOpen ? 'open' : '' }} class="group mt-3">
            <summary class="menu-card cursor-pointer list-none {{ $servicesOpen ? 'is-active text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">
                <span class="menu-label">{{ __('Services') }}</span>
            </summary>
            <div class="mt-2 space-y-2 pl-2">
                <a href="{{ route('student.id-card.show') }}" class="{{ $base }} {{ request()->routeIs('student.id-card.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('My ID Card') }}</span>
                </a>
                @if(\App\Models\Addon::isActive('referral-system') && filter_var(get_setting('referral_enabled', true), FILTER_VALIDATE_BOOL) && filter_var(get_setting('referral_students_can_be_agents', true), FILTER_VALIDATE_BOOL))
                    <a href="{{ route('student.referrals.index') }}" class="{{ $base }} {{ request()->routeIs('student.referrals.*') ? $active : $idle }}">
                        <span class="menu-label">{{ __('Referrals') }}</span>
                    </a>
                @endif
            </div>
        </details>
    </div>

    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/50 p-3">
        <details open class="group">
            <summary class="menu-card cursor-pointer list-none {{ request()->routeIs('student.profile.*') ? 'is-active text-blue-700 dark:text-blue-200' : 'text-slate-700 dark:text-slate-200' }}">
                <span class="menu-label">{{ __('Account') }}</span>
            </summary>
            <div class="mt-2 space-y-2 pl-2">
                <a href="{{ route('student.profile.edit') }}" class="{{ $base }} {{ request()->routeIs('student.profile.*') ? $active : $idle }}">
                    <span class="menu-label">{{ __('Profile Settings') }}</span>
                </a>
            </div>
        </details>
    </div>
</div>
