<!-- Student Sidebar Navigation -->
<div class="pb-6">
    <a href="{{ route('dashboard') }}" 
       class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'dashboard' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l-4-4m0 0l-4 4m4-4v4m8-11l2 1"></path>
        </svg>
        <span class="font-medium">Dashboard</span>
    </a>

    <div class="mt-6 pt-4 border-t border-gray-200">
        <p class="px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider mb-3">Accommodations</p>
        
        <a href="{{ route('student.bookings.available') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'student.bookings.available' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5.581m0 0H9m0 0h5.581M9 3h6"></path>
            </svg>
            <span class="font-medium">Browse Rooms</span>
        </a>

        <a href="{{ route('student.bookings.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'student.bookings.index' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="font-medium">My Bookings</span>
        </a>

        <a href="{{ route('student.payments.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'student.payments.index' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">Payments</span>
        </a>

        <a href="{{ route('student.complaints.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'student.complaints.index' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
            </svg>
            <span class="font-medium">Complaints</span>
        </a>
    </div>

    <div class="mt-6 pt-4 border-t border-gray-200">
        <p class="px-4 text-xs font-semibold text-gray-600 uppercase tracking-wider mb-3">Account</p>
        
        <a href="{{ route('student.profile.edit') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ Route::currentRouteName() === 'student.profile.edit' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-gray-100' }}">
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
