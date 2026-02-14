@extends('layouts.app')

@section('title', 'My Bookings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-gray-900">My Bookings</h1>
        <a href="{{ route('student.bookings.available') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
            Browse Rooms
        </a>
    </div>
    
    <div class="grid grid-cols-1 gap-6">
        @forelse($bookings as $booking)
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $booking->room->room_number }}</h2>
                        <p class="text-gray-600">{{ $booking->room->hostel->name }}</p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-medium
                        @if($booking->status === 'approved') bg-green-100 text-green-800
                        @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                        @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 py-4 border-y">
                    <div>
                        <p class="text-sm text-gray-600">Check-in</p>
                        <p class="font-medium text-gray-900">{{ $booking->check_in_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Check-out</p>
                        <p class="font-medium text-gray-900">{{ $booking->check_out_date?->format('M d, Y') ?? 'TBD' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Room Type</p>
                        <p class="font-medium text-gray-900">{{ ucfirst($booking->room->type) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Amount</p>
                        <p class="font-medium text-gray-900">${{ number_format($booking->total_amount, 2) }}</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Booked: {{ $booking->created_at->format('M d, Y') }}
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('student.bookings.show', $booking) }}" class="bg-blue-100 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-200 font-medium">
                            View
                        </a>
                        @if($booking->status === 'pending' || $booking->status === 'approved')
                            <form method="POST" action="{{ route('student.bookings.cancel', $booking) }}" onsubmit="return confirm('Are you sure?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-100 text-red-600 px-4 py-2 rounded-lg hover:bg-red-200 font-medium">
                                    Cancel
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-600 text-lg mb-4">You have no bookings yet</p>
                <a href="{{ route('student.bookings.available') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
                    Browse Available Rooms
                </a>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    <div class="mt-8">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
