@extends('layouts.app')

@section('title', __('Salary Payslip'))

@php
    $monthName = ($payment->payment_month && $payment->payment_month >= 1 && $payment->payment_month <= 12)
        ? now()->startOfYear()->month((int) $payment->payment_month)->format('F')
        : '-';
@endphp

@section('content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 space-y-6 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between border-b border-slate-200 dark:border-slate-700 pb-5">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-300">{{ get_setting('app_name', config('app.name')) }}</p>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ __('Salary Payslip') }}</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Reference') }}: {{ $payment->reference ?: '-' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ $pdfUrl }}" class="inline-flex items-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 text-sm font-semibold">
                    {{ __('Download PDF') }}
                </a>
                <a href="{{ $imageUrl }}" class="inline-flex items-center rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm font-semibold">
                    {{ __('Download Image') }}
                </a>
                <a href="{{ $printUrl }}" class="inline-flex items-center rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm font-semibold">
                    {{ __('Print') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/40">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Amount Paid') }}</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ formatCurrency((float) $payment->amount, compact: false) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/40">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Pay Period') }}</p>
                <p class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $monthName }} {{ $payment->payment_year ?: '-' }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/40">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Payment Method') }}</p>
                <p class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ $payment->payment_method ?: '-' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr><td class="px-4 py-2 font-semibold">{{ __('Staff Name') }}</td><td class="px-4 py-2">{{ $payment->staffMember?->full_name ?: '-' }}</td></tr>
                        <tr><td class="px-4 py-2 font-semibold">{{ __('Staff ID') }}</td><td class="px-4 py-2">{{ $payment->staffMember?->employee_code ?: '-' }}</td></tr>
                        <tr><td class="px-4 py-2 font-semibold">{{ __('Department') }}</td><td class="px-4 py-2">{{ $payment->staffMember?->department ?: '-' }}</td></tr>
                        <tr><td class="px-4 py-2 font-semibold">{{ __('Category') }}</td><td class="px-4 py-2">{{ $payment->staffMember?->category ?: '-' }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr><td class="px-4 py-2 font-semibold">{{ __('Paid At') }}</td><td class="px-4 py-2">{{ $payment->paid_at?->format('Y-m-d H:i') ?: '-' }}</td></tr>
                        <tr><td class="px-4 py-2 font-semibold">{{ __('Reference') }}</td><td class="px-4 py-2">{{ $payment->reference ?: '-' }}</td></tr>
                        <tr><td class="px-4 py-2 font-semibold">{{ __('Email') }}</td><td class="px-4 py-2">{{ $payment->staffMember?->email ?: '-' }}</td></tr>
                        <tr><td class="px-4 py-2 font-semibold">{{ __('Phone') }}</td><td class="px-4 py-2">{{ $payment->staffMember?->phone ?: '-' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

@if($printMode ?? false)
    <script>window.print()</script>
@endif
@endsection
