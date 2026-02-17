@extends('layouts.app')

@section('title', 'Book Rooms')

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h1 class="text-5xl font-bold text-slate-900 dark:text-slate-100">Available Rooms</h1>
                <p class="text-slate-600 dark:text-slate-300 mt-1">{{ $rooms->total() }} rooms available for booking.</p>
            </div>
            @guest
                <div class="flex items-center gap-2">
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg border border-blue-600 text-blue-700 dark:text-blue-300 font-semibold">Sign up</a>
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold">Log in</a>
                </div>
            @endguest
        </div>

        <form action="{{ route('public.rooms.index') }}" method="GET" class="bg-white dark:bg-slate-900 rounded-xl shadow-md border border-slate-200 dark:border-slate-800 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <select name="hostel_id" class="px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                <option value="">All hostels</option>
                @foreach($hostels as $hostel)
                    <option value="{{ $hostel->id }}" @selected(request('hostel_id') == $hostel->id)>{{ $hostel->name }}</option>
                @endforeach
            </select>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Hostel, room, city" class="px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            <input type="number" name="max_price" value="{{ request('max_price') }}" min="0" step="0.01" placeholder="Max price" class="px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            <select name="sort" class="px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                <option value="price_asc" @selected($sort === 'price_asc')>Price: Low to high</option>
                <option value="price_desc" @selected($sort === 'price_desc')>Price: High to low</option>
                <option value="recent" @selected($sort === 'recent')>Newest</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 py-2.5 font-semibold hover:bg-blue-700">Search</button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($rooms as $room)
                <x-room-listing-card variant="booking" :room="$room" :action-url="route('public.rooms.book', $room)" action-label="Book Now" />
            @empty
                <div class="col-span-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-10 text-center text-slate-600 dark:text-slate-300">No rooms match your filters.</div>
            @endforelse
        </div>

        <div>{{ $rooms->links() }}</div>
    </div>
</section>
@endsection
