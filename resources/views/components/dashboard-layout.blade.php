@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Hostel Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            try {
                var mode = localStorage.getItem('dashboard.themeMode');
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
        .menu-collapsed .sidebar-menu .ml-8 { margin-left: 0 !important; }
        .sidebar-transition { transition: width .2s ease, transform .2s ease; }
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
        .site-footer { display: block !important; visibility: visible !important; width: 100% !important; }
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
    </style>
    @php
        $customCss = \App\Models\SystemSetting::getSetting('custom_css', '');
        $favicon = \App\Models\SystemSetting::getSetting('global_header_favicon', \App\Models\SystemSetting::getSetting('global_header_logo', ''));
    @endphp
    @if(!empty($customCss))
        <style>{!! $customCss !!}</style>
    @endif
    @include('components.website-theme-style')
    @if(!empty($favicon))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $favicon) }}">
    @endif
</head>
<body class="h-full bg-gray-50 dark:bg-slate-950 text-gray-900 dark:text-slate-100 antialiased">
    @php
        $logo = \App\Models\SystemSetting::getSetting('global_header_logo', '');
        $brandName = \App\Models\SystemSetting::getSetting('global_header_brand', \App\Models\SystemSetting::getSetting('app_name', 'Hostel Manager'));
        $headerNotice = \App\Models\SystemSetting::getSetting('global_header_notice_html', '');
        $headerEmail = \App\Models\SystemSetting::getSetting('global_header_contact_email', '');
        $headerPhone = \App\Models\SystemSetting::getSetting('global_header_contact_phone', '');
        $currentUser = auth()->user();
        $profileImage = $currentUser?->profile_image ? asset('storage/' . $currentUser->profile_image) : null;
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

        <header class="bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 sticky top-0 z-40">
            <div class="px-4 sm:px-6 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <button id="sidebar-toggle" type="button" class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-slate-700 p-2 text-gray-700 dark:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 7h16M4 12h16M4 17h16"></path>
                        </svg>
                    </button>
                    <a href="{{ url('/') }}" class="flex items-center gap-3 min-w-0">
                        @if($logo)
                            <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="max-h-9 w-auto object-contain">
                        @elseif($favicon)
                            <img src="{{ asset('storage/' . $favicon) }}" alt="Favicon" class="h-8 w-8 object-contain">
                        @else
                            <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-xs">HMS</span>
                            </div>
                        @endif
                        @if(!$logo)
                            <span class="hidden sm:inline text-base font-bold text-gray-900 dark:text-slate-100 truncate">{{ $brandName }}</span>
                        @endif
                    </a>
                    <span class="hidden md:inline text-sm text-gray-500 dark:text-slate-400 truncate">/ {{ $title }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-xs sm:text-sm text-gray-600 dark:text-slate-300 text-right hidden sm:block">
                        @if(session('impersonator_id'))
                            <p><a href="{{ route('impersonation.leave') }}" class="text-blue-600 hover:text-blue-700 font-semibold">Return to Admin</a></p>
                        @endif
                        @if($headerEmail)<p>{{ $headerEmail }}</p>@endif
                        @if($headerPhone)<p>{{ $headerPhone }}</p>@endif
                    </div>

                    @if($currentUser)
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
                                    <a href="{{ $profileRoute }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-800">Edit Profile</a>
                                @endif
                                <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400 mb-2">Theme</p>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="theme-mode-btn" data-theme-mode="system" title="Use Device Theme" aria-label="Use Device Theme">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="4" width="18" height="12" rx="2"></rect>
                                                <path d="M8 20h8"></path>
                                            </svg>
                                        </button>
                                        <button type="button" class="theme-mode-btn" data-theme-mode="light" title="Light Theme" aria-label="Light Theme">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="4"></circle>
                                                <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path>
                                            </svg>
                                        </button>
                                        <button type="button" class="theme-mode-btn" data-theme-mode="dark" title="Dark Theme" aria-label="Dark Theme">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 1 0 9.8 9.8z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <p id="theme-mode-label" class="mt-2 text-xs text-gray-500 dark:text-slate-400">Following device setting</p>
                                </div>
                                @if(session('impersonator_id'))
                                    <a href="{{ route('impersonation.leave') }}" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-slate-800">Return to Admin</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-slate-800">Sign Out</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($headerNotice)
                <div class="px-4 sm:px-6 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-900 dark:text-blue-100 text-sm">
                    {!! $headerNotice !!}
                </div>
            @endif
        </header>

        <div class="flex flex-1 min-h-0">
            <aside id="sidebar-desktop" class="hidden lg:flex lg:flex-col bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-slate-800 sidebar-transition w-80 xl:w-96">
                <nav class="sidebar-menu px-3 py-4 space-y-2 overflow-y-auto flex-1">
                    @if(isset($sidebar))
                        {{ $sidebar }}
                    @elseif(auth()->user()?->isManager())
                        @include('components.manager-sidebar')
                    @elseif(auth()->user()?->isStudent())
                        @include('components.student-sidebar')
                    @endif
                </nav>
            </aside>

            <aside id="sidebar-mobile" class="lg:hidden fixed top-16 bottom-0 left-0 z-40 w-80 max-w-[90vw] bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-slate-800 shadow-lg -translate-x-full sidebar-transition">
                <nav class="sidebar-menu px-3 py-4 space-y-2 overflow-y-auto h-full pb-16">
                    @if(isset($sidebar))
                        {{ $sidebar }}
                    @elseif(auth()->user()?->isManager())
                        @include('components.manager-sidebar')
                    @elseif(auth()->user()?->isStudent())
                        @include('components.student-sidebar')
                    @endif
                </nav>
            </aside>

            <div class="flex-1 min-w-0 bg-gray-50 dark:bg-slate-950">
                <main class="p-4 sm:p-6 lg:p-8">
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

    <script>
        (function () {
            const shell = document.getElementById('dashboard-shell');
            const desktopSidebar = document.getElementById('sidebar-desktop');
            const mobileSidebar = document.getElementById('sidebar-mobile');
            const overlay = document.getElementById('sidebar-overlay');
            const toggle = document.getElementById('sidebar-toggle');
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenuPanel = document.getElementById('user-menu-panel');
            const themeButtons = Array.from(document.querySelectorAll('[data-theme-mode]'));
            const themeModeLabel = document.getElementById('theme-mode-label');
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

            const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

            function setCollapsed(collapsed) {
                shell.classList.toggle('menu-collapsed', collapsed);
                if (desktopSidebar) {
                    desktopSidebar.classList.toggle('w-80', !collapsed);
                    desktopSidebar.classList.toggle('xl:w-96', !collapsed);
                    desktopSidebar.classList.toggle('w-24', collapsed);
                }
                localStorage.setItem('dashboard.sidebarCollapsed', JSON.stringify(collapsed));
            }

            function getCollapsed() {
                try { return JSON.parse(localStorage.getItem('dashboard.sidebarCollapsed') || 'false') === true; } catch (e) { return false; }
            }

            function openMobileSidebar(open) {
                if (!mobileSidebar || !overlay) return;
                mobileSidebar.classList.toggle('-translate-x-full', !open);
                mobileSidebar.classList.toggle('translate-x-0', open);
                overlay.classList.toggle('hidden', !open);
            }

            function resolveIsDark(mode) {
                return mode === 'dark' || (mode === 'system' && mediaQuery.matches);
            }

            function applyTheme(mode) {
                const root = document.documentElement;
                const useDark = resolveIsDark(mode);
                root.classList.toggle('dark', useDark);
                document.body.classList.toggle('dark', useDark);
            }

            function getThemeMode() {
                const storedMode = localStorage.getItem('dashboard.themeMode');
                if (storedMode === 'light' || storedMode === 'dark' || storedMode === 'system') return storedMode;
                try {
                    const legacyDark = localStorage.getItem('dashboard.darkMode');
                    if (legacyDark !== null) return JSON.parse(legacyDark) === true ? 'dark' : 'light';
                } catch (e) {}
                return 'system';
            }

            function setThemeMode(mode) {
                localStorage.setItem('dashboard.themeMode', mode);
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
            const currentThemeMode = getThemeMode();
            applyTheme(currentThemeMode);
            updateThemeButtons(currentThemeMode);

            toggle?.addEventListener('click', function () {
                if (isDesktop()) {
                    setCollapsed(!shell.classList.contains('menu-collapsed'));
                } else {
                    const isOpen = mobileSidebar && mobileSidebar.classList.contains('translate-x-0');
                    openMobileSidebar(!isOpen);
                }
            });

            overlay?.addEventListener('click', function () { openMobileSidebar(false); });

            userMenuButton?.addEventListener('click', function (e) {
                e.stopPropagation();
                userMenuPanel?.classList.toggle('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!document.getElementById('user-menu-root')?.contains(e.target)) {
                    userMenuPanel?.classList.add('hidden');
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
                if (event.key === 'dashboard.themeMode') {
                    const mode = getThemeMode();
                    applyTheme(mode);
                    updateThemeButtons(mode);
                }
            });

            window.addEventListener('resize', function () {
                if (isDesktop()) openMobileSidebar(false);
            });

            mobileSidebar?.addEventListener('click', function (e) {
                if (e.target.closest('a') || e.target.closest('button[type="submit"]')) {
                    openMobileSidebar(false);
                }
            });
        })();
    </script>
</body>
</html>
