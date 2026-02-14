@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Payment Management</h1>

    <!-- Payment Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Paid Full</p>
            <p class="text-3xl font-bold text-green-600">{{ $paidFull }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Partial Payment</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $partialPayment }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Still Owing</p>
            <p class="text-3xl font-bold text-red-600">{{ $stillOwing }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('manager.payments.index') }}" class="flex gap-4">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="paid_full" {{ request('status') === 'paid_full' ? 'selected' : '' }}>Paid Full</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial Payment</option>
                <option value="owing" {{ request('status') === 'owing' ? 'selected' : '' }}>Still Owing</option>
            </select>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Student</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Room</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Total Amount</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Paid</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Last Payment</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $payment->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $payment->room->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">₦{{ number_format($payment->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">₦{{ number_format($payment->amount_paid, 2) }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($payment->payment_status === 'paid_full')
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Paid Full</span>
                            @elseif($payment->payment_status === 'partial')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Partial</span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Owing</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->last_payment_date?->format('M d, Y') ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No payments found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($payments->hasPages())
        <div class="mt-6">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
