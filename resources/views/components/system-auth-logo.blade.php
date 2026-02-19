@php
    $logoLight = \App\Models\SystemSetting::getSetting(
        'global_header_logo_light',
        \App\Models\SystemSetting::getSetting(
            'global_header_logo',
            \App\Models\SystemSetting::getSetting('app_logo', '')
        )
    );
    $logoDark = \App\Models\SystemSetting::getSetting('global_header_logo_dark', $logoLight);
    $siteName = \App\Models\SystemSetting::getSetting('site_name', config('app.name', 'Hostel Manager'));

    $toLogoUrl = function (?string $path): string {
        $value = trim((string) $path);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($value, '/'));
    };

    $resolvedLogoLight = $toLogoUrl($logoLight);
    $resolvedLogoDark = $toLogoUrl($logoDark ?: $logoLight);
@endphp

<a href="{{ url('/') }}" class="flex flex-col items-center gap-2">
    @if($resolvedLogoLight)
        <img src="{{ $resolvedLogoLight }}" alt="{{ $siteName }}" class="h-12 w-auto object-contain dark:hidden">
        <img src="{{ $resolvedLogoDark ?: $resolvedLogoLight }}" alt="{{ $siteName }}" class="h-12 w-auto object-contain hidden dark:inline">
    @else
        <span class="text-lg font-semibold text-slate-900 dark:text-white">{{ $siteName }}</span>
    @endif
</a>
