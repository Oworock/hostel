# Quick Start Guide - Admin Functions

## Login Information

### Test Credentials
All accounts share the same password: **Password123**

| Role | Email | Purpose |
|------|-------|---------|
| Admin | admin@hostel.com | Full system control |
| Manager | manager@hostel.com | Hostel management |
| Student | student@hostel.com | Booking & profile |

---

## Admin Login & Dashboard

1. Navigate to: `/login`
2. Enter: `admin@hostel.com`
3. Password: `Password123`
4. Access admin panel at: `/admin`

---

## Quick Tasks

### Create a New Manager
1. Go to: Admin → User Management → Users
2. Click "Create"
3. Fill in:
   - Name
   - Email (unique)
   - Password
   - Select Role: **Manager**
   - Select Hostel: Choose from list
   - Toggle Active: ON
4. Save

### Create a New Hostel
1. Go to: Admin → Hostel Management → Hostels
2. Click "Create"
3. Fill in:
   - Name
   - Description
   - Address
   - City
   - Phone
   - Select Owner/Admin
   - Toggle Active: ON
4. Save

### Configure Payment Gateway (Paystack Example)
1. Go to: Admin → Payments → Payment Gateways
2. Click "Create"
3. Fill in:
   - Name: `Paystack`
   - Public Key: Your Paystack public key
   - Secret Key: Your Paystack secret key
   - Transaction Fee: `1.5` (percentage)
   - Toggle Active: ON
4. Save

### Configure SMS Provider (Twilio Example)
1. Go to: Admin → SMS & Marketing → SMS Providers
2. Click "Create"
3. Fill in:
   - Name: `Twilio`
   - API Key: Your Twilio API key
   - API Secret: Your Twilio auth token (if needed)
   - Sender ID: Your Twilio phone number
   - Config: Add any extra settings
   - Toggle Active: ON
4. Save

### Update System Settings
1. Go to: Admin → System → System Settings
2. Edit existing settings or create new ones:
   - Click on a setting to edit
   - Or click "Create" to add new
3. Examples of settings:
   - `app_name` = "My Hostel Management"
   - `app_color` = "#FF6B35"
   - `company_email` = "admin@hostel.com"
   - `company_phone` = "+1234567890"

### Add Rooms to a Hostel
1. Manager logs in to their dashboard
2. Go to: Dashboard → Rooms
3. Click "Create"
4. Fill in room details:
   - Room name (e.g., "Room 101")
   - Room type (e.g., "4-Bed Dorm")
   - Price per bed
   - Capacity
5. Save

### Add Beds to a Room
1. Admin or Manager goes to: Admin → Hostel Management → Beds
2. Click "Create"
3. Fill in:
   - Select Room
   - Bed number
   - Bed type
4. Save

---

## Key Features by Role

### Admin Capabilities
- ✅ Manage all users (create, edit, delete)
- ✅ Create and manage hostels
- ✅ Configure payment gateways
- ✅ Configure SMS providers
- ✅ Update system settings
- ✅ View all bookings and payments
- ✅ View all complaints
- ✅ Generate reports

### Manager Capabilities
- ✅ Manage rooms in assigned hostel
- ✅ Manage beds in assigned rooms
- ✅ Approve/reject student bookings
- ✅ View student information
- ✅ View hostel dashboard
- ✅ Track occupancy rates
- ❌ Cannot manage other hostels
- ❌ Cannot access payment settings

### Student Capabilities
- ✅ Browse available rooms
- ✅ Make bookings
- ✅ View booking history
- ✅ Process payments
- ✅ Update profile
- ✅ Submit complaints
- ❌ Cannot manage rooms
- ❌ Cannot manage other students

---

## Navigation Tips

### Admin Panel Structure
```
Admin Dashboard
├── User Management
│   ├── Users (all users)
│   └── Students (student specific)
├── Hostel Management
│   ├── Hostels
│   ├── Rooms
│   ├── Beds
│   └── Allocations
├── Payments
│   └── Payment Gateways
├── SMS & Marketing
│   └── SMS Providers
├── Operations
│   ├── Bookings
│   ├── Payments
│   └── Complaints
└── System
    └── System Settings
```

### Manager Dashboard Structure
```
Manager Dashboard
├── Rooms (manage rooms)
├── Bookings (approve/reject)
└── Students (view students)
```

### Student Dashboard Structure
```
Student Dashboard
├── Browse Rooms (view available)
├── My Bookings (view bookings)
└── Account (update profile)
```

---

## Common Issues & Solutions

### Issue: Can't login as admin
**Solution:**
- Verify email is: `admin@hostel.com`
- Verify password is: `Password123`
- Check browser cookies are enabled
- Clear browser cache and try again

### Issue: Payment gateway not working
**Solution:**
- Verify keys are correct in Payment Gateways settings
- Toggle "Active" is ON
- Test with sandbox credentials first
- Check payment gateway account is active

### Issue: SMS not sending
**Solution:**
- Verify SMS Provider is enabled (Active toggle ON)
- Check API credentials are correct
- Verify SMS provider account has balance
- Check sender ID is registered with provider

### Issue: Manager can't see their hostel
**Solution:**
- Verify manager's `hostel_id` is set in Users
- Verify the hostel exists and is active
- Check manager's role is set to "manager"
- Refresh page or clear cache

---

## Database Info

### Main Tables
- `users` - All user accounts
- `hostels` - Hostel information
- `rooms` - Room information
- `beds` - Bed information
- `bookings` - Student bookings
- `payments` - Payment records
- `system_settings` - System configuration
- `payment_gateways` - Payment integration settings
- `sms_providers` - SMS integration settings
- `complaints` - User complaints

---

## API & Configuration Files

### Important Files
- `/config/auth.php` - Authentication settings
- `/config/fortify.php` - Laravel Fortify config
- `/database/migrations/` - Database schema
- `/app/Models/` - Data models
- `/app/Filament/Resources/` - Admin panel resources

---

## Useful Commands

```bash
# Clear all caches
php artisan cache:clear

# Clear compiled views
php artisan view:clear

# Migrate database
php artisan migrate

# Seed test data
php artisan db:seed

# Create backup
php artisan backup:run

# View logs
tail -f storage/logs/laravel.log
```

---

## Next Steps

1. **Login** with admin credentials
2. **Configure Payment Gateways** (Paystack/Flutterwave)
3. **Configure SMS Providers** (Twilio/Termii)
4. **Create Hostels** and assign managers
5. **Add Rooms & Beds** to each hostel
6. **Customize System Settings** (colors, name, etc.)
7. **Invite Managers** to manage hostels
8. **Start accepting student bookings**

---

## Support

For detailed guides, see:
- `/ADMIN_SETUP_GUIDE.md` - Complete admin guide
- `/README.md` - Project overview
- `/SETUP_GUIDE.md` - Installation guide

For issues, check the error logs:
```bash
tail storage/logs/laravel.log
```
