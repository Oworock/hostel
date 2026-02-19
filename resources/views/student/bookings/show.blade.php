<x-dashboard-layout title="Booking Details">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="uniform-page">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Booking Details</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <section class="uniform-card p-6">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Room Information</h2>
                <dl class="space-y-3">
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Room Number</dt><dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->room->room_number }}</dd></div>
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Room Type</dt><dd class="font-semibold text-slate-900 dark:text-slate-100">{{ ucfirst($booking->room->type) }}</dd></div>
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Hostel</dt><dd class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->room->hostel->name }}</dd></div>
                    <div><dt class="text-sm text-slate-500 dark:text-slate-400">Price Per {{ ucfirst(getBookingPeriodLabel()) }}</dt><dd class="font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency($booking->room->price_per_month) }}</dd></div>
                </dl>
            </section>

            <section class="uniform-card p-6">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Booking Status</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Status</p>
                        <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold
                            @if($booking->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                            @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                            @elseif($booking->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                            @elseif($booking->status === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                            @else bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300
                            @endif">{{ ucfirst($booking->status) }}</span>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Booking Date</p>
                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </section>
        </div>

        <section class="uniform-card p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Stay Duration</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Check-in Date</p>
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->check_in_date->format('M d, Y') }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Check-out Date</p>
                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $booking->check_out_date?->format('M d, Y') ?? '-' }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Duration</p>
                    <p class="font-semibold text-slate-900 dark:text-slate-100">
                        @if($booking->check_out_date)
                            {{ $booking->check_in_date->diffInDays($booking->check_out_date) }} days
                        @else
                            TBD
                        @endif
                    </p>
                </div>
            </div>
        </section>

        <section class="uniform-card p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Payment Information</h2>
            @php
                $paidAmount = $booking->payments->where('status', 'paid')->sum('amount');
                $outstandingAmount = max(0, (float) $booking->total_amount - (float) $paidAmount);
            @endphp
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-700">
                    <span class="text-slate-600 dark:text-slate-300">Total Amount</span>
                    <span class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ formatCurrency($booking->total_amount) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-700">
                    <span class="text-slate-600 dark:text-slate-300">Paid Amount</span>
                    <span class="font-semibold text-green-700 dark:text-green-400">{{ formatCurrency($paidAmount) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-700">
                    <span class="text-slate-600 dark:text-slate-300">Outstanding Amount</span>
                    <span class="font-semibold {{ $outstandingAmount > 0 ? 'text-red-700 dark:text-red-400' : 'text-green-700 dark:text-green-400' }}">
                        {{ formatCurrency($outstandingAmount) }}
                    </span>
                </div>

                @if($booking->payments->isNotEmpty())
                    <div class="pt-2">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 mb-2">Payment History</h3>
                        <div class="space-y-2">
                            @foreach($booking->payments as $payment)
                                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ ucfirst($payment->payment_method) }}</p>
                                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ $payment->payment_date?->format('M d, Y') ?? 'Pending' }}</p>
                                    </div>
                                <div class="text-right">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency($payment->amount) }}</p>
                                    <span class="text-xs px-2 py-1 rounded-full {{ $payment->status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300' }}">{{ ucfirst($payment->status) }}</span>
                                    @if($payment->is_manual && $payment->status === 'paid')
                                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                            Cleared by admin{{ $payment->createdByAdmin ? ': ' . $payment->createdByAdmin->name : '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @else
                    <p class="text-slate-600 dark:text-slate-300">No payments recorded yet.</p>
                @endif

                @if($outstandingAmount > 0 && in_array($booking->status, ['pending', 'approved']))
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
                    <div id="payment-gateway-actions" class="pt-4 border-t border-slate-200 dark:border-slate-700">
                        <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">Complete payment to conclude your booking.</p>
                        <div class="flex flex-wrap gap-3">
                            @forelse($activeGateways as $gatewayKey => $gatewayRecord)
                                <form method="POST" action="{{ route('student.payments.initialize', ['booking' => $booking->id, 'gateway' => $gatewayKey]) }}">
                                    @csrf
                                    <button type="submit" class="{{ $gatewayStyles[$gatewayKey] ?? 'bg-blue-600' }} text-white px-4 py-2 rounded-lg font-medium hover:opacity-90">
                                        Pay with {{ $gatewayRecord->name }}
                                    </button>
                                </form>
                            @empty
                                <p class="text-sm text-red-600 dark:text-red-400">No active payment gateway is configured by admin.</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </section>

        @if($booking->notes)
            <section class="uniform-card p-6">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">Notes</h2>
                <p class="text-slate-600 dark:text-slate-300">{{ $booking->notes }}</p>
            </section>
        @endif

        @php
            $canCancel = in_array($booking->status, ['pending', 'approved'], true) && $booking->payments->isEmpty();
            $canPay = $outstandingAmount > 0 && in_array($booking->status, ['pending', 'approved'], true) && $activeGateways->isNotEmpty();
            $preferredGateway = isset($activeGateways['paystack']) ? 'paystack' : $activeGateways->keys()->first();
        @endphp

        <div class="flex items-center justify-between">
            <a href="{{ route('student.bookings.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">Back to My Bookings</a>

            <div class="flex items-center gap-3">
                @if($booking->isFullyPaid())
                    <a href="{{ route('student.bookings.receipt', $booking) }}" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 font-medium">Download Receipt</a>
                @endif

                @if($canPay)
                    <form method="POST" action="{{ route('student.payments.initialize', ['booking' => $booking->id, 'gateway' => $preferredGateway]) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">Pay Now</button>
                    </form>
                @endif

                @if($canCancel)
                    <form method="POST" action="{{ route('student.bookings.cancel', $booking) }}" onsubmit="return confirm('Are you sure you want to cancel this booking?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 font-medium">Cancel Booking</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-dashboard-layout>
