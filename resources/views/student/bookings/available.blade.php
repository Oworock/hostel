<x-dashboard-layout title="Browse Rooms">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="uniform-page">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Browse Rooms</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">Choose a room and continue booking with clear pricing per {{ getBookingPeriodLabel() }}.</p>
        </div>

        @if($blockingBooking)
            <div class="uniform-card p-4 border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30">
                @if($blockingBooking->status === 'pending')
                    <p class="text-amber-800 dark:text-amber-200 font-medium">
                        You already have a pending booking. Pay for it or cancel it before creating a new booking.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('student.bookings.show', $blockingBooking) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium text-sm">Pay Pending Booking</a>
                        <a href="{{ route('student.bookings.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-4 py-2 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 font-medium text-sm">Go to My Bookings</a>
                    </div>
                @else
                    <p class="text-amber-800 dark:text-amber-200 font-medium">
                        You already have an active booking. Use hostel change or room change request instead of creating another booking.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('student.hostel-change.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium text-sm">Request Hostel Change</a>
                        <a href="{{ route('student.room-change.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium text-sm">Request Room Change</a>
                    </div>
                @endif
            </div>
        @endif

        <div class="uniform-grid-3">
            @forelse($rooms as $room)
                <x-room-listing-card
                    variant="booking"
                    :room="$room"
                    :action-url="$blockingBooking ? null : route('student.bookings.create', $room)"
                    action-label="Book Now"
                    :locked="(bool) $blockingBooking"
                    :locked-label="$blockingBooking?->status === 'pending' ? 'Resolve Pending Booking' : 'Use Change Request'"
                />
            @empty
                <div class="col-span-full uniform-card p-12 text-center">
                    <p class="text-slate-600 dark:text-slate-300 text-lg">No rooms available at the moment.</p>
                </div>
            @endforelse
        </div>

        <div>
            {{ $rooms->links() }}
        </div>
    </div>
</x-dashboard-layout>
