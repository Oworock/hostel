<x-dashboard-layout title="Create Room">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __('Create New Room') }}</h1>

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6 sm:p-8">
            <form method="POST" action="{{ route('manager.rooms.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label for="hostel_id" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Hostel *</label>
                    <select id="hostel_id" name="hostel_id" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('hostel_id') border-red-500 @enderror" required>
                        <option value="">Select hostel</option>
                        @foreach($hostels as $hostel)
                            <option value="{{ $hostel->id }}" @selected(old('hostel_id') == $hostel->id)>{{ $hostel->name }}</option>
                        @endforeach
                    </select>
                    @error('hostel_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="room_number" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Room Number *</label>
                        <input type="text" id="room_number" name="room_number" value="{{ old('room_number') }}" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('room_number') border-red-500 @enderror" placeholder="e.g., R101" required>
                        @error('room_number')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Room Type *</label>
                        <select id="type" name="type" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror" required>
                            <option value="">Select type</option>
                            <option value="single" @selected(old('type') === 'single')>Single Occupancy (1)</option>
                            <option value="double" @selected(old('type') === 'double')>Double Occupancy (2)</option>
                            <option value="triple" @selected(old('type') === 'triple')>Triple Occupancy (3)</option>
                            <option value="quad" @selected(old('type') === 'quad')>Quad Occupancy (4)</option>
                            <option value="quint" @selected(old('type') === 'quint')>Quintuple Occupancy (5)</option>
                            <option value="sext" @selected(old('type') === 'sext')>Sextuple Occupancy (6)</option>
                            <option value="sept" @selected(old('type') === 'sept')>Septuple Occupancy (7)</option>
                            <option value="oct" @selected(old('type') === 'oct')>Octuple Occupancy (8)</option>
                        </select>
                        @error('type')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Capacity (beds) *</label>
                        <input type="number" id="capacity" name="capacity" value="{{ old('capacity') }}" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('capacity') border-red-500 @enderror" min="1" max="8" required>
                        @error('capacity')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="price_per_month" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Price Per {{ ucfirst(getBookingPeriodLabel()) }} *</label>
                        <input type="number" id="price_per_month" name="price_per_month" value="{{ old('price_per_month') }}" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('price_per_month') border-red-500 @enderror" step="0.01" min="0" required>
                        @error('price_per_month')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ __("Description") }}</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="cover_image" class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">{{ __('Room Cover Image (Optional)') }}</label>
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cover_image') border-red-500 @enderror">
                    @error('cover_image')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3 pt-5 border-t border-slate-200 dark:border-slate-700">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-lg hover:bg-blue-700 font-medium">Create Room</button>
                    <a href="{{ route('manager.rooms.index') }}" class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-slate-100 font-medium">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-dashboard-layout>
