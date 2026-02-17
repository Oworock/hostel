<x-dashboard-layout :title="$room->room_number">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $room->room_number }}</h1>
                <p class="text-slate-600 dark:text-slate-300 mt-1">{{ ucfirst($room->type) }} Room</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('manager.rooms.edit', $room) }}" class="bg-amber-600 text-white px-4 py-2.5 rounded-lg hover:bg-amber-700">Edit</a>
                <form method="POST" action="{{ route('manager.rooms.destroy', $room) }}" onsubmit="return confirm('Are you sure?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2.5 rounded-lg hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-5">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Capacity</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $room->capacity }} beds</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-5">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Price Per {{ ucfirst(getBookingPeriodLabel()) }}</p>
                <p class="text-3xl font-bold text-green-700 dark:text-green-400">{{ getCurrencySymbol() }}{{ number_format($room->price_per_month, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-5">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Occupancy</p>
                <p class="text-3xl font-bold text-blue-700 dark:text-blue-400">{{ $room->getOccupancyPercentage() }}%</p>
            </div>
        </div>

        @if($room->description)
            <section class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">Description</h2>
                <p class="text-slate-600 dark:text-slate-300">{{ $room->description }}</p>
            </section>
        @endif

        <section class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Beds</h2>
                <span class="text-xs text-slate-500 dark:text-slate-400">Only admin-approved beds are visible to students.</span>
            </div>

            <form method="POST" action="{{ route('manager.rooms.beds.store', $room) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6 p-4 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                @csrf
                <div>
                    <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">Bed Number</label>
                    <input type="text" name="bed_number" class="w-full border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-lg px-3 py-2 text-slate-900 dark:text-slate-100" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">Bed Label (Optional)</label>
                    <input type="text" name="name" class="w-full border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-lg px-3 py-2 text-slate-900 dark:text-slate-100">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Add Bed Space</button>
                </div>
            </form>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($room->beds()->orderBy('bed_number')->get() as $bed)
                    <div class="p-4 rounded-lg border {{ $bed->is_occupied ? 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800' : 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' }}">
                        <p class="font-bold text-slate-900 dark:text-slate-100">{{ $bed->bed_number }}</p>
                        <p class="text-sm {{ $bed->is_occupied ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300' }}">{{ $bed->is_occupied ? 'Occupied' : 'Available' }}</p>
                        <p class="text-xs mt-1 {{ $bed->is_approved ? 'text-green-700 dark:text-green-300' : 'text-amber-700 dark:text-amber-300' }}">{{ $bed->is_approved ? 'Approved for booking' : 'Pending admin approval' }}</p>
                        @if($bed->is_occupied && $bed->user)
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-2">{{ $bed->user->name }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-dashboard-layout>
