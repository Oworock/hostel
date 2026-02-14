@extends('layouts.app')

@section('title', $room->room_number)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">{{ $room->room_number }}</h1>
            <p class="text-gray-600 mt-2">{{ ucfirst($room->type) }} Room</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('manager.rooms.edit', $room) }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                Edit
            </a>
            <form method="POST" action="{{ route('manager.rooms.destroy', $room) }}" onsubmit="return confirm('Are you sure?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>
    
    <!-- Room Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-sm">Capacity</p>
            <p class="text-3xl font-bold text-gray-900">{{ $room->capacity }} beds</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-sm">Price Per Month</p>
            <p class="text-3xl font-bold text-green-600">${{ number_format($room->price_per_month, 2) }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-sm">Occupancy</p>
            <p class="text-3xl font-bold text-blue-600">{{ $room->getOccupancyPercentage() }}%</p>
        </div>
    </div>
    
    <!-- Description -->
    @if($room->description)
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Description</h2>
            <p class="text-gray-600">{{ $room->description }}</p>
        </div>
    @endif
    
    <!-- Beds -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Beds</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($room->beds()->orderBy('bed_number')->get() as $bed)
                <div class="p-4 rounded-lg border {{ $bed->is_occupied ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }}">
                    <p class="font-bold text-gray-900">{{ $bed->bed_number }}</p>
                    <p class="text-sm {{ $bed->is_occupied ? 'text-red-600' : 'text-green-600' }}">
                        {{ $bed->is_occupied ? 'Occupied' : 'Available' }}
                    </p>
                    @if($bed->is_occupied && $bed->user)
                        <p class="text-xs text-gray-600 mt-2">{{ $bed->user->name }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
