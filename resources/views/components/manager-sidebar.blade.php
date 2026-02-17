<!-- Manager Sidebar Navigation -->
<div class="pb-6">
    <a href="{{ route('dashboard') }}" 
       class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'dashboard' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l-4-4m0 0l-4 4m4-4v4m8-11l2 1"></path>
        </svg>
        <span class="font-medium">Dashboard</span>
    </a>

    <div class="mt-6 pt-4 border-t border-gray-200">
        <p class="px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider mb-3">Management</p>
        
        <a href="{{ route('manager.students.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'manager.students.index' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12v-2a9 9 0 00-9-9 9 9 0 00-9 9v2h12z"></path>
            </svg>
            <span class="font-medium">Students</span>
        </a>

        <a href="{{ route('manager.rooms.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'manager.rooms.index' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5.581m0 0H9m0 0h5.581M9 3h6"></path>
            </svg>
            <span class="font-medium">Rooms</span>
        </a>

        <a href="{{ route('manager.rooms.create') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-gray-700 hover:bg-gray-100 ml-8 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Add New Room</span>
        </a>

        <a href="{{ route('manager.bookings.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'manager.bookings.index' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="font-medium">Bookings</span>
        </a>

        <a href="{{ route('manager.payments.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'manager.payments.index' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">Payments</span>
        </a>
    </div>

    <div class="mt-6 pt-4 border-t border-gray-200">
        <p class="px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider mb-3">Account</p>
        
        <a href="{{ route('manager.profile.edit') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'manager.profile.edit' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="font-medium">Profile Settings</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-red-700 hover:bg-red-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span class="font-medium">Logout</span>
            </button>
        </form>
    </div>
</div>
