<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Salary Payslip') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .box { border: 1px solid #cbd5e1; border-radius: 10px; padding: 18px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { border: 1px solid #e2e8f0; padding: 8px; vertical-align: top; }
        .label { color: #475569; width: 180px; }
        .head { border-bottom: 1px solid #e2e8f0; margin-bottom: 12px; padding-bottom: 10px; }
        .badge { display: inline-block; background: #ecfeff; color: #0e7490; border: 1px solid #a5f3fc; border-radius: 999px; padding: 4px 10px; font-size: 10px; }
        .amount { font-size: 24px; font-weight: 700; color: #059669; margin: 8px 0; }
    </style>
</head>
<body>
@php
    $monthName = ($payment->payment_month && $payment->payment_month >= 1 && $payment->payment_month <= 12)
        ? now()->startOfYear()->month((int) $payment->payment_month)->format('F')
        : '-';
@endphp
<div class="box">
    <div class="head">
        <h2 style="margin: 0 0 4px;">{{ get_setting('app_name', config('app.name', 'Hostel System')) }}</h2>
        <h3 style="margin: 0 0 4px;">{{ __('Salary Payslip') }}</h3>
        <span class="badge">{{ __('Reference') }}: {{ $payment->reference ?: '-' }}</span>
        <div class="amount">{{ formatCurrency((float) $payment->amount, compact: false) }}</div>
    </div>
    <table class="grid">
        <tr><td class="label">{{ __('Reference') }}</td><td>{{ $payment->reference ?: '-' }}</td></tr>
        <tr><td class="label">{{ __('Staff Name') }}</td><td>{{ $payment->staffMember?->full_name ?: '-' }}</td></tr>
        <tr><td class="label">{{ __('Staff ID') }}</td><td>{{ $payment->staffMember?->employee_code ?: '-' }}</td></tr>
        <tr><td class="label">{{ __('Department') }}</td><td>{{ $payment->staffMember?->department ?: '-' }}</td></tr>
        <tr><td class="label">{{ __('Category') }}</td><td>{{ $payment->staffMember?->category ?: '-' }}</td></tr>
        <tr><td class="label">{{ __('Amount') }}</td><td>{{ formatCurrency((float) $payment->amount, compact: false) }}</td></tr>
        <tr><td class="label">{{ __('Month') }}</td><td>{{ $monthName }}</td></tr>
        <tr><td class="label">{{ __('Year') }}</td><td>{{ $payment->payment_year ?: '-' }}</td></tr>
        <tr><td class="label">{{ __('Payment Method') }}</td><td>{{ $payment->payment_method ?: '-' }}</td></tr>
        <tr><td class="label">{{ __('Paid At') }}</td><td>{{ $payment->paid_at?->format('Y-m-d H:i') ?: '-' }}</td></tr>
    </table>
</div>
</body>
</html>
