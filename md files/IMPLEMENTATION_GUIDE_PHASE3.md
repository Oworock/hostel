# Phase 3 Implementation Guide - Admin Features & Integrations

## 
### 1. Database Setup
All migrations have been run. To verify:
```bash
php artisan migrate:status
```

### 2. Access System Settings
```
URL: http://localhost:8000/admin/settings
Login: admin@hostel.com / password
```

## 
### Payment Gateway Configuration

#### Paystack Setup
1. Go to `/admin/settings/payment`
2. Click on Paystack section
3. Enter:
   - **Public Key**: From `https://dashboard.paystack.com/settings/api-keys`
   - **Secret Key**: From same dashboard
   - **Transaction Fee**: Set your fee (recommended: 1-2%)
   - **Status**: Toggle Active
4. Click "Save Paystack Settings"

#### Flutterwave Setup
1. Go to `/admin/settings/payment`
2. Click on Flutterwave section
3. Enter:
   - **Public Key**: From `https://dashboard.flutterwave.com/settings/api`
   - **Secret Key**: From same dashboard
   - **Transaction Fee**: Set your fee (recommended: 2-3%)
   - **Status**: Toggle Active
4. Click "Save Flutterwave Settings"

### SMS Provider Configuration

#### Twilio Setup
1. Go to `/admin/settings/sms`
2. Click on Twilio section
3. Get credentials from `https://www.twilio.com/console`
4. Enter:
   - **API Key**: Your Account SID
   - **API Secret**: Your Auth Token
   - **Sender ID**: Your Twilio phone number
   - **Status**: Toggle Active
5. Click "Save Twilio Settings"

#### Termii Setup
1. Go to `/admin/settings/sms`
2. Click on Termii section
3. Get credentials from `https://www.termii.com/dashboard/settings/api/hash`
4. Enter:
   - **API Key**: Your API key from Termii
   - **Sender ID**: Your sender ID
   - **Config**: `{"channel": "generic"}` or leave empty
   - **Status**: Toggle Active
5. Click "Save Termii Settings"

#### Africa's Talking Setup
1. Go to `/admin/settings/sms`
2. Click on Africa's Talking section
3. Get credentials from `https://africastalking.com/sms/api`
4. Enter:
   - **API Key**: Your API key
   - **API Secret**: Your API Secret
   - **Sender ID**: Your sender ID
   - **Config**: `{"username": "your_username"}`
   - **Status**: Toggle Active
5. Click "Save Africa's Talking Settings"

## 
### Manage Students
1. Go to `/admin/users/students`
2. View all students with pagination
3. Click on any student to view details
4. Update student status (active/inactive/suspended)
5. Add admin notes
6. Delete student if needed

### Manage Managers
1. Go to `/admin/users/managers`
2. View all managers with pagination
3. Click on any manager to view details
4. Assign hostels to manager
5. Update manager status
6. Add admin notes
7. Delete manager if needed

## 
### General Settings
1. Go to `/admin/settings/general`
2. Update:
   - **App Name**: Display name of system
   - **App Description**: System description
   - **Primary Color**: Main theme color (use color picker)
   - **Secondary Color**: Accent color (use color picker)
   - **Currency**: 3-letter code (USD, NGN, GHS, etc)
   - **Timezone**: Select from 500+ options
   - **Support Email**: Contact email
3. Click "Save Changes"

## 
### Create Email Campaign (Coming Soon)
1. Go to `/admin/marketing/campaigns`
2. Click "+ Create Campaign"
3. Fill:
   - Campaign name
   - Campaign type (email/sms/push)
   - Content/message
   - Target audience
   - Schedule (optional)
4. Save as draft or launch

### Create SMS Campaign
1. Go to `/admin/marketing/sms`
2. Click "+ Create SMS Campaign"
3. Fill:
   - Campaign name
   - SMS message (max 160 chars per message)
   - Target: All / Students / Managers / Custom
   - Schedule date/time
4. Save as draft or schedule
5. Send or schedule

##  API Integration Examples

### Using Paystack in Booking Payment

```php
// In PaymentController
$paystack = PaymentGateway::where('name', 'Paystack')
    ->where('is_active', true)->first();

if ($paystack) {
    $paymentUrl = "https://api.paystack.co/transaction/initialize";
    
    $response = Http::post($paymentUrl, [
        'email' => $booking->user->email,
        'amount' => $booking->total_amount * 100, // In kobo
        'currency' => SystemSetting::getSetting('currency'),
    ], [
        'Authorization' => 'Bearer ' . $paystack->secret_key,
    ]);
    
    return redirect($response['data']['authorization_url']);
}
```

### Using Termii for SMS

```php
// In SmsCampaignController
$termii = SmsProvider::where('name', 'Termii')
    ->where('is_active', true)->first();

if ($termii) {
    foreach ($recipients as $phone) {
        Http::post('https://api.ng.termii.com/api/sms/send', [
            'api_key' => $termii->api_key,
            'to' => $phone,
            'from' => $termii->sender_id,
            'sms' => $campaign->message,
        ]);
    }
}
```

## 
### Get Payment Gateway Config
```
GET /api/payment-gateways
Response:
{
    "active_gateways": [
        {
            "name": "Paystack",
            "public_key": "pk_live_xxx",
            "transaction_fee": 1.5
        }
    ]
}
```

### Get SMS Provider Config
```
GET /api/sms-providers
Response:
{
    "active_providers": [
        {
            "name": "Twilio",
            "sender_id": "+1234567890"
        }
    ]
}
```

## 
### Test Payment Gateway Connection
1. Go to `/admin/settings/payment`
2. Enter test keys from provider
3. Enable "Active"
4. Use provider's test mode to verify

### Test SMS Provider Connection
1. Go to `/admin/settings/sms`
2. Enter test credentials
3. Enable "Active"
4. Send test SMS to admin number

## 
### System Settings
```php
// Get setting with default
$appName = SystemSetting::getSetting('app_name', 'Hostel Manager');

// Set setting
SystemSetting::setSetting('app_name', 'My Hostel', 'string');

// In views
@php
$colors = [
    'primary' => SystemSetting::getSetting('primary_color'),
    'secondary' => SystemSetting::getSetting('secondary_color'),
];
@endphp
```

### User Management
```php
// Get user management record
$management = User::find($id)->userManagement;

// Update status
$management->update(['status' => 'active']);

// Check if user is suspended
if ($user->userManagement?->status === 'suspended') {
    abort(403, 'Account suspended');
}
```

### Payment Gateways
```php
// Get active payment gateways
$gateways = PaymentGateway::where('is_active', true)->get();

// Calculate fee
$fee = $amount * ($gateway->transaction_fee / 100);
```

### SMS Campaigns
```php
// Get sent campaigns
$campaigns = SmsCampaign::where('status', 'sent')->get();

// Track delivery
$successRate = ($campaign->successful / $campaign->total_recipients) * 100;
```

echo Advanced Configuration## 

### Custom SMS Configuration
For providers requiring custom config, use JSON:

```json
{
    "account_sid": "your_account_sid",
    "auth_token": "your_auth_token",
    "webhook_url": "https://yourdomain.com/webhooks/sms"
}
```

### Multi-Currency Support
1. Set currency in `/admin/settings/general`
2. Database stores as text
3. Use when calculating payments:

```php
$currency = SystemSetting::getSetting('currency', 'USD');
// $currency = 'NGN', 'USD', 'GHS', etc
```

## 
### For Production
1. **Never commit API keys** to version control
2. **Use environment variables**:
   ```env
   PAYSTACK_KEY=pk_live_xxx
   FLUTTERWAVE_KEY=rave_xxx
   ```
3. **Encrypt sensitive data** in database
4. **Use HTTPS only** for payment pages
5. **Validate webhooks** using secrets
6. **Log API calls** securely
7. **Rate limit** payment attempts

### Environment Variables Setup
```bash
# .env
PAYSTACK_PUBLIC_KEY=pk_live_xxx
PAYSTACK_SECRET_KEY=sk_live_xxx
FLUTTERWAVE_PUBLIC_KEY=rave_live_xxx
FLUTTERWAVE_SECRET_KEY=rave_live_xxx

TWILIO_ACCOUNT_SID=AC_xxx
TWILIO_AUTH_TOKEN=xxx
TERMII_API_KEY=xxx
```

## 
For implementation help:
1. Check `/PHASE_3_SUMMARY.md` for complete feature list
2. Review `/API_REFERENCE.md` for code examples
3. Check `/TESTING_GUIDE.md` for workflow testing

---

**Last Updated:** February 12, 2026
**Version:** 3.0
**Status:** Complete & Ready for Integration

