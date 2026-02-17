<x-dashboard-layout title="My Bookings">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="uniform-page">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">My Bookings</h1>
            <a href="{{ route('student.bookings.available') }}" class="inline-flex items-center justify-center bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 font-medium">
                Browse Rooms
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6">
            @forelse($bookings as $booking)
                @php
                    $outstandingAmount = $booking->outstandingAmount();
                    $canCancel = in_array($booking->status, ['pending', 'approved'], true) && $booking->payments->isEmpty();
                @endphp
                <article class="bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 hover:shadow-lg transition border border-slate-100 dark:border-slate-800">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">{{ $booking->room->room_number }}</h2>
                            <p class="text-gray-600 dark:text-slate-300">{{ $booking->room->hostel->name }}</p>
                        </div>
                        <span class="px-4 py-2 rounded-full text-sm font-medium
                            @if($booking->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                            @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                            @elseif($booking->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                            @elseif($booking->status === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                            @elseif(in_array($booking->status, ['cancelled', 'canceled'])) bg-gray-100 text-gray-800 dark:bg-slate-700 dark:text-slate-200
                            @else bg-gray-100 text-gray-800 dark:bg-slate-700 dark:text-slate-200
                            @endif">
                            {{ ucwords(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </div>

                    <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 py-4 border-y border-gray-200 dark:border-slate-700">
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-slate-300">Check-in</dt>
                            <dd class="font-medium text-gray-900 dark:text-slate-100">{{ $booking->check_in_date->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-slate-300">Check-out</dt>
                            <dd class="font-medium text-gray-900 dark:text-slate-100">{{ $booking->check_out_date?->format('M d, Y') ?? 'TBD' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-slate-300">Room Type</dt>
                            <dd class="font-medium text-gray-900 dark:text-slate-100">{{ ucfirst($booking->room->type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-slate-300">Total Amount</dt>
                            <dd class="font-medium text-gray-900 dark:text-slate-100">{{ getCurrencySymbol() }}{{ number_format($booking->total_amount, 2) }}</dd>
                        </div>
                    </dl>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-gray-600 dark:text-slate-300">Booked: {{ $booking->created_at->format('M d, Y') }}</p>
                        <div class="flex items-center space-x-2">
                            @if($outstandingAmount > 0 && in_array($booking->status, ['pending', 'approved'], true))
                                <a href="{{ route('student.bookings.show', $booking) }}" class="bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 px-4 py-2 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/60 font-medium">
                                    Pay Now
                                </a>
                            @endif
                            <a href="{{ route('student.bookings.show', $booking) }}" class="bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 px-4 py-2 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/60 font-medium">
                                View
                            </a>
                            @if($canCancel)
                                <form method="POST" action="{{ route('student.bookings.cancel', $booking) }}" onsubmit="return confirm('Are you sure?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300 px-4 py-2 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/60 font-medium">
                                        Cancel
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="uniform-card p-12 text-center">
                    <p class="text-slate-600 dark:text-slate-300 text-lg mb-4">You have no bookings yet.</p>
                    <a href="{{ route('student.bookings.available') }}" class="inline-block bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 font-medium">
                        Browse Available Rooms
                    </a>
                </div>
            @endforelse
        </div>

        <div>
            {{ $bookings->links() }}
        </div>
    </div>
</x-dashboard-layout>
