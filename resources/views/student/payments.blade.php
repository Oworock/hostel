<x-dashboard-layout title="My Payments">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">My Payments</h1>
            <p class="text-slate-600 dark:text-slate-300 mt-1">Review your payment records and outstanding balance.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
                <p class="text-slate-600 dark:text-slate-300 text-sm">Total Paid</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">₦{{ number_format($totalPaid, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
                <p class="text-slate-600 dark:text-slate-300 text-sm">Outstanding Balance</p>
                <p class="text-3xl font-bold text-red-600 dark:text-red-400">₦{{ number_format($totalOwing, 2) }}</p>
            </div>
        </div>

        @if($pendingPayments->count() > 0)
            <section class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-4">Pending Payments</h2>
                <div class="space-y-3">
                    @foreach($pendingPayments as $payment)
                        <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $payment->booking->room->name ?? $payment->booking->room->room_number ?? 'Booking #' . $payment->booking_id }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-300">Amount: ₦{{ number_format($payment->amount, 2) }}</p>
                            </div>
                            <form action="{{ route('student.payments.complete', $payment->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Complete Payment</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Payment History</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px]">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Booking</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($paymentHistory as $payment)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $payment->payment_date?->format('M d, Y') ?? 'Pending' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $payment->booking->room->name ?? $payment->booking->room->room_number ?? 'Booking #' . $payment->booking_id }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-slate-100">₦{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    @if($payment->payment_method)
                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if(in_array($payment->status, ['completed', 'paid']))
                                        <span class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 rounded-full text-xs font-semibold">Completed</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 rounded-full text-xs font-semibold">Pending</span>
                                    @else
                                        <span class="px-3 py-1 bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 rounded-full text-xs font-semibold">Failed</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if(in_array($payment->status, ['completed', 'paid']))
                                        <a href="{{ route('student.payments.receipt', $payment->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Download Receipt</a>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-600 dark:text-slate-300">No payment history.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if($paymentHistory->hasPages())
            <div>
                {{ $paymentHistory->links() }}
            </div>
        @endif

        <section class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">Need Help?</h3>
            <p class="text-slate-600 dark:text-slate-300">If you have questions about your payments, contact support.</p>
        </section>
    </div>
</x-dashboard-layout>
