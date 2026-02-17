<x-dashboard-layout title="My Payments">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Payment History</h1>
            <p class="text-gray-600 mt-2">View all your payments and transactions</p>
        </div>

        @if($payments->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Booking ID</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Amount</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Method</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($payments as $payment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <a href="{{ route('student.bookings.show', $payment->booking_id) }}" class="text-blue-600 hover:underline">
                                            #{{ $payment->booking_id }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                        {{ getCurrencySymbol() }}{{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ $payment->payment_method }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                            @if($payment->status === 'paid')
                                                bg-green-100 text-green-800
                                            @elseif($payment->status === 'pending')
                                                bg-yellow-100 text-yellow-800
                                            @elseif($payment->status === 'failed')
                                                bg-red-100 text-red-800
                                            @else
                                                bg-gray-100 text-gray-800
                                            @endif
                                        ">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $payments->links() }}
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Payments Yet</h3>
                <p class="text-gray-600 mb-6">You haven't made any payments yet.</p>
                <a href="{{ route('student.bookings.available') }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Browse Rooms
                </a>
            </div>
        @endif
    </div>
</x-dashboard-layout>
