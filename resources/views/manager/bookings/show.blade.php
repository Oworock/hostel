@extends('layouts.app')

@section('title', 'Booking Details')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Booking #{{ $booking->id }}</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Booking Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Booking Information</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">Student</p>
                    <p class="text-lg font-medium text-gray-900">{{ $booking->user->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="text-lg font-medium text-gray-900">{{ $booking->user->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phone</p>
                    <p class="text-lg font-medium text-gray-900">{{ $booking->user->phone ?? '-' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Room Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Room Information</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">Room Number</p>
                    <p class="text-lg font-medium text-gray-900">{{ $booking->room->room_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Room Type</p>
                    <p class="text-lg font-medium text-gray-900">{{ ucfirst($booking->room->type) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Price Per Month</p>
                    <p class="text-lg font-medium text-gray-900">${{ number_format($booking->room->price_per_month, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking Details -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Booking Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <p class="text-sm text-gray-600">Check-in Date</p>
                <p class="text-lg font-medium text-gray-900">{{ $booking->check_in_date->format('M d, Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Check-out Date</p>
                <p class="text-lg font-medium text-gray-900">{{ $booking->check_out_date?->format('M d, Y') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Amount</p>
                <p class="text-lg font-medium text-gray-900">${{ number_format($booking->total_amount, 2) }}</p>
            </div>
        </div>
        
        <!-- Status -->
        <div class="mb-6">
            <p class="text-sm text-gray-600 mb-2">Status</p>
            <span class="px-4 py-2 rounded-full text-lg font-medium
                @if($booking->status === 'approved') bg-green-100 text-green-800
                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($booking->status) }}
            </span>
        </div>
        
        <!-- Notes -->
        @if($booking->notes)
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-2">Notes</p>
                <p class="text-gray-900">{{ $booking->notes }}</p>
            </div>
        @endif
        
        <!-- Actions -->
        @if($booking->isPending())
            <div class="flex items-center space-x-3 pt-6 border-t">
                <form method="POST" action="{{ route('manager.bookings.approve', $booking) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-medium">
                        Approve
                    </button>
                </form>
                
                <form method="POST" action="{{ route('manager.bookings.reject', $booking) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 font-medium">
                        Reject
                    </button>
                </form>
            </div>
        @elseif($booking->isApproved())
            <div class="flex items-center space-x-3 pt-6 border-t">
                <form method="POST" action="{{ route('manager.bookings.cancel', $booking) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 font-medium" onclick="return confirm('Are you sure?')">
                        Cancel Booking
                    </button>
                </form>
            </div>
        @endif
    </div>
    
    <!-- Back -->
    <div class="mt-8">
        <a href="{{ route('manager.bookings.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
 Back to Bookings            
        </a>
    </div>
</div>
@endsection
