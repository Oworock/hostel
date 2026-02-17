@extends('layouts.app')

@section('title', 'Students')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Students in {{ auth()->user()->hostel->name ?? 'Your Hostel' }}</h1>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Name</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Email</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Phone</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Current Room</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Check-in Date</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Booking Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($students as $student)
                        @php
                            $activeBooking = $student->bookings->firstWhere('status', 'approved');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $student->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $student->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $student->phone ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($activeBooking)
                                    {{ $activeBooking->room->room_number }}
                                @else
                                    <span class="text-gray-400">No active booking</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($activeBooking)
                                    {{ $activeBooking->check_in_date->format('M d, Y') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($activeBooking)
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @elseif($student->bookings->count() > 0)
                                    @php
                                        $latestBooking = $student->bookings->first();
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                        @if($latestBooking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($latestBooking->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($latestBooking->status) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">No bookings</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-600">No students found in your hostel</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="mt-8">
        {{ $students->links() }}
    </div>
</div>
@endsection
