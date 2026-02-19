<x-dashboard-layout title="Payments">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Payment Management</h1>
            <p class="text-slate-600 dark:text-slate-300 mt-1">Track settled, partial, and outstanding student payments.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
                <p class="text-slate-600 dark:text-slate-300 text-sm">Paid Full</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $paidFull }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
                <p class="text-slate-600 dark:text-slate-300 text-sm">Partial Payment</p>
                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $partialPayment }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
                <p class="text-slate-600 dark:text-slate-300 text-sm">Still Owing</p>
                <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stillOwing }}</p>
            </div>
        </div>

        <section class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 p-6">
            <form method="GET" action="{{ route('manager.payments.index') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-2">Status</label>
                    <select name="status" class="px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="paid_full" {{ request('status') === 'paid_full' ? 'selected' : '' }}>Paid Full</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial Payment</option>
                        <option value="owing" {{ request('status') === 'owing' ? 'selected' : '' }}>Still Owing</option>
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Apply Filter</button>
            </form>
        </section>

        <section class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-transparent dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px]">
                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Paid</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Last Payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100 font-medium">{{ $payment->user->name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ $payment->room->name ?? $payment->room->room_number ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ formatCurrency($payment->total_amount) }}</td>
                                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">{{ formatCurrency($payment->amount_paid) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if($payment->payment_status === 'paid_full')
                                        <span class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 rounded-full text-xs font-semibold">Paid Full</span>
                                    @elseif($payment->payment_status === 'partial')
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 rounded-full text-xs font-semibold">Partial</span>
                                    @else
                                        <span class="px-3 py-1 bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 rounded-full text-xs font-semibold">Owing</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $payment->last_payment_date?->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-600 dark:text-slate-300">No payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if($payments->hasPages())
            <div>
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
