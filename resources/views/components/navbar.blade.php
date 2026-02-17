<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex items-center space-x-2">
                    @php
                        $logo = \App\Models\SystemSetting::getSetting('app_logo', '');
                    @endphp
                    @if($logo)
                        <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="h-10 w-auto">
                    @endif
                    <span class="text-xl font-bold text-gray-800">Hostel Manager</span>
                </a>
            </div>
            
            <div class="hidden md:flex items-center space-x-8">
                @if(auth()->check())
                    @if(auth()->user()->isStudent())
                        <a href="{{ route('student.bookings.available') }}" class="text-gray-700 hover:text-blue-600">Browse Rooms</a>
                        <a href="{{ route('student.bookings.index') }}" class="text-gray-700 hover:text-blue-600">My Bookings</a>
                    @elseif(auth()->user()->isManager())
                        <a href="{{ route('manager.rooms.index') }}" class="text-gray-700 hover:text-blue-600">Rooms</a>
                        <a href="{{ route('manager.bookings.index') }}" class="text-gray-700 hover:text-blue-600">Bookings</a>
                    @elseif(auth()->user()->isAdmin())
                        <a href="{{ route('filament.admin.resources.hostels.index') }}" class="text-gray-700 hover:text-blue-600">Hostels</a>
                    @endif
                    
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                    
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-700 hover:text-red-600">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600">Login</a>
                    <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Register</a>
                @endif
            </div>
            
            <div class="md:hidden">
                <button id="menu-toggle" class="text-gray-700 hover:text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>
