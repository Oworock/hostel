<x-dashboard-layout :title="__('Browse Rooms')">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="uniform-page">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __("Browse Rooms") }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Choose a room and continue booking with clear pricing per :period.', ['period' => getBookingPeriodLabel()]) }}</p>
        </div>

        @if($blockingBooking)
            <div class="uniform-card p-4 border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30">
                @if($blockingBooking->status === 'pending')
                    <p class="text-amber-800 dark:text-amber-200 font-medium">
                        {{ __('You already have a pending booking. Pay for it or cancel it before creating a new booking.') }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('student.bookings.show', $blockingBooking) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium text-sm">{{ __('Pay Pending Booking') }}</a>
                        <a href="{{ route('student.bookings.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-4 py-2 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 font-medium text-sm">{{ __('Go to My Bookings') }}</a>
                    </div>
                @else
                    <p class="text-amber-800 dark:text-amber-200 font-medium">
                        {{ __('You already have an active booking. Use hostel change or room change request instead of creating another booking.') }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('student.hostel-change.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium text-sm">{{ __("Request Hostel Change") }}</a>
                        <a href="{{ route('student.room-change.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium text-sm">{{ __('Request Room Change') }}</a>
                    </div>
                @endif
            </div>
        @endif

        <form action="{{ route('student.bookings.available') }}" method="GET" class="uniform-card p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <select name="hostel_id" class="px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                <option value="">{{ __('All hostels') }}</option>
                @foreach($hostels as $hostel)
                    <option value="{{ $hostel->id }}" @selected(request('hostel_id') == $hostel->id)>{{ $hostel->name }}</option>
                @endforeach
            </select>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __("Hostel, room, city") }}" class="px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            <input type="number" name="max_price" value="{{ request('max_price') }}" min="0" step="0.01" placeholder="{{ __("Max price") }}" class="px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            <select name="sort" class="px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                <option value="price_asc" @selected($sort === 'price_asc')>{{ __('Price: Low to high') }}</option>
                <option value="price_desc" @selected($sort === 'price_desc')>{{ __('Price: High to low') }}</option>
                <option value="recent" @selected($sort === 'recent')>{{ __('Newest') }}</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 py-2.5 font-semibold hover:bg-blue-700">{{ __("Search") }}</button>
        </form>

        <div class="uniform-grid-3">
            @forelse($rooms as $room)
                <x-room-listing-card
                    variant="booking"
                    :room="$room"
                    :action-url="$blockingBooking ? null : route('student.bookings.create', $room)"
                    :action-label="__('Book Now')"
                    :locked="(bool) $blockingBooking"
                    :locked-label="$blockingBooking?->status === 'pending' ? __('Resolve Pending Booking') : __('Use Change Request')"
                />
            @empty
                <div class="col-span-full uniform-card p-12 text-center">
                    <p class="text-slate-600 dark:text-slate-300 text-lg">{{ __('No rooms available at the moment.') }}</p>
                </div>
            @endforelse
        </div>

        <div>
            {{ $rooms->links() }}
        </div>
    </div>
</x-dashboard-layout>
