<x-dashboard-layout :title="__('Referral Dashboard')">
    <x-slot name="sidebar">
        @include('components.student-sidebar')
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <h1 class="text-xl font-semibold text-blue-900">{{ __('Referral Dashboard') }}</h1>
            <p class="text-sm text-blue-800 mt-1">
                {{ __('Your referral code') }}:
                <span class="font-mono font-semibold">{{ $agent->referral_code }}</span>
            </p>
            <p class="text-sm text-blue-800 mt-1 break-all">
                {{ __('Referral link') }}: <span class="font-mono">{{ $agent->referralUrl() }}</span>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-xs text-slate-500">{{ __('Balance') }}</p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency($agent->balance, false) }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-xs text-slate-500">{{ __('Total Earned') }}</p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency($agent->total_earned, false) }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-xs text-slate-500">{{ __('Total Paid Out') }}</p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency($agent->total_paid, false) }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-xs text-slate-500">{{ __('Referred Students') }}</p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $agent->referred_students_count }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('Earned History') }}</h2>
                <div class="space-y-2 max-h-96 overflow-auto">
                    @forelse($agent->commissions as $row)
                        <div class="rounded border border-slate-200 dark:border-slate-700 px-3 py-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency($row->amount, false) }}</span>
                                <span class="text-xs px-2 py-0.5 rounded {{ $row->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ strtoupper($row->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ __('Booking') }} #{{ $row->booking_id }} • {{ optional($row->earned_at)->format('M d, Y H:i') }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('No referral earnings yet.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 space-y-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Request Payout') }}</h2>
                <form method="POST" action="{{ route('student.referrals.payouts.store') }}" class="space-y-3">
                    @csrf
                    <input type="number" step="0.01" min="0.01" name="amount" placeholder="{{ __('Amount') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2" required>
                    <input type="text" name="bank_name" placeholder="{{ __('Bank Name') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2">
                    <input type="text" name="account_name" placeholder="{{ __('Account Name') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2">
                    <input type="text" name="account_number" placeholder="{{ __('Account Number') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2">
                    <textarea name="note" placeholder="{{ __('Note (optional)') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2"></textarea>
                    <button type="submit" class="w-full rounded-lg bg-blue-600 text-white py-2 font-semibold hover:bg-blue-700">{{ __('Submit Payout Request') }}</button>
                </form>

                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-2">{{ __('Recent Payout Requests') }}</h3>
                    <div class="space-y-2 max-h-48 overflow-auto">
                        @forelse($agent->payoutRequests as $row)
                            <div class="rounded border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm flex items-center justify-between">
                                <span class="text-slate-800 dark:text-slate-200">{{ formatCurrency($row->amount, false) }}</span>
                                <span class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ strtoupper($row->status) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">{{ __('No payout requests yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('referral.partials.popup', ['dismissRoute' => route('student.referrals.popup.dismiss')])
</x-dashboard-layout>
