@extends('layouts.app')

@section('title', 'Rooms')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Rooms</h1>
        <a href="{{ route('manager.rooms.create') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
            + Add New Room
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($rooms as $room)
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $room->room_number }}</h2>
                        <p class="text-gray-600 text-sm">{{ ucfirst($room->type) }} Room</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $room->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $room->is_available ? 'Available' : 'Unavailable' }}
                    </span>
                </div>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Capacity:</span>
                        <span class="font-medium text-gray-900">{{ $room->capacity }} beds</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Occupancy:</span>
                        <span class="font-medium text-gray-900">{{ $room->getOccupancyPercentage() }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Price:</span>
                        <span class="font-medium text-gray-900">${{ number_format($room->price_per_month, 2) }}/mo</span>
                    </div>
                </div>
                
                @if($room->description)
                    <p class="text-sm text-gray-600 mb-4">{{ $room->description }}</p>
                @endif
                
                <div class="flex items-center space-x-2">
                    <a href="{{ route('manager.rooms.show', $room) }}" class="flex-1 bg-blue-100 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-200 font-medium text-center">
                        View
                    </a>
                    <a href="{{ route('manager.rooms.edit', $room) }}" class="flex-1 bg-yellow-100 text-yellow-600 px-4 py-2 rounded-lg hover:bg-yellow-200 font-medium text-center">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('manager.rooms.destroy', $room) }}" onsubmit="return confirm('Are you sure?')" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-100 text-red-600 px-4 py-2 rounded-lg hover:bg-red-200 font-medium">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-600 text-lg mb-4">No rooms found</p>
                <a href="{{ route('manager.rooms.create') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
                    Create First Room
                </a>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    <div class="mt-8">
        {{ $rooms->links() }}
    </div>
</div>
@endsection
