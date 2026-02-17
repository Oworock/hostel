@php
    $appName = \App\Models\SystemSetting::getSetting('app_name', 'Hostel Manager');
    $footerTitle = \App\Models\SystemSetting::getSetting('global_footer_title', $appName);
    $footerDescription = \App\Models\SystemSetting::getSetting('global_footer_description_html', 'Professional hostel management system for seamless room booking.');
    $footerEmail = \App\Models\SystemSetting::getSetting('global_footer_contact_email', 'info@hostelmanager.com');
    $footerPhone = \App\Models\SystemSetting::getSetting('global_footer_contact_phone', '+1 (555) 123-4567');
    $footerCopyright = \App\Models\SystemSetting::getSetting('global_footer_copyright_html', '&copy; ' . date('Y') . ' ' . $appName . '. All rights reserved.');
@endphp

<footer class="site-footer relative z-20 block w-full bg-slate-900 text-white mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-lg font-bold mb-4">{{ $footerTitle }}</h3>
                <div class="text-slate-300">{!! $footerDescription !!}</div>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-slate-300">
                    <li><a href="{{ url('/') }}" class="hover:text-white">Home</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white">Login</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white">Register</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Contact</h3>
                <p class="text-slate-300">
                    Email: {{ $footerEmail }}<br>
                    Phone: {{ $footerPhone }}
                </p>
            </div>
        </div>
        <div class="border-t border-slate-700 mt-8 pt-8 text-center text-slate-300">
            <p>{!! $footerCopyright !!}</p>
        </div>
    </div>
</footer>
