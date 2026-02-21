<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student ID Card</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            font-family: DejaVu Sans, Arial, sans-serif;
            background: #f1f5f9;
        }
        .card-wrap {
            width: 760px;
            margin: 0 auto;
        }
        .meta {
            margin-top: 12px;
            font-size: 11px;
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="card-wrap">
        <img src="{{ $imageDataUri }}" alt="Student ID Card" style="width:760px; max-width:100%; display:block;">
        <p class="meta">Booking #{{ $booking->id }} | {{ $student->name }}</p>
    </div>
</body>
</html>
