<x-dashboard-layout title="Edit Room">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Edit Room</h1>

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6 sm:p-8">
            <form method="POST" action="{{ route('manager.rooms.update', $room) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="hostel_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Hostel *</label>
                    <select id="hostel_id" name="hostel_id" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('hostel_id') border-red-500 @enderror" required>
                        @foreach($hostels as $hostel)
                            <option value="{{ $hostel->id }}" @selected(old('hostel_id', $room->hostel_id) == $hostel->id)>{{ $hostel->name }}</option>
                        @endforeach
                    </select>
                    @error('hostel_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="room_number" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Room Number *</label>
                        <input type="text" id="room_number" name="room_number" value="{{ old('room_number', $room->room_number) }}" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('room_number') border-red-500 @enderror" required>
                        @error('room_number')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Room Type *</label>
                        <select id="type" name="type" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror" required>
                            <option value="single" @selected(old('type', $room->type) === 'single')>Single</option>
                            <option value="double" @selected(old('type', $room->type) === 'double')>Double</option>
                            <option value="triple" @selected(old('type', $room->type) === 'triple')>Triple</option>
                            <option value="quad" @selected(old('type', $room->type) === 'quad')>Quad</option>
                        </select>
                        @error('type')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Capacity (beds) *</label>
                        <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $room->capacity) }}" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('capacity') border-red-500 @enderror" min="1" max="10" required>
                        @error('capacity')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="price_per_month" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Price Per {{ ucfirst(getBookingPeriodLabel()) }} *</label>
                        <input type="number" id="price_per_month" name="price_per_month" value="{{ old('price_per_month', $room->price_per_month) }}" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price_per_month') border-red-500 @enderror" step="0.01" min="0" required>
                        @error('price_per_month')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ __("Description") }}</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $room->description) }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="cover_image" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ __('Room Cover Image (Optional)') }}</label>
                    @if($room->cover_image)
                        <img src="{{ asset('storage/' . $room->cover_image) }}" alt="Room Image" class="h-24 w-24 object-cover rounded-lg border border-slate-200 dark:border-slate-700 mb-3">
                    @endif
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cover_image') border-red-500 @enderror">
                    @error('cover_image')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    @if($room->cover_image)
                        <label class="mt-2 inline-flex items-center text-sm text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="remove_cover_image" value="1" class="mr-2 rounded border-slate-300 dark:border-slate-700">
                            Remove current image
                        </label>
                    @endif
                </div>

                <div>
                    <label for="is_available" class="flex items-center">
                        <input type="checkbox" id="is_available" name="is_available" value="1" @checked(old('is_available', $room->is_available)) class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-700 rounded">
                        <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-200">{{ __("Available") }}</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-5 border-t border-slate-200 dark:border-slate-700">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-lg hover:bg-blue-700 font-medium">Update Room</button>
                    <a href="{{ route('manager.rooms.show', $room) }}" class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-slate-100 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-dashboard-layout>
