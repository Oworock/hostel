<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('Payment Receipt - Booking #:id', ['id' => $booking->id]) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .receipt-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border: 1px solid #ddd;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #ddd;
            font-size: 13px;
        }
        
        .info-row label {
            font-weight: bold;
            color: #666;
        }
        
        .info-row .value {
            text-align: right;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 12px;
        }

        .payments-table th,
        .payments-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .payments-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
            margin-top: 15px;
        }
        
        .payment-status {
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            margin: 15px 0;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #999;
        }
        
        .receipt-id {
            text-align: center;
            margin-bottom: 20px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>{{ config('app.name', 'Hostel System') }}</h1>
            <p>{{ __('Booking Receipt') }}</p>
            <p>{{ __('Receipt #:id', ['id' => $booking->id]) }}</p>
        </div>

        <div class="receipt-id">
            Date: {{ now()->format('d M Y H:i:s') }}
        </div>

        <div class="section">
            <div class="section-title">Student Information</div>
            <div class="info-row">
                <label>{{ __('Name:') }}</label>
                <div class="value">{{ $booking->user->name }}</div>
            </div>
            <div class="info-row">
                <label>{{ __('Email:') }}</label>
                <div class="value">{{ $booking->user->email }}</div>
            </div>
            <div class="info-row">
                <label>{{ __('Phone:') }}</label>
                <div class="value">{{ $booking->user->phone ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">{{ __("Booking Details") }}</div>
            <div class="info-row">
                <label>{{ __('Hostel:') }}</label>
                <div class="value">{{ $booking->room->hostel->name }}</div>
            </div>
            <div class="info-row">
                <label>{{ __('Room:') }}</label>
                <div class="value">{{ $booking->room->room_number }}</div>
            </div>
            <div class="info-row">
                <label>{{ __('Bed:') }}</label>
                <div class="value">{{ $booking->bed ? $booking->bed->bed_number : 'N/A' }}</div>
            </div>
            <div class="info-row">
                <label>{{ __('Check-in Date:') }}</label>
                <div class="value">{{ $booking->check_in_date->format('d M Y') }}</div>
            </div>
            <div class="info-row">
                <label>{{ __('Check-out Date:') }}</label>
                <div class="value">{{ $booking->check_out_date->format('d M Y') }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">{{ __("Payment Information") }}</div>
            @if ($booking->payments->count() > 0)
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>{{ __("Amount") }}</th>
                            <th>{{ __("Method") }}</th>
                            <th>{{ __("Status") }}</th>
                            <th>{{ __("Date") }}</th>
                            <th>{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($booking->payments as $payment)
                            <tr>
                                <td>{{ formatCurrency($payment->amount) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                                <td>{{ ucfirst($payment->status) }}</td>
                                <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : __('N/A') }}</td>
                                <td>
                                    @if($payment->is_manual && $payment->status === 'paid')
                                        Cleared by admin{{ $payment->createdByAdmin ? ': ' . $payment->createdByAdmin->name : '' }}
                                    @else
                                        {{ $payment->transaction_id ?: '-' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="info-row">
                    <label>{{ __('No payments recorded') }}</label>
                </div>
            @endif
        </div>

        <div class="section">
            <div class="total-row">
                <label>{{ __('Total Amount:') }}</label>
                <div>{{ formatCurrency($booking->total_amount) }}</div>
            </div>
            @php
                $paidTotal = (float) $booking->payments->where('status', 'paid')->sum('amount');
                $balance = max(0, (float) $booking->total_amount - $paidTotal);
            @endphp
            <div class="info-row">
                <label>{{ __('Total Paid:') }}</label>
                <div class="value">{{ formatCurrency($paidTotal) }}</div>
            </div>
            <div class="info-row">
                <label>{{ __('Outstanding:') }}</label>
                <div class="value">{{ formatCurrency($balance) }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">{{ __("Booking Status") }}</div>
            @php
                $statusClass = match($booking->status) {
                    'approved', 'completed' => 'status-paid',
                    'pending' => 'status-pending',
                    default => ''
                };
            @endphp
            <div class="payment-status {{ $statusClass }}">
                {{ ucfirst($booking->status) }}
            </div>
        </div>

        <div class="footer">
            <p>Thank you for using {{ config('app.name', 'Hostel System') }}!</p>
            <p>{{ __('This is a computer-generated receipt. Please keep it safe for your records.') }}</p>
        </div>
    </div>
</body>
</html>
