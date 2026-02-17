<x-dashboard-layout title="Browse Rooms">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="uniform-page">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Browse Rooms</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">Choose a room and continue booking with clear pricing per {{ getBookingPeriodLabel() }}.</p>
        </div>

        <div class="uniform-grid-3">
            @forelse($rooms as $room)
                <x-room-listing-card variant="booking" :room="$room" :action-url="route('student.bookings.create', $room)" action-label="Book Now" />
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
