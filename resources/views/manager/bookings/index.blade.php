@extends('layouts.app')

@section('title', 'Bookings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Bookings</h1>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Student</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Room</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Check-in</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Check-out</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Amount</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->room->room_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->check_in_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $booking->check_out_date?->format('M d, Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    @if($booking->status === 'approved') bg-green-100 text-green-800
                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">${{ number_format($booking->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('manager.bookings.show', $booking) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-600">No bookings found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="mt-8">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
