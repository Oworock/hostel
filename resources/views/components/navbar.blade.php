@php
    $logo = \App\Models\SystemSetting::getSetting('global_header_logo', '');
    $brandName = \App\Models\SystemSetting::getSetting('global_header_brand', \App\Models\SystemSetting::getSetting('app_name', 'Hostel Manager'));
    $headerNotice = \App\Models\SystemSetting::getSetting('global_header_notice_html', '');
    $headerEmail = \App\Models\SystemSetting::getSetting('global_header_contact_email', '');
    $headerPhone = \App\Models\SystemSetting::getSetting('global_header_contact_phone', '');
@endphp

<nav x-data="{ open: false }" class="bg-white dark:bg-slate-900 shadow-lg border-b border-transparent dark:border-slate-800">
    @if($headerNotice || $headerEmail || $headerPhone)
        <div class="bg-gray-900 dark:bg-slate-950 text-gray-100 text-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
                <div>{!! $headerNotice !!}</div>
                <div class="flex items-center gap-3">
                    @if($headerEmail)<span>{{ $headerEmail }}</span>@endif
                    @if($headerPhone)<span>{{ $headerPhone }}</span>@endif
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <a href="{{ url('/') }}" class="flex items-center space-x-2">
                @if($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="max-h-10 sm:max-h-11 w-auto object-contain">
                @endif
                <span class="text-xl font-bold text-gray-800 dark:text-slate-100">{{ $brandName }}</span>
            </a>

            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('public.rooms.index') }}" class="text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Book Rooms</a>
                @if(auth()->check())
                    @if(auth()->user()->isStudent())
                        <a href="{{ route('student.bookings.available') }}" class="text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Browse Rooms</a>
                        <a href="{{ route('student.bookings.index') }}" class="text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">My Bookings</a>
                    @elseif(auth()->user()->isManager())
                        <a href="{{ route('manager.rooms.index') }}" class="text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Rooms</a>
                        <a href="{{ route('manager.bookings.index') }}" class="text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Bookings</a>
                    @elseif(auth()->user()->isAdmin())
                        <a href="{{ route('filament.admin.resources.hostels.index') }}" class="text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Hostels</a>
                    @endif

                    <a href="{{ route('dashboard') }}" class="text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Dashboard</a>

                    @php
                        $u = auth()->user();
                        $profileRoute = $u->isStudent()
                            ? route('student.profile.edit')
                            : ($u->isManager()
                                ? route('manager.profile.edit')
                                : (\Illuminate\Support\Facades\Route::has('filament.admin.pages.profile') ? route('filament.admin.pages.profile') : route('dashboard')));
                    @endphp
                    <div x-data="{ userMenu: false }" class="relative">
                        <button type="button" class="flex items-center gap-2 rounded-full border border-gray-200 dark:border-slate-700 px-2 py-1 hover:bg-gray-50 dark:hover:bg-slate-800" @click="userMenu = !userMenu">
                            @if($u->profile_image)
                                <img src="{{ asset('storage/' . $u->profile_image) }}" alt="Profile" class="h-8 w-8 rounded-full object-cover">
                            @else
                                <div class="h-8 w-8 rounded-full bg-blue-600 text-white text-xs font-semibold flex items-center justify-center">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                            @endif
                            <span class="text-sm text-gray-700 dark:text-slate-200 max-w-28 truncate">{{ $u->name }}</span>
                        </button>
                        <div x-show="userMenu" x-cloak @click.outside="userMenu = false" class="absolute right-0 mt-2 w-44 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg shadow-lg z-50">
                            <a href="{{ $profileRoute }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-800">Edit Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-slate-800">Sign Out</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Login</a>
                    <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Register</a>
                @endif
            </div>

            <button
                type="button"
                class="md:hidden text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400"
                @click="open = !open"
                :aria-expanded="open.toString()"
                aria-controls="mobile-menu"
            >
                <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" x-show="open" x-cloak x-transition class="md:hidden border-t border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <div class="px-4 py-3 space-y-2">
            @if(auth()->check())
                <a href="{{ route('public.rooms.index') }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Book Rooms</a>
                @if(auth()->user()->isStudent())
                    <a href="{{ route('student.bookings.available') }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Browse Rooms</a>
                    <a href="{{ route('student.bookings.index') }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">My Bookings</a>
                @elseif(auth()->user()->isManager())
                    <a href="{{ route('manager.rooms.index') }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Rooms</a>
                    <a href="{{ route('manager.bookings.index') }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Bookings</a>
                @elseif(auth()->user()->isAdmin())
                    <a href="{{ route('filament.admin.resources.hostels.index') }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Hostels</a>
                @endif

                <a href="{{ route('dashboard') }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Dashboard</a>
                <a href="{{ auth()->user()->isStudent() ? route('student.profile.edit') : (auth()->user()->isManager() ? route('manager.profile.edit') : (\Illuminate\Support\Facades\Route::has('filament.admin.pages.profile') ? route('filament.admin.pages.profile') : route('dashboard'))) }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Edit Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left text-red-700 dark:text-red-300 hover:text-red-800">Sign Out</button>
                </form>
            @else
                <a href="{{ route('public.rooms.index') }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Book Rooms</a>
                <a href="{{ route('login') }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Login</a>
                <a href="{{ route('register') }}" class="block text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">Register</a>
            @endif
        </div>
    </div>
</nav>
