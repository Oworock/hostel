<x-dashboard-layout title="Student Dashboard">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="uniform-page">
        <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-slate-100">Student Dashboard</h1>
                <p class="text-gray-600 dark:text-slate-300 mt-1">Track your bookings, payments, and complaints in one place.</p>
            </div>
            <a href="{{ route('student.profile.edit') }}" class="inline-flex items-center justify-center bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 font-semibold">
                Update Profile
            </a>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">Active Stay</p>
                <p class="text-3xl font-bold {{ $stats['active_booking'] ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-slate-100' }}">{{ $stats['active_booking'] ? 'Yes' : 'No' }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">Pending Bookings</p>
                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['pending_bookings'] }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">Completed Bookings</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['completed_bookings'] }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">Total Paid</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ getCurrencySymbol() }}{{ number_format($stats['total_paid'], 2) }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">Open Complaints</p>
                <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['open_complaints'] }}</p>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="uniform-card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">Current Stay</h2>
                @if($currentBooking)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-300">Hostel</p>
                            <p class="font-semibold text-gray-900 dark:text-slate-100">{{ $currentHostelName ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-300">Room / Bed</p>
                            <p class="font-semibold text-gray-900 dark:text-slate-100">{{ $currentBooking->room->room_number }} @if($currentBooking->bed) • {{ $currentBooking->bed->bed_number }} @endif</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-300">Check-In</p>
                            <p class="font-semibold text-gray-900 dark:text-slate-100">{{ $currentBooking->check_in_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-300">Check-Out</p>
                            <p class="font-semibold text-gray-900 dark:text-slate-100">{{ $currentBooking->check_out_date?->format('M d, Y') ?? 'TBD' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-300">Outstanding Balance</p>
                            <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ getCurrencySymbol() }}{{ number_format($stats['current_booking_balance'], 2) }}</p>
                            @if($stats['current_booking_balance'] > 0)
                                <a href="{{ route('student.bookings.show', $currentBooking) }}" class="inline-flex mt-2 items-center bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
                                    Pay Outstanding
                                </a>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-slate-300">Days to Checkout</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-slate-100">{{ $stats['days_to_checkout'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-gray-600 dark:text-slate-300 mb-4">No active stay yet.</p>
                    <a href="{{ route('student.bookings.available') }}" class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">Browse Rooms</a>
                @endif
            </div>

            <div class="uniform-card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="{{ route('student.bookings.available') }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-medium">Browse Rooms</a>
                    <a href="{{ route('student.bookings.index') }}" class="block w-full text-center bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 font-medium">My Bookings</a>
                    <a href="{{ route('student.payments.index') }}" class="block w-full text-center bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 font-medium">Payment History</a>
                    <a href="{{ route('student.room-change.index') }}" class="block w-full text-center bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 font-medium">Change Room</a>
                    <a href="{{ route('student.hostel-change.index') }}" class="block w-full text-center bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 font-medium">Change Hostel</a>
                    <a href="{{ route('student.complaints.index') }}" class="block w-full text-center bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 font-medium">Support & Complaints</a>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="uniform-card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">Recent Bookings</h2>
                <div class="space-y-3">
                    @forelse($recentBookings as $booking)
                        <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-medium text-gray-900 dark:text-slate-100">{{ $booking->room->hostel->name ?? 'Hostel' }} • Room {{ $booking->room->room_number }}</p>
                                <span class="text-xs px-2 py-1 rounded-full
                                    @if($booking->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                    @elseif($booking->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                    @else bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-slate-300
                                    @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">Check-in {{ $booking->check_in_date->format('M d, Y') }}</p>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-slate-300">No booking history yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="uniform-card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">Recent Payments</h2>
                <div class="space-y-3">
                    @forelse($recentPayments as $payment)
                        <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                            <p class="font-semibold text-gray-900 dark:text-slate-100">{{ getCurrencySymbol() }}{{ number_format($payment->amount, 2) }}</p>
                            <p class="text-sm text-gray-600 dark:text-slate-300">{{ ucfirst($payment->payment_method ?? 'N/A') }} • {{ $payment->created_at->format('M d, Y') }}</p>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-slate-300">No recent payments.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="uniform-card p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">Recent Complaints</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse($recentComplaints as $complaint)
                    <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg">
                        <p class="font-semibold text-gray-900 dark:text-slate-100">{{ $complaint->subject }}</p>
                        <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">{{ $complaint->created_at->format('M d, Y') }}</p>
                    </div>
                @empty
                    <p class="text-gray-600 dark:text-slate-300">No complaints submitted.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-dashboard-layout>
