@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __($title) }} - {{ __('Hostel Management System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            try {
                var mode = localStorage.getItem('site.themeMode') || localStorage.getItem('dashboard.themeMode');
                if (!mode) {
                    var legacyDark = localStorage.getItem('dashboard.darkMode');
                    if (legacyDark !== null) {
                        mode = (JSON.parse(legacyDark) === true) ? 'dark' : 'light';
                    } else {
                        mode = 'system';
                    }
                }
                var useDark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', useDark);
            } catch (e) {}
        })();
    </script>
    <style>
        .menu-collapsed .menu-label,
        .menu-collapsed .menu-section,
        .menu-collapsed .menu-subitem { display: none !important; }
        .menu-collapsed .sidebar-menu a,
        .menu-collapsed .sidebar-menu button { justify-content: center; }
        .menu-collapsed .sidebar-menu .menu-card svg { display: block !important; opacity: 1 !important; }
        .menu-collapsed .sidebar-menu .menu-card { padding: 0.625rem !important; }
        .menu-collapsed .sidebar-menu .ml-8 { margin-left: 0 !important; }
        .sidebar-transition { transition: width .2s ease, transform .2s ease, opacity .18s ease; }
        #sidebar-desktop { display: none; }
        @media (min-width: 1024px) {
            #sidebar-desktop { display: flex; }
        }
        #sidebar-mobile {
            display: block;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 70;
            width: min(22rem, 92vw);
            opacity: 0;
            pointer-events: none;
            transform: translateX(-105%);
        }
        @media (min-width: 1024px) {
            #sidebar-mobile { display: none !important; }
        }
        html.mobile-sidebar-open #sidebar-mobile {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0) !important;
        }
        #sidebar-overlay {
            opacity: 0;
            pointer-events: none;
            transition: opacity .18s ease;
        }
        html.mobile-sidebar-open #sidebar-overlay {
            opacity: 1;
            pointer-events: auto;
        }
        .sidebar-menu .menu-card {
            border: 1px solid rgb(226 232 240);
            border-radius: 0.75rem;
            margin-bottom: 0.5rem;
            padding: 0.75rem 0.875rem;
            background: #fff;
        }
        .dark .sidebar-menu .menu-card {
            border-color: rgb(51 65 85);
            background: rgb(15 23 42);
        }
        .sidebar-menu .menu-card:hover {
            border-color: rgb(191 219 254);
            background: rgb(248 250 252);
        }
        .dark .sidebar-menu .menu-card:hover {
            border-color: rgb(59 130 246 / 0.6);
            background: rgb(30 41 59);
        }
        .sidebar-menu .menu-card.is-active {
            background: rgb(239 246 255);
            border-color: rgb(147 197 253);
            box-shadow: inset 0 0 0 1px rgb(191 219 254 / .5);
        }
        .dark .sidebar-menu .menu-card.is-active {
            background: rgb(30 58 138 / .25);
            border-color: rgb(59 130 246 / .5);
            box-shadow: inset 0 0 0 1px rgb(59 130 246 / .35);
        }
        .sidebar-menu details > summary::-webkit-details-marker { display: none; }
        .sidebar-menu details > summary { position: relative; }
        .sidebar-menu details > summary::after {
            content: '+';
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            font-weight: 700;
            opacity: 0.7;
        }
        .sidebar-menu details[open] > summary::after { content: '-'; }
        .site-footer { display: block !important; visibility: visible !important; width: 100% !important; }
        .dashboard-sidebar-scroll {
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: rgb(148 163 184 / .55) transparent;
        }
        .dashboard-sidebar-scroll::-webkit-scrollbar { width: 8px; }
        .dashboard-sidebar-scroll::-webkit-scrollbar-thumb { background: rgb(148 163 184 / .55); border-radius: 999px; }
        .menu-collapsed .menu-section-title { display: none !important; }
        .uniform-page {
            max-width: 80rem;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .uniform-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .uniform-header > p {
            flex-basis: 100%;
            margin-top: 0;
        }
        .theme-mode-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            border: 1px solid rgb(226 232 240);
            color: rgb(71 85 105);
            background: #fff;
            transition: all .2s ease;
        }
        .theme-mode-btn:hover {
            border-color: rgb(147 197 253);
            color: rgb(37 99 235);
            background: rgb(239 246 255);
        }
        .theme-mode-btn.is-active {
            border-color: rgb(59 130 246);
            color: rgb(29 78 216);
            background: rgb(219 234 254);
        }
        .dark .theme-mode-btn {
            border-color: rgb(51 65 85);
            color: rgb(148 163 184);
            background: rgb(15 23 42);
        }
        .dark .theme-mode-btn:hover {
            border-color: rgb(59 130 246 / 0.7);
            color: rgb(147 197 253);
            background: rgb(30 58 138 / 0.2);
        }
        .dark .theme-mode-btn.is-active {
            border-color: rgb(96 165 250);
            color: rgb(191 219 254);
            background: rgb(30 58 138 / 0.35);
        }
        .uniform-grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        .uniform-grid-3 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        .uniform-grid-4 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .uniform-card {
            background: #fff;
            border: 1px solid rgb(226 232 240);
            border-radius: 0.75rem;
            box-shadow: 0 6px 20px rgb(15 23 42 / 0.06);
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .uniform-card:hover { box-shadow: 0 12px 28px rgb(15 23 42 / 0.12); }
        .dark .uniform-card {
            background: rgb(15 23 42);
            border-color: rgb(51 65 85);
            box-shadow: none;
        }
        .dark .uniform-card:hover { box-shadow: none; }
        .dark main .bg-white { background-color: rgb(15 23 42) !important; }
        .dark main .bg-gray-50 { background-color: rgb(30 41 59) !important; }
        .dark main .bg-gray-100 { background-color: rgb(51 65 85) !important; }
        .dark main .text-gray-900 { color: rgb(241 245 249) !important; }
        .dark main .text-gray-800 { color: rgb(226 232 240) !important; }
        .dark main .text-gray-700 { color: rgb(203 213 225) !important; }
        .dark main .text-gray-600 { color: rgb(148 163 184) !important; }
        .dark main .text-gray-500 { color: rgb(148 163 184) !important; }
        .dark main .border-gray-200 { border-color: rgb(51 65 85) !important; }
        .dark main .border-gray-300 { border-color: rgb(71 85 105) !important; }
        .dark main input,
        .dark main select,
        .dark main textarea {
            background-color: rgb(15 23 42) !important;
            color: rgb(241 245 249) !important;
            border-color: rgb(71 85 105) !important;
        }
        .dark main input::placeholder,
        .dark main textarea::placeholder {
            color: rgb(148 163 184) !important;
        }
        .dark main table thead {
            background-color: rgb(30 41 59) !important;
        }
        .dark main table tbody tr:hover {
            background-color: rgb(30 41 59) !important;
        }
        .uniform-page a.bg-blue-600,
        .uniform-page button.bg-blue-600,
        main a.bg-blue-600,
        main button.bg-blue-600 {
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem !important;
            font-weight: 500 !important;
            color: #fff !important;
        }
        .uniform-page a.bg-blue-600:hover,
        .uniform-page button.bg-blue-600:hover,
        main a.bg-blue-600:hover,
        main button.bg-blue-600:hover {
            background-color: rgb(29 78 216) !important;
        }
        @media (min-width: 768px) {
            .uniform-grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .uniform-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .uniform-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .uniform-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .uniform-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        @media (max-width: 375px) {
            #sidebar-mobile { width: min(18.5rem, 95vw); }
            .sidebar-brand { padding: 0.625rem 0.75rem !important; }
            .sidebar-menu { padding: 0.5rem !important; }
            .sidebar-menu .menu-card {
                padding: 0.55rem 0.65rem;
                margin-bottom: 0.35rem;
                border-radius: 0.625rem;
            }
            .sidebar-menu .menu-card svg { width: 1.1rem; height: 1.1rem; }
            .uniform-page { gap: 1rem; }
        }
    </style>
    @php
        $customCss = \App\Models\SystemSetting::getSetting('custom_css', '');
        $logoLight = \App\Models\SystemSetting::getSetting('global_header_logo_light', \App\Models\SystemSetting::getSetting('global_header_logo', \App\Models\SystemSetting::getSetting('app_logo', '')));
        $logoDark = \App\Models\SystemSetting::getSetting('global_header_logo_dark', $logoLight);
        $favicon = \App\Models\SystemSetting::getSetting('global_header_favicon', $logoLight);
    @endphp
    @if(!empty($customCss))
        <style>{!! $customCss !!}</style>
    @endif
    @include('components.website-theme-style')
    @if(!empty($favicon))
        @php
            $faviconPath = ltrim((string) $favicon, '/');
            $faviconPath = preg_replace('/^(storage\/|public\/)/', '', $faviconPath);
        @endphp
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $faviconPath) }}">
    @endif
</head>
<body class="h-full bg-gray-50 dark:bg-slate-950 text-gray-900 dark:text-slate-100 antialiased">
    @php
        $logoLight = \App\Models\SystemSetting::getSetting('global_header_logo_light', \App\Models\SystemSetting::getSetting('global_header_logo', \App\Models\SystemSetting::getSetting('app_logo', '')));
        $logoDark = \App\Models\SystemSetting::getSetting('global_header_logo_dark', $logoLight);
        $logo = $logoLight;
        $brandName = \App\Models\SystemSetting::getSetting('global_header_brand', \App\Models\SystemSetting::getSetting('app_name', 'Hostel Manager'));
        $headerNotice = \App\Models\SystemSetting::getSetting('global_header_notice_html', '');
        $headerEmail = \App\Models\SystemSetting::getSetting('global_header_contact_email', '');
        $headerPhone = \App\Models\SystemSetting::getSetting('global_header_contact_phone', '');
        $toLogoUrl = function (?string $path): string {
            $path = trim((string) $path);
            if ($path === '') {
                return '';
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
                return $path;
            }

            $path = ltrim($path, '/');
            $path = preg_replace('/^(storage\/|public\/)/', '', $path);
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return '';
            }

            return asset('storage/' . $path);
        };
        $resolvedLogoLight = $toLogoUrl($logoLight);
        $resolvedLogoDark = $toLogoUrl($logoDark ?: $logoLight);
        $resolvedFavicon = $toLogoUrl($favicon);
        $currentUser = auth()->user();
        $loginPopup = null;
        if ($currentUser && ($currentUser->isStudent() || $currentUser->isManager())) {
            $roleTarget = $currentUser->isStudent() ? 'students' : 'managers';
            $dismissedPopupIds = collect(session('dismissed_popup_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->values()
                ->all();
            $loginPopup = \App\Models\PopupAnnouncement::query()
                ->currentlyActive()
                ->whereIn('target', [$roleTarget, 'both'])
                ->whereDoesntHave('seenByUsers', fn ($q) => $q->where('users.id', $currentUser->id))
                ->when(!empty($dismissedPopupIds), fn ($q) => $q->whereNotIn('id', $dismissedPopupIds))
                ->latest('id')
                ->first();
        }
        $profileImage = $currentUser?->profile_image ? asset('storage/' . $currentUser->profile_image) : null;
        $latestNotifications = $currentUser?->notifications()?->latest()->limit(8)->get() ?? collect();
        $unreadNotificationCount = $currentUser?->unreadNotifications()?->count() ?? 0;
        $profileRoute = null;
        if ($currentUser?->isStudent()) {
            $profileRoute = route('student.profile.edit');
        } elseif ($currentUser?->isManager()) {
            $profileRoute = route('manager.profile.edit');
        } elseif ($currentUser?->isAdmin() && \Illuminate\Support\Facades\Route::has('filament.admin.pages.profile')) {
            $profileRoute = route('filament.admin.pages.profile');
        }
    @endphp

        <div id="dashboard-shell" class="min-h-screen flex flex-col overflow-x-hidden">
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden"></div>

        <div class="sticky top-0 z-50">
            @if($headerNotice || $headerEmail || $headerPhone)
                <div class="bg-gray-900 dark:bg-slate-950 text-gray-100 text-xs">
                    <div class="px-4 sm:px-6 py-2 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
                        <div>{!! $headerNotice !!}</div>
                        <div class="flex items-center gap-3">
                            @if($headerEmail)<span>{{ $headerEmail }}</span>@endif
                            @if($headerPhone)<span>{{ $headerPhone }}</span>@endif
                        </div>
                    </div>
                </div>
            @endif

            <header class="bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 relative z-30">
            <div class="px-4 sm:px-6 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 min-w-0">
                        @if($resolvedLogoLight)
                            <img src="{{ $resolvedLogoLight }}" alt="Logo" class="h-8 w-auto object-contain dark:hidden">
                            <img src="{{ $resolvedLogoDark ?: $resolvedLogoLight }}" alt="Logo" class="h-8 w-auto object-contain hidden dark:inline">
                        @elseif($resolvedFavicon)
                            <img src="{{ $resolvedFavicon }}" alt="Favicon" class="h-8 w-8 object-contain">
                        @else
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xs">HMS</div>
                        @endif
                        <span class="text-sm font-semibold text-gray-700 dark:text-slate-200 truncate max-w-[11rem] sm:max-w-[14rem]">{{ $brandName }}</span>
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <button data-sidebar-toggle type="button" class="lg:hidden inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-slate-700 bg-white/95 dark:bg-slate-900/95 p-2 text-gray-700 dark:text-slate-200 shadow hover:bg-gray-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 7h16M4 12h16M4 17h16"></path>
                        </svg>
                    </button>

                    <div class="text-xs sm:text-sm text-gray-600 dark:text-slate-300 text-right hidden sm:block">
                        @if(session('impersonator_id'))
                            <p><a href="{{ route('impersonation.leave') }}" class="text-blue-600 hover:text-blue-700 font-semibold">{{ __('Return to Admin') }}</a></p>
                        @endif
                    </div>

                    @if($currentUser)
                        <div class="relative" id="notification-menu-root">
                            <button id="notification-menu-button" type="button" class="relative inline-flex items-center justify-center rounded-full border border-gray-300 dark:border-slate-700 p-2 hover:bg-gray-100 dark:hover:bg-slate-800">
                                <svg class="w-5 h-5 text-gray-700 dark:text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9"></path>
                                </svg>
                                @if($unreadNotificationCount > 0)
                                    <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[1.1rem] h-[1.1rem] text-[10px] font-bold rounded-full bg-red-600 text-white px-1">
                                        {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                    </span>
                                @endif
                            </button>

                            <div id="notification-menu-panel" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg shadow-lg z-50">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-slate-100">{{ __('Notifications') }}</p>
                                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 dark:text-blue-300 hover:underline">{{ __('Mark all read') }}</button>
                                    </form>
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    @forelse($latestNotifications as $notification)
                                        <div class="px-4 py-3 border-b border-gray-100 dark:border-slate-800 {{ $notification->read_at ? '' : 'bg-blue-50/70 dark:bg-blue-900/10' }}">
                                            <p class="text-sm font-semibold text-gray-800 dark:text-slate-100">{{ $notification->data['title'] ?? __('Notification') }}</p>
                                            <p class="text-xs text-gray-600 dark:text-slate-300 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                                            <div class="mt-2 flex items-center justify-between">
                                                <p class="text-[11px] text-gray-500 dark:text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                                                @if(!$notification->read_at)
                                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                                        @csrf
                                                        <button type="submit" class="text-[11px] text-blue-600 dark:text-blue-300 hover:underline">{{ __('Mark read') }}</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="px-4 py-6 text-sm text-gray-500 dark:text-slate-400">{{ __('No notifications yet.') }}</p>
                                    @endforelse
                                </div>
                                <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-sm text-center text-blue-600 dark:text-blue-300 hover:bg-gray-50 dark:hover:bg-slate-800">{{ __('View all notifications') }}</a>
                            </div>
                        </div>

                        <div class="relative" id="user-menu-root">
                            <button id="user-menu-button" type="button" class="flex items-center gap-2 rounded-full border border-gray-300 dark:border-slate-700 px-2 py-1 hover:bg-gray-100 dark:hover:bg-slate-800">
                                @if($profileImage)
                                    <img src="{{ $profileImage }}" alt="Profile" class="h-8 w-8 rounded-full object-cover">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-blue-600 text-white text-xs font-semibold flex items-center justify-center">
                                        {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="hidden sm:inline text-sm text-gray-700 dark:text-slate-200 max-w-32 truncate font-semibold">{{ $currentUser->name }}</span>
                            </button>

                            <div id="user-menu-panel" class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg shadow-lg z-50">
                                @if($profileRoute)
                                    <a href="{{ $profileRoute }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-800">{{ __('Edit Profile') }}</a>
                                @endif
                                <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400 mb-2">Theme</p>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="theme-mode-btn" data-theme-mode="system" title="{{ __('Use Device Theme') }}" aria-label="{{ __('Use Device Theme') }}">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="4" width="18" height="12" rx="2"></rect>
                                                <path d="M8 20h8"></path>
                                            </svg>
                                        </button>
                                        <button type="button" class="theme-mode-btn" data-theme-mode="light" title="{{ __('Light Theme') }}" aria-label="{{ __('Light Theme') }}">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="4"></circle>
                                                <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path>
                                            </svg>
                                        </button>
                                        <button type="button" class="theme-mode-btn" data-theme-mode="dark" title="{{ __('Dark Theme') }}" aria-label="{{ __('Dark Theme') }}">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 1 0 9.8 9.8z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <p id="theme-mode-label" class="mt-2 text-xs text-gray-500 dark:text-slate-400">{{ __('Following device setting') }}</p>
                                </div>
                                @if(session('impersonator_id'))
                                    <a href="{{ route('impersonation.leave') }}" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-slate-800">{{ __('Return to Admin') }}</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-slate-800">{{ __('Sign Out') }}</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            </header>
        </div>

        <div class="flex flex-1 min-h-0">
            <aside id="sidebar-desktop" class="lg:flex lg:flex-col bg-transparent sidebar-transition w-80 xl:w-96 lg:sticky lg:top-0 lg:self-start lg:h-screen p-3">
                <div class="rounded-2xl border border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden flex flex-col h-full min-h-0">
                <div class="sidebar-brand px-3 py-3 border-b border-gray-200 dark:border-slate-800 flex items-center gap-2">
                    <div class="menu-section-title text-sm font-semibold text-slate-700 dark:text-slate-200 truncate">{{ __('Navigation Menu') }}</div>
                    <button data-sidebar-toggle type="button" class="ml-auto inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-slate-700 p-2 text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 7h16M4 12h16M4 17h16"></path>
                        </svg>
                    </button>
                </div>
                <nav class="sidebar-menu dashboard-sidebar-scroll px-3 py-4 space-y-2 flex-1 min-h-0 max-h-full">
                    @if(isset($sidebar))
                        {{ $sidebar }}
                    @elseif(auth()->user()?->isManager())
                        @include('components.manager-sidebar')
                    @elseif(auth()->user()?->isStudent())
                        @include('components.student-sidebar')
                    @endif
                </nav>
                </div>
            </aside>

            <aside id="sidebar-mobile" class="lg:hidden bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-slate-800 shadow-lg sidebar-transition z-[70]">
                <div class="h-16 border-b border-gray-200 dark:border-slate-800 flex items-center px-4">
                    <span class="font-semibold text-gray-900 dark:text-slate-100">Menu</span>
                    <button data-sidebar-toggle type="button" class="ml-auto inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-slate-700 p-2 text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 7h16M4 12h16M4 17h16"></path>
                        </svg>
                    </button>
                </div>
                <nav class="sidebar-menu dashboard-sidebar-scroll px-3 py-4 space-y-2 overflow-y-auto h-[calc(100%-4rem)] pb-16">
                    @if(isset($sidebar))
                        {{ $sidebar }}
                    @elseif(auth()->user()?->isManager())
                        @include('components.manager-sidebar')
                    @elseif(auth()->user()?->isStudent())
                        @include('components.student-sidebar')
                    @endif
                </nav>
            </aside>

            <div class="flex-1 min-w-0 min-h-0 bg-gray-50 dark:bg-slate-950 flex flex-col">
                <main class="p-3 sm:p-6 lg:p-8 flex-1 min-h-0">
                    @if(session('success'))
                        @include('components.alert', ['type' => 'success', 'message' => session('success')])
                    @endif
                    @if(session('error'))
                        @include('components.alert', ['type' => 'error', 'message' => session('error')])
                    @endif
                    @if($errors->any())
                        @include('components.alert', ['type' => 'danger', 'message' => 'Please fix the errors below.'])
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        @include('components.footer')
    </div>

    @if($loginPopup)
        <div id="login-popup-modal" class="fixed bottom-4 right-4 z-[90] w-[calc(100%-2rem)] max-w-sm">
            <div class="rounded-xl bg-white/95 dark:bg-slate-900/95 border border-gray-200 dark:border-slate-700 shadow-2xl backdrop-blur-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-slate-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-100 truncate">{{ $loginPopup->title }}</h3>
                </div>
                <div class="px-4 py-3 text-xs text-gray-700 dark:text-slate-200 whitespace-pre-line max-h-40 overflow-y-auto">{{ $loginPopup->body }}</div>
                <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700 flex justify-end">
                    <button
                        type="button"
                        id="login-popup-dismiss"
                        data-dismiss-url="{{ route('notifications.popup.dismiss', $loginPopup) }}"
                        class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded-md hover:bg-blue-700"
                    >
                        {{ __('Dismiss') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        (function () {
            const shell = document.getElementById('dashboard-shell');
            const desktopSidebar = document.getElementById('sidebar-desktop');
            const mobileSidebar = document.getElementById('sidebar-mobile');
            const overlay = document.getElementById('sidebar-overlay');
            const toggles = Array.from(document.querySelectorAll('[data-sidebar-toggle]'));
            const notificationMenuButton = document.getElementById('notification-menu-button');
            const notificationMenuPanel = document.getElementById('notification-menu-panel');
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenuPanel = document.getElementById('user-menu-panel');
            const themeButtons = Array.from(document.querySelectorAll('[data-theme-mode]'));
            const themeModeLabel = document.getElementById('theme-mode-label');
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            const storage = {
                get(key) {
                    try { return window.localStorage.getItem(key); } catch (e) { return null; }
                },
                set(key, value) {
                    try { window.localStorage.setItem(key, value); } catch (e) {}
                },
            };

            const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

            function setCollapsed(collapsed) {
                shell.classList.toggle('menu-collapsed', collapsed);
                if (desktopSidebar) {
                    desktopSidebar.classList.toggle('w-80', !collapsed);
                    desktopSidebar.classList.toggle('xl:w-96', !collapsed);
                    desktopSidebar.classList.toggle('w-28', collapsed);
                }
                storage.set('dashboard.sidebarCollapsed', JSON.stringify(collapsed));
            }

            function getCollapsed() {
                try { return JSON.parse(storage.get('dashboard.sidebarCollapsed') || 'false') === true; } catch (e) { return false; }
            }

            function openMobileSidebar(open) {
                if (!mobileSidebar || !overlay) return;
                document.documentElement.classList.toggle('mobile-sidebar-open', open);
                mobileSidebar.classList.toggle('is-open', open);
                mobileSidebar.style.setProperty('display', 'block', 'important');
                mobileSidebar.style.setProperty('transform', open ? 'translateX(0)' : 'translateX(-105%)', 'important');
                mobileSidebar.style.setProperty('opacity', open ? '1' : '0', 'important');
                mobileSidebar.style.setProperty('pointer-events', open ? 'auto' : 'none', 'important');
                overlay.classList.toggle('hidden', !open);
                overlay.style.setProperty('display', open ? 'block' : 'none', 'important');
                document.body.classList.toggle('overflow-hidden', open);
                toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', open ? 'true' : 'false'));
            }

            function applyTheme(mode) {
                const root = document.documentElement;
                const useDark = mode === 'dark' || (mode === 'system' && mediaQuery.matches);
                root.classList.toggle('dark', useDark);
                document.body.classList.toggle('dark', useDark);
            }

            function getThemeMode() {
                const storedMode = storage.get('site.themeMode') || storage.get('dashboard.themeMode');
                if (storedMode === 'light' || storedMode === 'dark' || storedMode === 'system') {
                    return storedMode;
                }
                try {
                    const legacyDark = storage.get('dashboard.darkMode');
                    if (legacyDark !== null) return JSON.parse(legacyDark) === true ? 'dark' : 'light';
                } catch (e) {}
                return 'system';
            }

            function setThemeMode(mode) {
                storage.set('site.themeMode', mode);
                storage.set('dashboard.themeMode', mode);
                applyTheme(mode);
                updateThemeButtons(mode);
            }

            function updateThemeButtons(mode) {
                const labelMap = {
                    system: 'Following device setting',
                    light: 'Light mode selected',
                    dark: 'Dark mode selected',
                };
                themeButtons.forEach((button) => {
                    const active = button.getAttribute('data-theme-mode') === mode;
                    button.classList.toggle('is-active', active);
                });
                if (themeModeLabel) {
                    themeModeLabel.textContent = labelMap[mode] || labelMap.system;
                }
            }

            setCollapsed(getCollapsed());
            openMobileSidebar(false);
            const currentThemeMode = getThemeMode();
            applyTheme(currentThemeMode);
            updateThemeButtons(currentThemeMode);

            toggles.forEach((toggle) => {
                const onToggle = function () {
                    if (isDesktop()) {
                        setCollapsed(!shell.classList.contains('menu-collapsed'));
                    } else {
                        const isOpen = document.documentElement.classList.contains('mobile-sidebar-open');
                        openMobileSidebar(!isOpen);
                    }
                };
                toggle.addEventListener('click', onToggle);
            });

            overlay?.addEventListener('click', function () { openMobileSidebar(false); });

            userMenuButton?.addEventListener('click', function (e) {
                e.stopPropagation();
                userMenuPanel?.classList.toggle('hidden');
            });

            notificationMenuButton?.addEventListener('click', function (e) {
                e.stopPropagation();
                notificationMenuPanel?.classList.toggle('hidden');
                userMenuPanel?.classList.add('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!document.getElementById('user-menu-root')?.contains(e.target)) {
                    userMenuPanel?.classList.add('hidden');
                }
                if (!document.getElementById('notification-menu-root')?.contains(e.target)) {
                    notificationMenuPanel?.classList.add('hidden');
                }
            });

            themeButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    const mode = button.getAttribute('data-theme-mode');
                    if (mode === 'light' || mode === 'dark' || mode === 'system') {
                        setThemeMode(mode);
                    }
                });
            });

            const onSystemThemeChange = function () {
                if (getThemeMode() === 'system') {
                    applyTheme('system');
                }
            };
            if (typeof mediaQuery.addEventListener === 'function') {
                mediaQuery.addEventListener('change', onSystemThemeChange);
            } else if (typeof mediaQuery.addListener === 'function') {
                mediaQuery.addListener(onSystemThemeChange);
            }

            window.addEventListener('storage', function (event) {
                if (event.key === 'dashboard.themeMode' || event.key === 'site.themeMode') {
                    const mode = getThemeMode();
                    applyTheme(mode);
                    updateThemeButtons(mode);
                }
            });

            window.addEventListener('resize', function () {
                if (isDesktop()) openMobileSidebar(false);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    openMobileSidebar(false);
                }
            });

            mobileSidebar?.addEventListener('click', function (e) {
                if (e.target.closest('a') || e.target.closest('button[type="submit"]')) {
                    openMobileSidebar(false);
                }
            });

            const popupDismissBtn = document.getElementById('login-popup-dismiss');
            popupDismissBtn?.addEventListener('click', async function () {
                const modal = document.getElementById('login-popup-modal');
                const url = popupDismissBtn.getAttribute('data-dismiss-url');
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf || '',
                            'Accept': 'application/json',
                        },
                    });
                } catch (e) {
                    // Ignore dismiss network failure; close popup for current view.
                }

                modal?.remove();
            });
        })();
    </script>
</body>
</html>
