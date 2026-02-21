<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Hostel Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            try {
                var mode = localStorage.getItem('site.themeMode') || localStorage.getItem('dashboard.themeMode');
                if (!mode) {
                    var legacyDark = localStorage.getItem('dashboard.darkMode');
                    mode = legacyDark !== null ? (JSON.parse(legacyDark) ? 'dark' : 'light') : 'system';
                }
                var useDark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', useDark);
            } catch (e) {}
        })();
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @php
        $customCss = \App\Models\SystemSetting::getSetting('custom_css', '');
    @endphp
    @if(!empty($customCss))
        <style>{!! $customCss !!}</style>
    @endif
    @include('components.website-theme-style')
    @php
        $logoLight = \App\Models\SystemSetting::getSetting('global_header_logo_light', \App\Models\SystemSetting::getSetting('global_header_logo', \App\Models\SystemSetting::getSetting('app_logo', '')));
        $favicon = \App\Models\SystemSetting::getSetting('global_header_favicon', $logoLight);
    @endphp
    @if(!empty($favicon))
        @php
            $faviconPath = ltrim((string) $favicon, '/');
            $faviconPath = preg_replace('/^(storage\/|public\/)/', '', $faviconPath);
        @endphp
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $faviconPath) }}">
    @endif
    <style>
        .site-footer { display: block !important; visibility: visible !important; width: 100% !important; }
        .dark body { background-color: rgb(2 6 23) !important; color: rgb(241 245 249) !important; }
        .dark main .bg-white { background-color: rgb(15 23 42) !important; }
        .dark main .bg-gray-50 { background-color: rgb(30 41 59) !important; }
        .dark main .text-gray-900 { color: rgb(241 245 249) !important; }
        .dark main .text-gray-800 { color: rgb(226 232 240) !important; }
        .dark main .text-gray-700 { color: rgb(203 213 225) !important; }
        .dark main .text-gray-600 { color: rgb(148 163 184) !important; }
        .dark main .border-gray-200 { border-color: rgb(51 65 85) !important; }
        .dark main input,
        .dark main select,
        .dark main textarea {
            background-color: rgb(15 23 42) !important;
            color: rgb(241 245 249) !important;
            border-color: rgb(71 85 105) !important;
        }
    </style>
</head>
<body class="min-h-full bg-gray-50 dark:bg-slate-950 text-gray-900 dark:text-slate-100">
    <div class="min-h-screen flex flex-col">
        @include('components.navbar')
        
        <main class="flex-1">
            @if(session('success'))
                @include('components.alert', ['type' => 'success', 'message' => session('success')])
            @endif
            
            @if(session('error'))
                @include('components.alert', ['type' => 'error', 'message' => session('error')])
            @endif
            
            @if($errors->any())
                @include('components.alert', ['type' => 'danger', 'message' => 'Please fix the errors below.'])
            @endif
            
            @yield('content')
        </main>
        
        @include('components.footer')
    </div>
    <script>
        (function () {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            function getMode() {
                return localStorage.getItem('site.themeMode') || localStorage.getItem('dashboard.themeMode') || 'system';
            }
            function applyTheme() {
                const mode = getMode();
                const useDark = mode === 'dark' || (mode === 'system' && mediaQuery.matches);
                document.documentElement.classList.toggle('dark', useDark);
                document.body.classList.toggle('dark', useDark);
            }
            applyTheme();
            if (typeof mediaQuery.addEventListener === 'function') {
                mediaQuery.addEventListener('change', function () { if (getMode() === 'system') applyTheme(); });
            }
            window.addEventListener('storage', function (event) {
                if (event.key === 'site.themeMode' || event.key === 'dashboard.themeMode') {
                    applyTheme();
                }
            });
        })();
    </script>
    <script>
        (function () {
            const COUNTRY_CODES = [
                ['NG', '+234'], ['US', '+1'], ['GB', '+44'], ['CA', '+1'], ['IN', '+91'], ['GH', '+233'],
                ['KE', '+254'], ['ZA', '+27'], ['AE', '+971'], ['SA', '+966'], ['FR', '+33'], ['DE', '+49'],
                ['IT', '+39'], ['ES', '+34'], ['BR', '+55'], ['AU', '+61'], ['JP', '+81'], ['CN', '+86'],
            ];

            function normalizeDial(value) {
                const digits = String(value || '').replace(/[^\d]/g, '');
                return digits ? '+' + digits : '';
            }

            function enhancePhoneInput(input) {
                if (!(input instanceof HTMLInputElement) || input.dataset.phoneIntlEnhanced === '1') {
                    return;
                }
                if (input.closest('.fi-form')) {
                    return;
                }

                const fieldName = input.getAttribute('name') || '';
                if (!fieldName || fieldName.endsWith('_country_code')) {
                    return;
                }

                input.dataset.phoneIntlEnhanced = '1';
                const wrapper = document.createElement('div');
                wrapper.className = 'mt-1 flex items-center gap-2';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);

                const select = document.createElement('select');
                select.className = 'rounded-lg border border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-2 py-2 text-sm';
                COUNTRY_CODES.forEach(([country, dial]) => {
                    const option = document.createElement('option');
                    option.value = dial;
                    option.textContent = country + ' ' + dial;
                    select.appendChild(option);
                });

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = fieldName + '_country_code';

                const parsedDial = (String(input.value || '').match(/^\+[\d]{1,4}/) || [])[0] || '';
                if (parsedDial) {
                    select.value = parsedDial;
                } else {
                    select.value = '+234';
                }

                hidden.value = select.value;
                wrapper.prepend(select);
                input.parentNode.appendChild(hidden);

                select.addEventListener('change', function () {
                    hidden.value = normalizeDial(select.value) || '+234';
                    const raw = String(input.value || '').trim();
                    const stripped = raw.replace(/^\+\d{1,4}\s*/, '');
                    input.value = (hidden.value + ' ' + stripped).trim();
                });

                input.addEventListener('blur', function () {
                    const raw = String(input.value || '').trim();
                    if (raw === '') {
                        return;
                    }
                    if (!raw.startsWith('+')) {
                        input.value = (hidden.value + ' ' + raw).trim();
                    }
                });
            }

            function initIntlPhoneFields(root) {
                const selector = 'input[type="tel"], input[name="phone"], input[name$="_phone"]';
                (root || document).querySelectorAll(selector).forEach(enhancePhoneInput);
            }

            document.addEventListener('DOMContentLoaded', function () {
                initIntlPhoneFields(document);
            });
        })();
    </script>
</body>
</html>
