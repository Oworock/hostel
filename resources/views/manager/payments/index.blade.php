<x-dashboard-layout title="Payments">
    <x-slot name="sidebar">
        @include('components.manager-sidebar')
    </x-slot>

    <div class="uniform-page">
        <div class="uniform-header">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Payment Records</h1>
            <p class="text-slate-600 dark:text-slate-300 mt-1">All payments from students in your managed hostels.</p>
        </div>

        @if($payments->count() > 0)
            <div class="uniform-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px]">
                        <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Booking ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($payments as $payment)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                    <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">
                                        <div class="flex items-center gap-3">
                                            @if($payment->user->profile_image)
                                                <img src="{{ asset('storage/' . $payment->user->profile_image) }}" alt="{{ $payment->user->name }}" class="w-9 h-9 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                            @else
                                                <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ substr($payment->user->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium">{{ $payment->user->name }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $payment->user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('manager.bookings.show', $payment->booking_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">#{{ $payment->booking_id }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency($payment->amount) }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 capitalize">
                                        {{ $payment->payment_method === 'manual_admin' ? 'Manual (Admin)' : $payment->payment_method }}
                                        @if($payment->is_manual && $payment->createdByAdmin)
                                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">Approved by {{ $payment->createdByAdmin->name }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                            @if($payment->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                            @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                            @elseif($payment->status === 'failed') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                            @else bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300
                                            @endif">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $payments->links() }}
                </div>
            </div>
        @else
            <div class="uniform-card p-10 text-center">
                <svg class="w-14 h-14 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-2">No Payments Yet</h3>
                <p class="text-slate-600 dark:text-slate-300">No payments have been recorded for your hostels.</p>
            </div>
        @endif
    </div>
</x-dashboard-layout>
