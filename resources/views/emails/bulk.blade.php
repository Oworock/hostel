<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px;">
            {!! $message !!}
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
                <p>This is an automated message from {{ config('app.name') }}</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name' )}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
