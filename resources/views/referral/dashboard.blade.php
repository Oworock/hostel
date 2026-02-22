@extends('layouts.app')

@section('title', 'Referral Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Referral Dashboard</h1>
                <p class="text-sm text-gray-600">Welcome, {{ $agent->name }}</p>
            </div>
            <form method="POST" action="{{ route('referral.logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
            </form>
        </div>

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
            Referral Link: <span class="font-mono break-all">{{ $agent->referralUrl() }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-xl bg-white p-4 border"><p class="text-xs text-gray-500">Balance</p><p class="text-xl font-semibold">{{ formatCurrency($agent->balance, false) }}</p></div>
            <div class="rounded-xl bg-white p-4 border"><p class="text-xs text-gray-500">Total Earned</p><p class="text-xl font-semibold">{{ formatCurrency($agent->total_earned, false) }}</p></div>
            <div class="rounded-xl bg-white p-4 border"><p class="text-xs text-gray-500">Total Paid Out</p><p class="text-xl font-semibold">{{ formatCurrency($agent->total_paid, false) }}</p></div>
            <div class="rounded-xl bg-white p-4 border"><p class="text-xs text-gray-500">Referred Students</p><p class="text-xl font-semibold">{{ $agent->referred_students_count }}</p></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-xl bg-white border p-4">
                <h2 class="text-lg font-semibold mb-3">Commission History</h2>
                <div class="space-y-2 max-h-80 overflow-auto">
                    @forelse($agent->commissions as $row)
                        <div class="rounded border px-3 py-2">
                            <div class="flex justify-between text-sm">
                                <span>#{{ $row->booking_id }} - {{ formatCurrency($row->amount, false) }}</span>
                                <span class="px-2 py-0.5 rounded text-xs {{ $row->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ strtoupper($row->status) }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ optional($row->earned_at)->format('M d, Y H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No commission records yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white border p-4 space-y-4">
                <h2 class="text-lg font-semibold">Request Payout</h2>
                <form method="POST" action="{{ route('referral.payouts.store') }}" class="space-y-3">
                    @csrf
                    <input type="number" step="0.01" min="0" name="amount" placeholder="Amount" class="w-full rounded-lg border px-3 py-2" required>
                    <input type="text" name="bank_name" placeholder="Bank Name" class="w-full rounded-lg border px-3 py-2">
                    <input type="text" name="account_name" placeholder="Account Name" class="w-full rounded-lg border px-3 py-2">
                    <input type="text" name="account_number" placeholder="Account Number" class="w-full rounded-lg border px-3 py-2">
                    <textarea name="note" placeholder="Note (optional)" class="w-full rounded-lg border px-3 py-2"></textarea>
                    <button type="submit" class="w-full rounded-lg bg-blue-600 text-white py-2 font-semibold hover:bg-blue-700">Submit Request</button>
                </form>

                <div>
                    <h3 class="text-sm font-semibold mb-2">Recent Payout Requests</h3>
                    <div class="space-y-2 max-h-40 overflow-auto">
                        @forelse($agent->payoutRequests as $row)
                            <div class="rounded border px-3 py-2 text-sm flex items-center justify-between">
                                <span>{{ formatCurrency($row->amount, false) }}</span>
                                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700">{{ strtoupper($row->status) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500">No payout requests yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('referral.partials.popup', ['dismissRoute' => route('referral.popup.dismiss')])
@endsection
