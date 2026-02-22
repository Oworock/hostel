<x-dashboard-layout :title="__('Rooms')">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="uniform-page">
        <div class="uniform-header">
            <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-slate-100">Rooms</h1>
            <a href="{{ route('manager.rooms.create') }}" class="inline-flex items-center justify-center bg-blue-600 text-white px-7 py-3 rounded-2xl hover:bg-blue-700 font-semibold text-lg sm:text-xl">+ Add New Room</a>
        </div>

        <div class="uniform-grid-2">
            @forelse($rooms as $room)
                <x-room-listing-card variant="manager" :room="$room">
                    <x-slot name="actions">
                        <div class="grid grid-cols-3 gap-3">
                            <a href="{{ route('manager.rooms.show', $room) }}" class="bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 px-4 py-2.5 rounded-2xl text-center text-lg font-semibold hover:bg-blue-200 dark:hover:bg-blue-900/60">View</a>
                            <a href="{{ route('manager.rooms.edit', $room) }}" class="bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 px-4 py-2.5 rounded-2xl text-center text-lg font-semibold hover:bg-amber-200 dark:hover:bg-amber-900/60">Edit</a>
                            <form method="POST" action="{{ route('manager.rooms.destroy', $room) }}" onsubmit="return confirm(@js(__('Are you sure?')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 px-4 py-2.5 rounded-2xl text-lg font-semibold hover:bg-red-200 dark:hover:bg-red-900/60">{{ __("Delete") }}</button>
                            </form>
                        </div>
                    </x-slot>
                </x-room-listing-card>
            @empty
                <div class="col-span-full uniform-card p-12 text-center">
                    <p class="text-slate-600 dark:text-slate-300 text-lg mb-4">{{ __('No rooms found.') }}</p>
                    <a href="{{ route('manager.rooms.create') }}" class="inline-block bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 font-medium">Create First Room</a>
                </div>
            @endforelse
        </div>

        <div>
            {{ $rooms->links() }}
        </div>
    </div>
</x-dashboard-layout>
