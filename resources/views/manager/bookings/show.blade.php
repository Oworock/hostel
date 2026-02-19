<x-dashboard-layout title="Booking Details">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Booking #{{ $booking->id }}</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <section class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Booking Information</h2>
                <dl class="space-y-3">
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Student</dt><dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->user->name }}</dd></div>
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Email</dt><dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->user->email }}</dd></div>
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Phone</dt><dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->user->phone ?? '-' }}</dd></div>
                </dl>
            </section>

            <section class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Room Information</h2>
                <dl class="space-y-3">
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Room Number</dt><dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->room->room_number }}</dd></div>
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Room Type</dt><dd class="font-semibold text-slate-900 dark:text-slate-100">{{ ucfirst($booking->room->type) }}</dd></div>
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Price Per {{ ucfirst(getBookingPeriodLabel()) }}</dt><dd class="font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency($booking->room->price_per_month) }}</dd></div>
                </dl>
            </section>
        </div>

        <section class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Booking Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                <div class="rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Check-in Date</p>
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->check_in_date->format('M d, Y') }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Check-out Date</p>
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->check_out_date?->format('M d, Y') ?? '-' }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Total Amount</p>
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency($booking->total_amount) }}</p>
                </div>
            </div>

            <div class="mb-6">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">Status</p>
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    @if($booking->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                    @elseif($booking->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                    @else bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300
                    @endif">
                    {{ ucfirst($booking->status) }}
                </span>
            </div>

            @if($booking->notes)
                <div class="mb-6">
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">Notes</p>
                    <p class="text-slate-900 dark:text-slate-100">{{ $booking->notes }}</p>
                </div>
            @endif

            @if($booking->isPending())
                <div class="flex items-center gap-3 pt-5 border-t border-slate-200 dark:border-slate-700">
                    <form method="POST" action="{{ route('manager.bookings.approve', $booking) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-green-600 text-white px-6 py-2.5 rounded-lg hover:bg-green-700 font-medium">Approve</button>
                    </form>

                    <form method="POST" action="{{ route('manager.bookings.reject', $booking) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 font-medium">Reject</button>
                    </form>
                </div>
            @elseif($booking->isApproved())
                <div class="flex items-center gap-3 pt-5 border-t border-slate-200 dark:border-slate-700">
                    <form method="POST" action="{{ route('manager.bookings.cancel', $booking) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 font-medium" onclick="return confirm('Are you sure?')">Cancel Booking</button>
                    </form>
                </div>
            @endif
        </section>

        <a href="{{ route('manager.bookings.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">Back to Bookings</a>
    </div>
</x-dashboard-layout>
