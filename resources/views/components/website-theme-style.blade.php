@php
    $theme = \App\Models\SystemSetting::getSetting('website_theme', 'oceanic');

    $themes = [
        'oceanic' => [
            '--brand' => '#2563eb', '--brand-dark' => '#1d4ed8', '--accent' => '#0ea5e9', '--page-bg' => '#f8fafc', '--hero-a' => '#1d4ed8', '--hero-b' => '#0ea5e9', '--nav-bg' => '#ffffff', '--footer-bg' => '#0f172a',
        ],
        'emerald' => [
            '--brand' => '#059669', '--brand-dark' => '#047857', '--accent' => '#14b8a6', '--page-bg' => '#f7fdfb', '--hero-a' => '#047857', '--hero-b' => '#14b8a6', '--nav-bg' => '#ffffff', '--footer-bg' => '#052e2b',
        ],
        'slate' => [
            '--brand' => '#334155', '--brand-dark' => '#1e293b', '--accent' => '#64748b', '--page-bg' => '#f8fafc', '--hero-a' => '#1e293b', '--hero-b' => '#475569', '--nav-bg' => '#ffffff', '--footer-bg' => '#0b1220',
        ],
        'sunset' => [
            '--brand' => '#ea580c', '--brand-dark' => '#c2410c', '--accent' => '#f59e0b', '--page-bg' => '#fffaf7', '--hero-a' => '#c2410c', '--hero-b' => '#f59e0b', '--nav-bg' => '#ffffff', '--footer-bg' => '#431407',
        ],
        'royal' => [
            '--brand' => '#4f46e5', '--brand-dark' => '#3730a3', '--accent' => '#7c3aed', '--page-bg' => '#f8f8ff', '--hero-a' => '#3730a3', '--hero-b' => '#7c3aed', '--nav-bg' => '#ffffff', '--footer-bg' => '#1e1b4b',
        ],
    ];

    $vars = $themes[$theme] ?? $themes['oceanic'];
@endphp

<style>
:root {
@foreach($vars as $k => $v)
    {{ $k }}: {{ $v }};
@endforeach
}

body { background: var(--page-bg); }
nav.bg-white.shadow-lg { background: var(--nav-bg) !important; }
.site-footer { background: var(--footer-bg) !important; }

.bg-blue-600 { background-color: var(--brand) !important; }
.hover\:bg-blue-700:hover { background-color: var(--brand-dark) !important; }
.text-blue-600, .text-blue-700 { color: var(--brand) !important; }
.border-blue-600 { border-color: var(--brand) !important; }

.bg-gradient-to-r.from-blue-600.to-blue-800 {
    background-image: linear-gradient(to right, var(--hero-a), var(--hero-b)) !important;
}

.bg-gradient-to-br.from-blue-700.via-blue-600.to-blue-800 {
    background-image: linear-gradient(135deg, var(--hero-a), var(--brand), var(--hero-b)) !important;
}
</style>
