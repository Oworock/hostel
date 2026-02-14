@extends('layouts.app')

@section('title', 'Edit Room')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Edit Room</h1>
    
    <div class="bg-white rounded-lg shadow-md p-8">
        <form method="POST" action="{{ route('manager.rooms.update', $room) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="room_number" class="block text-sm font-medium text-gray-700 mb-1">Room Number *</label>
                    <input type="text" id="room_number" name="room_number" value="{{ old('room_number', $room->room_number) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('room_number') border-red-500 @enderror"
                           required>
                    @error('room_number')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Room Type *</label>
                    <select id="type" name="type" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror"
                            required>
                        <option value="single" @selected(old('type', $room->type) === 'single')>Single</option>
                        <option value="double" @selected(old('type', $room->type) === 'double')>Double</option>
                        <option value="triple" @selected(old('type', $room->type) === 'triple')>Triple</option>
                        <option value="quad" @selected(old('type', $room->type) === 'quad')>Quad</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacity (beds) *</label>
                    <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $room->capacity) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('capacity') border-red-500 @enderror"
                           min="1" max="10" required>
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="price_per_month" class="block text-sm font-medium text-gray-700 mb-1">Price Per Month *</label>
                    <input type="number" id="price_per_month" name="price_per_month" value="{{ old('price_per_month', $room->price_per_month) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price_per_month') border-red-500 @enderror"
                           step="0.01" min="0" required>
                    @error('price_per_month')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $room->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="is_available" class="flex items-center">
                    <input type="checkbox" id="is_available" name="is_available" value="1" 
                           @checked(old('is_available', $room->is_available))
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm font-medium text-gray-700">Available</span>
                </label>
            </div>
            
            <div class="flex items-center space-x-4 pt-6 border-t">
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium">
                    Update Room
                </button>
                <a href="{{ route('manager.rooms.show', $room) }}" class="text-gray-600 hover:text-gray-900 font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
