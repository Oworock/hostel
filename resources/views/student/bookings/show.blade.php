@extends('layouts.app')

@section('title', 'Booking Details')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Booking Details</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
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
                    <p class="text-sm text-gray-600">Hostel</p>
                    <p class="text-lg font-medium text-gray-900">{{ $booking->room->hostel->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Price Per Month</p>
                    <p class="text-lg font-medium text-gray-900">{{ getCurrencySymbol() }}{{ number_format($booking->room->price_per_month, 2) }}</p>
                </div>
            </div>
        </div>
        
        <!-- Booking Status -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Booking Status</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <span class="inline-block px-4 py-2 rounded-full text-sm font-medium
                        @if($booking->status === 'approved') bg-green-100 text-green-800
                        @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                        @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Booking Date</p>
                    <p class="text-lg font-medium text-gray-900">{{ $booking->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking Dates -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Stay Duration</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-600">Check-in Date</p>
                <p class="text-lg font-medium text-gray-900">{{ $booking->check_in_date->format('M d, Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Check-out Date</p>
                <p class="text-lg font-medium text-gray-900">{{ $booking->check_out_date?->format('M d, Y') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Duration</p>
                <p class="text-lg font-medium text-gray-900">
                    @if($booking->check_out_date)
                        {{ $booking->check_in_date->diffInDays($booking->check_out_date) }} days
                    @else
                        TBD
                    @endif
                </p>
            </div>
        </div>
    </div>
    
    <!-- Payment Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Payment Information</h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b">
                <span class="text-gray-700">Total Amount</span>
                <span class="text-2xl font-bold text-gray-900">{{ getCurrencySymbol() }}{{ number_format($booking->total_amount, 2) }}</span>
            </div>
            
            @if($booking->payments->isNotEmpty())
                <div class="mt-4">
                    <h3 class="font-bold text-gray-900 mb-3">Payment History</h3>
                    <div class="space-y-3">
                        @foreach($booking->payments as $payment)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ ucfirst($payment->payment_method) }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ $payment->payment_date?->format('M d, Y') ?? 'Pending' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900">{{ getCurrencySymbol() }}{{ number_format($payment->amount, 2) }}</p>
                                    <span class="text-xs px-2 py-1 rounded {{ $payment->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="text-gray-600">No payments recorded yet.</p>
            @endif
        </div>
    </div>
    
    <!-- Notes -->
    @if($booking->notes)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Notes</h2>
            <p class="text-gray-600">{{ $booking->notes }}</p>
        </div>
    @endif
    
    <!-- Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('student.bookings.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
 Back to My Bookings            
        </a>
        
        @if($booking->status === 'pending' || $booking->status === 'approved')
            <form method="POST" action="{{ route('student.bookings.cancel', $booking) }}" onsubmit="return confirm('Are you sure you want to cancel this booking?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 font-medium">
                    Cancel Booking
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
