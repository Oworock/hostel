<x-dashboard-layout title="Student Dashboard">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>
    
    <div class="max-w-6xl">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">My Dashboard</h1>
    
    <!-- Profile Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">My Profile</h2>
            <a href="{{ route('student.profile.edit') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">
                Edit Profile
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Profile Picture -->
            <div class="flex flex-col items-center">
                @if(auth()->user()->profile_image)
                    <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" alt="Profile" class="w-32 h-32 rounded-full object-cover border-4 border-blue-200 mb-4">
                    <p class="text-sm text-green-600 font-medium">✓ Profile picture uploaded</p>
                @else
                    <div class="w-32 h-32 rounded-full bg-gray-300 flex items-center justify-center mb-4 border-4 border-yellow-200">
                        <span class="text-gray-600 text-sm">No Picture</span>
                    </div>
                    <p class="text-sm text-yellow-600 font-medium">⚠️ Profile picture required to book</p>
                @endif
            </div>
            
            <!-- Profile Information -->
            <div class="md:col-span-2 space-y-4">
                <div>
                    <p class="text-sm text-gray-600">Full Name</p>
                    <p class="text-lg font-medium text-gray-900">{{ auth()->user()->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="text-lg font-medium text-gray-900">{{ auth()->user()->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phone</p>
                    <p class="text-lg font-medium text-gray-900">{{ auth()->user()->phone ?? 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Admission Number</p>
                    <p class="text-lg font-medium text-gray-900">{{ auth()->user()->admission_number ?? 'Not provided' }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Active Booking</p>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['active_booking'] ? 'Yes' : 'No' }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Pending Bookings</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending_bookings'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Completed Bookings</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['completed_bookings'] }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Current Booking -->
    @if($currentBooking)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Your Current Booking</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-gray-600 text-sm">Room</p>
                    <p class="text-lg font-bold text-gray-900">{{ $currentBooking->room->room_number }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Check-in Date</p>
                    <p class="text-lg font-bold text-gray-900">{{ $currentBooking->check_in_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Status</p>
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        @if($currentBooking->status === 'approved') bg-green-100 text-green-800
                        @elseif($currentBooking->status === 'pending') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($currentBooking->status) }}
                    </span>
                </div>
            </div>
        </div>
    @else
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">No Active Booking</h2>
            <p class="text-gray-600 mb-4">You don't currently have an active booking.</p>
            <a href="{{ route('student.bookings.available') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
                Browse Available Rooms
            </a>
        </div>
    @endif
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">My Bookings</h2>
            <a href="{{ route('student.bookings.index') }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-medium">
                View All Bookings
            </a>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Browse Rooms</h2>
            <a href="{{ route('student.bookings.available') }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-medium">
                Browse Available Rooms
            </a>
        </div>
    </div>
</x-dashboard-layout>
