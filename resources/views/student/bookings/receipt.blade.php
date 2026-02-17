<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - Booking #{{ $booking->id }}</title>
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
            <p>Booking Receipt</p>
            <p>Receipt #{{ $booking->id }}</p>
        </div>

        <div class="receipt-id">
            Date: {{ now()->format('d M Y H:i:s') }}
        </div>

        <div class="section">
            <div class="section-title">Student Information</div>
            <div class="info-row">
                <label>Name:</label>
                <div class="value">{{ $booking->user->name }}</div>
            </div>
            <div class="info-row">
                <label>Email:</label>
                <div class="value">{{ $booking->user->email }}</div>
            </div>
            <div class="info-row">
                <label>Phone:</label>
                <div class="value">{{ $booking->user->phone ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Booking Details</div>
            <div class="info-row">
                <label>Hostel:</label>
                <div class="value">{{ $booking->room->hostel->name }}</div>
            </div>
            <div class="info-row">
                <label>Room:</label>
                <div class="value">{{ $booking->room->room_number }}</div>
            </div>
            <div class="info-row">
                <label>Bed:</label>
                <div class="value">{{ $booking->bed ? $booking->bed->bed_number : 'N/A' }}</div>
            </div>
            <div class="info-row">
                <label>Check-in Date:</label>
                <div class="value">{{ $booking->check_in_date->format('d M Y') }}</div>
            </div>
            <div class="info-row">
                <label>Check-out Date:</label>
                <div class="value">{{ $booking->check_out_date->format('d M Y') }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Payment Information</div>
            @if ($booking->payments->count() > 0)
                @foreach ($booking->payments as $payment)
                    <div class="info-row">
                        <label>Amount:</label>
                        <div class="value">{{ getCurrencySymbol() }}{{ number_format($payment->amount, 2) }}</div>
                    </div>
                    <div class="info-row">
                        <label>Payment Method:</label>
                        <div class="value">{{ ucfirst($payment->payment_method) }}</div>
                    </div>
                    <div class="info-row">
                        <label>Payment Date:</label>
                        <div class="value">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <label>Status:</label>
                        <div class="value">{{ ucfirst($payment->status) }}</div>
                    </div>
                @endforeach
            @else
                <div class="info-row">
                    <label>No payments recorded</label>
                </div>
            @endif
        </div>

        <div class="section">
            <div class="total-row">
                <label>Total Amount:</label>
                <div>{{ getCurrencySymbol() }}{{ number_format($booking->total_amount, 2) }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Booking Status</div>
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
            <p>This is a computer-generated receipt. Please keep it safe for your records.</p>
        </div>
    </div>
</body>
</html>
