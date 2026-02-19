<x-dashboard-layout title="My Payments">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-slate-100">Payment History</h1>
            <p class="text-gray-600 dark:text-slate-300 mt-2">View all your payments and transactions</p>
        </div>

        @if($outstandingBookings->isNotEmpty())
            <section class="bg-white dark:bg-slate-900 rounded-lg shadow p-6 mb-8">
                @php
                    $gatewayStyles = [
                        'paystack' => 'bg-[#0BA4DB]',
                        'flutterwave' => 'bg-[#F5A623]',
                        'stripe' => 'bg-[#635BFF]',
                        'paypal' => 'bg-[#003087]',
                        'razorpay' => 'bg-[#2B6CF6]',
                        'square' => 'bg-[#111827]',
                    ];
                @endphp
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-slate-100">Outstanding Payments</h2>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">{{ $outstandingBookings->count() }} pending</span>
                </div>
                @if($activeGateways->isEmpty())
                    <p class="text-sm text-red-600 dark:text-red-400 mb-4">No active online payment gateway is currently configured by admin.</p>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($outstandingBookings as $booking)
                        <div class="rounded-lg border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-slate-100">Booking #{{ $booking->id }} • Room {{ $booking->room->room_number ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-600 dark:text-slate-300">{{ $booking->room->hostel->name ?? 'Hostel' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 dark:text-slate-400">Outstanding</p>
                                    <p class="text-lg font-bold text-red-700 dark:text-red-300">{{ formatCurrency($booking->outstandingAmount()) }}</p>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($activeGateways as $gatewayKey => $gatewayRecord)
                                    <form method="POST" action="{{ route('student.payments.initialize', ['booking' => $booking->id, 'gateway' => $gatewayKey]) }}">
                                        @csrf
                                        <button type="submit" class="{{ $gatewayStyles[$gatewayKey] ?? 'bg-blue-600' }} text-white px-3 py-2 rounded-lg text-sm font-medium hover:opacity-90">
                                            Pay with {{ $gatewayRecord->name }}
                                        </button>
                                    </form>
                                @endforeach
                                <a href="{{ route('student.bookings.show', $booking) }}" class="px-3 py-2 rounded-lg text-sm font-medium bg-gray-200 text-gray-800 dark:bg-slate-700 dark:text-slate-100 hover:bg-gray-300 dark:hover:bg-slate-600">Open Booking</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($payments->count() > 0)
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px]">
                        <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-slate-100">Booking ID</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-slate-100">Amount</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-slate-100">Method</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-slate-100">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-slate-100">Date</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-slate-100">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-800">
                            @foreach($payments as $payment)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/60">
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('student.bookings.show', $payment->booking_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">#{{ $payment->booking_id }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-slate-100">{{ formatCurrency($payment->amount) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-slate-300 capitalize">{{ $payment->payment_method }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="space-y-1">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                @if($payment->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                                @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                                @elseif($payment->status === 'failed') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                                @else bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-slate-300
                                                @endif">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                            @if($payment->is_manual && $payment->status === 'paid')
                                                <p class="text-xs text-blue-700 dark:text-blue-300">
                                                    Cleared by admin{{ $payment->createdByAdmin ? ': ' . $payment->createdByAdmin->name : '' }}
                                                </p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-slate-300">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($payment->status === 'paid')
                                            <a href="{{ route('student.bookings.receipt', $payment->booking_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Download</a>
                                        @else
                                            <span class="text-gray-400 dark:text-slate-500">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                    {{ $payments->links() }}
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-slate-100 mb-2">No Payments Yet</h3>
                <p class="text-gray-600 dark:text-slate-300 mb-6">You haven't made any payments yet.</p>
                <a href="{{ route('student.bookings.available') }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Browse Rooms
                </a>
            </div>
        @endif
    </div>
</x-dashboard-layout>
