<x-dashboard-layout :title="__('Manager Dashboard')">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="uniform-page">
        <section>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-slate-100">{{ __(':hostel Operations', ['hostel' => $hostelLabel]) }}</h1>
            <p class="text-gray-600 dark:text-slate-300 mt-1">{{ __('Monitor occupancy, students, bookings, finance, and complaints.') }}</p>
        </section>

        <section class="uniform-grid-4">
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">{{ __('Total Rooms') }}</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-slate-100">{{ $stats['total_rooms'] }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">{{ __('Total Beds') }}</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-slate-100">{{ $stats['total_beds'] }}</p>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">{{ __(':count available', ['count' => $stats['available_beds']]) }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">{{ __('Occupancy') }}</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['occupancy_rate'] }}%</p>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">{{ __(':count occupied', ['count' => $stats['occupied_beds']]) }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">{{ __('Students') }}</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-slate-100">{{ $stats['total_students'] }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">{{ __('Pending Bookings') }}</p>
                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['pending_bookings'] }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">{{ __('Approved Bookings') }}</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['approved_bookings'] }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">{{ __('Open Complaints') }}</p>
                <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['open_complaints'] }}</p>
            </div>
            <div class="uniform-card p-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm">{{ __('Monthly Revenue') }}</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency($stats['monthly_revenue']) }}</p>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">{{ __('Total :amount', ['amount' => formatCurrency($stats['total_revenue'])]) }}</p>
            </div>
        </section>

        <section class="uniform-grid-2">
            <div class="uniform-card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">{{ __('Operational Actions') }}</h2>
                <div class="space-y-3">
                    <a href="{{ route('manager.students.index') }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-medium">{{ __('Student Directory') }}</a>
                    <a href="{{ route('manager.rooms.index') }}" class="block w-full text-center bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 font-medium">{{ __('Room Inventory') }}</a>
                    <a href="{{ route('manager.bookings.index') }}" class="block w-full text-center bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 font-medium">{{ __('Booking Queue') }}</a>
                    <a href="{{ route('manager.complaints.index') }}" class="block w-full text-center bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100 py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 font-medium">{{ __('Complaints Queue') }}</a>
                </div>
            </div>

            <div class="uniform-card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">{{ __('Subscription Expiry Alerts') }}</h2>
                <p class="text-sm text-gray-600 dark:text-slate-300 mb-3">{{ __('Expired: :count', ['count' => $expiredSubscriptionsCount ?? 0]) }}</p>
                <div class="space-y-3">
                    @forelse(($subscriptionAlerts ?? collect()) as $subscription)
                        @php
                            $daysRemaining = now()->startOfDay()->diffInDays($subscription->expires_at, false);
                        @endphp
                        <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                            <p class="font-medium text-gray-900 dark:text-slate-100">{{ $subscription->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-slate-300">{{ __(':hostel • Expires :date', ['hostel' => $subscription->hostel?->name, 'date' => $subscription->expires_at?->format('M d, Y')]) }}</p>
                            <p class="text-sm {{ $daysRemaining < 0 ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $daysRemaining < 0 ? __('Expired') : __(':count day(s) remaining', ['count' => $daysRemaining]) }}
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-slate-300">{{ __('No subscriptions expiring within 7 days.') }}</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="uniform-card p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">{{ __('Room Occupancy Snapshot') }}</h2>
                <div class="space-y-3">
                    @forelse($roomSnapshot as $room)
                        @php
                            $occupancy = $room->total_beds_count > 0 ? round(($room->occupied_beds_count / $room->total_beds_count) * 100, 2) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium text-gray-800 dark:text-slate-100">{{ __('Room :room', ['room' => $room->room_number]) }}</span>
                                <span class="text-gray-600 dark:text-slate-300">{{ __(':occupied/:total occupied', ['occupied' => $room->occupied_beds_count, 'total' => $room->total_beds_count]) }}</span>
                            </div>
                            <div class="h-2 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-600 rounded-full" style="width: {{ $occupancy }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-slate-300">{{ __('No rooms available for snapshot.') }}</p>
                    @endforelse
                </div>
        </section>

        <section class="uniform-grid-2">
            <div class="uniform-card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">{{ __("Recent Bookings") }}</h2>
                <div class="space-y-3">
                    @forelse($recentBookings as $booking)
                        <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-medium text-gray-900 dark:text-slate-100">{{ $booking->user->name }}</p>
                                <span class="text-xs px-2 py-1 rounded-full
                                    @if($booking->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                    @elseif($booking->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                    @else bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-slate-300
                                    @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">{{ __('Room :room • :date', ['room' => $booking->room->room_number, 'date' => $booking->check_in_date->format('M d, Y')]) }}</p>
                            <a href="{{ route('manager.bookings.show', $booking) }}" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 mt-2 inline-block">{{ __('Open booking') }}</a>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-slate-300">{{ __("No recent bookings.") }}</p>
                    @endforelse
                </div>
            </div>

            <div class="uniform-card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">{{ __("Recent Payments") }}</h2>
                <div class="space-y-3">
                    @forelse($recentPayments as $payment)
                        <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-slate-100">{{ $payment->user->name }}</p>
                                <p class="text-sm text-gray-600 dark:text-slate-300">{{ __('Room :room • :method', ['room' => $payment->booking?->room?->room_number ?? __('N/A'), 'method' => ucfirst($payment->payment_method ?? __('N/A'))]) }}</p>
                                @if($payment->is_manual && $payment->createdByAdmin)
                                    <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">{{ __('Approved by admin: :name', ['name' => $payment->createdByAdmin->name]) }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-slate-100">{{ formatCurrency($payment->amount) }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">{{ $payment->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-slate-300">{{ __("No recent payments.") }}</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="uniform-grid-2">
            <div class="uniform-card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">{{ __('Manual Payments Approved by Admin') }}</h2>
                <div class="space-y-3">
                    @forelse($manualAdminPayments as $payment)
                        <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $payment->user->name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-slate-300">{{ __('Room :room', ['room' => $payment->booking?->room?->room_number ?? __('N/A')]) }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">{{ __('Approved by Admin') }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">
                                {{ formatCurrency($payment->amount) }}
                                @if($payment->createdByAdmin)
                                    • {{ __('by :name', ['name' => $payment->createdByAdmin->name]) }}
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-slate-300">{{ __('No manual admin payments yet.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="uniform-card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100 mb-4">{{ __("Recent Complaints") }}</h2>
                <div class="space-y-3">
                    @forelse($recentComplaints as $complaint)
                        <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-medium text-gray-900 dark:text-slate-100">{{ $complaint->subject }}</p>
                                <span class="text-xs px-2 py-1 rounded-full
                                    @if($complaint->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                    @elseif($complaint->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                    @endif">
                                    {{ ucwords(str_replace('_', ' ', $complaint->status)) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">{{ $complaint->user->name }} • {{ $complaint->created_at->format('M d, Y') }}</p>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-slate-300">{{ __("No complaints raised.") }}</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-dashboard-layout>
