@props([
    'room',
    'variant' => 'booking', // booking | manager
    'actionUrl' => null,
    'actionLabel' => 'Book Now',
    'periodLabel' => null,
    'locked' => false,
    'lockedLabel' => 'Booking Locked',
])

@php
    $displayImage = $room->cover_image ?: ($room->images->first()?->image_path ?: $room->hostel?->image_path);
    $availableBeds = method_exists($room, 'availableBeds') ? $room->availableBeds()->count() : 0;
    $occupancy = method_exists($room, 'getOccupancyPercentage') ? $room->getOccupancyPercentage() : 0;
    $isAvailable = (bool) ($room->is_available ?? false);
    $period = $periodLabel ?: ('per ' . getBookingPeriodLabel());
    $locationText = collect([
        $room->hostel->name ?? null,
        $room->hostel->address ?? null,
        $room->hostel->city ?? null,
        $room->hostel->state ?? null,
    ])->filter()->implode(' - ');
@endphp

@if($variant === 'manager')
    <article {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 rounded-lg shadow-md p-6 hover:shadow-lg transition border border-slate-100 dark:border-slate-800']) }}>
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h3 class="text-4xl sm:text-5xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ $room->room_number }}</h3>
                <p class="text-xl sm:text-2xl text-slate-600 dark:text-slate-300 mt-1">{{ ucfirst($room->type) }} Room</p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-medium {{ $isAvailable ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' }}">
                {{ $isAvailable ? 'Available' : 'Unavailable' }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-3 mb-6 py-4 border-y border-gray-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">
            <div class="flex items-center justify-between"><span>Capacity:</span><strong>{{ $room->capacity }} beds</strong></div>
            <div class="flex items-center justify-between"><span>Occupancy:</span><strong>{{ $occupancy }}%</strong></div>
            <div class="flex items-center justify-between"><span>Price:</span><strong>{{ getCurrencySymbol() }}{{ number_format($room->price_per_month, 2) }}/{{ getBookingPeriodLabel() }}</strong></div>
        </div>

        @if(isset($actions))
            <div>{{ $actions }}</div>
        @elseif($actionUrl)
            <a href="{{ $actionUrl }}" class="inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700">{{ $actionLabel }}</a>
        @endif
    </article>
@else
    <article {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition']) }}>
        <div class="w-full h-48 bg-gray-200 dark:bg-slate-800 flex items-center justify-center">
            @if($displayImage)
                <div class="relative w-full h-48 bg-gray-200 dark:bg-slate-800 overflow-hidden">
                    <img src="{{ asset('storage/' . $displayImage) }}" alt="Room {{ $room->room_number }}" class="w-full h-full object-cover">
                </div>
            @else
                <span class="text-gray-600 dark:text-slate-300">No Images Available</span>
            @endif
        </div>

        <div class="p-6">
            <div class="flex items-start justify-between gap-2 mb-3">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-slate-100">{{ $room->room_number }}</h3>
                    <p class="text-gray-600 dark:text-slate-300">{{ ucfirst($room->type) }} Room</p>
                </div>
                <div class="text-right">
                    <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ getCurrencySymbol() }}{{ number_format($room->price_per_month, 2) }}</span>
                    <p class="text-xs text-gray-500 dark:text-slate-400">{{ $period }}</p>
                </div>
            </div>

            <div class="space-y-2 mb-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-slate-300">Capacity:</span>
                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ $room->capacity }} beds</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-slate-300">Available:</span>
                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ $availableBeds }} beds</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-slate-300">Occupancy:</span>
                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ $occupancy }}%</span>
                </div>
            </div>

            @if(isset($actions))
                <div>{{ $actions }}</div>
            @elseif($actionUrl)
                @if($locked)
                    <button type="button" disabled class="block w-full text-center bg-gray-400 dark:bg-slate-700 text-white px-4 py-2 rounded-lg cursor-not-allowed font-medium">
                        {{ $lockedLabel }}
                    </button>
                @elseif($availableBeds > 0 && $isAvailable)
                    <a href="{{ $actionUrl }}" class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">
                        {{ $actionLabel }}
                    </a>
                @else
                    <button type="button" disabled class="block w-full text-center bg-gray-400 dark:bg-slate-700 text-white px-4 py-2 rounded-lg cursor-not-allowed font-medium">
                        No Beds Available
                    </button>
                @endif
            @endif
        </div>

        <div class="bg-gray-50 dark:bg-slate-800 px-6 py-3 border-t border-slate-200 dark:border-slate-700">
            <p class="text-xs text-gray-600 dark:text-slate-300">
                {{ $locationText !== '' ? $locationText : 'Location not set' }}
            </p>
        </div>
    </article>
@endif
