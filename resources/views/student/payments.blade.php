@extends('layouts.app')

@section('title', 'My Payments')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">My Payments</h1>

    <!-- Payment Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <p class="text-blue-900 text-sm font-medium">Total Paid</p>
            <p class="text-3xl font-bold text-blue-600">₦{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <p class="text-red-900 text-sm font-medium">Outstanding Balance</p>
            <p class="text-3xl font-bold text-red-600">₦{{ number_format($totalOwing, 2) }}</p>
        </div>
    </div>

    <!-- Pending Payments -->
    @if($pendingPayments->count() > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
            <h2 class="text-lg font-semibold text-yellow-900 mb-4">Pending Payments</h2>
            <div class="space-y-3">
                @foreach($pendingPayments as $payment)
                    <div class="flex items-center justify-between bg-white p-4 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $payment->booking->room->name }}</p>
                            <p class="text-sm text-gray-600">Amount: ₦{{ number_format($payment->amount, 2) }}</p>
                        </div>
                        <form action="{{ route('student.payments.complete', $payment->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                Complete Payment
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Payment History -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="border-b bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Payment History</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Date</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Booking</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Amount</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Method</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($paymentHistory as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $payment->payment_date?->format('M d, Y') ?? 'Pending' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $payment->booking->room->name }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">₦{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($payment->payment_method)
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($payment->status === 'completed')
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Completed</span>
                                @elseif($payment->status === 'pending')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Pending</span>
                                @else
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Failed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($payment->status === 'completed')
                                    <a href="{{ route('student.payments.receipt', $payment->id) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                        Download Receipt
                                    </a>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No payment history</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($paymentHistory->hasPages())
        <div class="mt-6">
            {{ $paymentHistory->links() }}
        </div>
    @endif

    <!-- Need Help -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-2">Need Help?</h3>
        <p class="text-blue-800">If you have any questions about your payments or need to make a payment, please contact us at support@hostel.com or call +234 XXX XXXX XXX</p>
    </div>
</div>
@endsection
