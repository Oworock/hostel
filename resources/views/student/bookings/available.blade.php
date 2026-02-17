@extends('layouts.app')

@section('title', 'Browse Rooms')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Available Rooms</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($rooms as $room)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <!-- Room Images -->
                @if($room->images->count() > 0)
                    <div class="relative w-full h-48 bg-gray-200 overflow-hidden">
                        @php $firstImage = $room->images->first(); @endphp
                        @if($firstImage && $firstImage->image_path)
                            <img src="{{ asset('storage/' . $firstImage->image_path) }}" alt="Room Image" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                <span class="text-gray-600">No Image</span>
                            </div>
                        @endif
                        @if($room->images->count() > 1)
                            <div class="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white px-2 py-1 rounded text-xs">
                                {{ $room->images->count() }} images
                            </div>
                        @endif
                    </div>
                @else
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-600">No Images Available</span>
                    </div>
                @endif
                
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ $room->room_number }}</h2>
                            <p class="text-gray-600">{{ ucfirst($room->type) }} Room</p>
                        </div>
                        <span class="text-3xl font-bold text-blue-600">{{ getCurrencySymbol() }}{{ number_format($room->price_per_month, 2) }}</span>
                    </div>
                    
                    @if($room->description)
                        <p class="text-gray-600 mb-4">{{ $room->description }}</p>
                    @endif
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Capacity:</span>
                            <span class="font-medium">{{ $room->capacity }} beds</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Available:</span>
                            <span class="font-medium">{{ $room->availableBeds()->count() }} beds</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Occupancy:</span>
                            <span class="font-medium">{{ $room->getOccupancyPercentage() }}%</span>
                        </div>
                    </div>
                    
                    @if($room->availableBeds()->count() > 0)
                        <a href="{{ route('student.bookings.create', $room) }}" class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">
                            Book Now
                        </a>
                    @else
                        <button disabled class="block w-full text-center bg-gray-400 text-white px-4 py-2 rounded-lg cursor-not-allowed font-medium">
                            No Beds Available
                        </button>
                    @endif
                </div>
                
                <div class="bg-gray-50 px-6 py-3 border-t">
                    <p class="text-xs text-gray-600">
                        @if ($room->hostel)
                            {{ $room->hostel->name }} - {{ $room->hostel->city }}
                        @endif
                    </p>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-600 text-lg mb-4">No rooms available at the moment</p>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    <div class="mt-12">
        {{ $rooms->links() }}
    </div>
</div>
@endsection
