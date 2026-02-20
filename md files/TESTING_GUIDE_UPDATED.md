# Quick Testing Guide - Hostel Management System

## System Overview
This is a Laravel-based hostel management system with three user roles:
- **Students**: Can book rooms, view bookings, and file complaints
- **Managers**: Can manage rooms, approve/reject bookings, and respond to complaints
- **Admin**: Full system access including SMS broadcasting and system settings

## Critical Fixes Applied (Feb 13, 2026)

### ✅ Fixed Issues:

1. **User Profile Route** - Now accessible at `/admin/user-profile`
2. **SMS Broadcast** - Now accessible at `/admin/send-s-m-s` 
3. **Allocation (Beds) Selection** - Fixed database query to use correct field
4. **SQLite Compatibility** - Revenue chart now works with SQLite
5. **Student Complaints** - Form is now visible and functional at `/student/complaints`
6. **Complaint Management** - Admins/Managers can assign and respond to complaints

## How to Test Each Feature

### 1. User Authentication
```
- Test URL: /login
- Create test accounts for each role: student, manager, admin
```

### 2. Student Complaints (NEW)
```
As a Student:
1. Navigate to: /student/complaints
2. Fill in complaint form with:
   - Subject: "Room is too cold"
   - Description: "The AC is not working properly"
   - Related Booking: (Select from dropdown if any)
3. Click "Submit Complaint"
4. View submitted complaints below the form
5. See manager responses when they're added
```

### 3. Admin/Manager Complaint Response
```
As Admin or Manager:
1. Navigate to: /admin/complaints
2. Click on a complaint to edit
3. Fill in "Manager Response" field
4. Optionally assign to a specific manager
5. Update complaint status (open -> in progress -> resolved -> closed)
6. Save changes
7. Student will see the response in their complaints list
```

### 4. User Profile Editing
```
As Any Authenticated User:
1. Navigate to: /admin/user-profile
2. Edit in "Personal Information" tab:
   - Full Name
   - Email Address
   - Phone Number
   - Address
3. (Optional) Change password in "Security" tab
4. Click "Save Changes"
```

### 5. SMS Broadcasting (Admin Only)
```
As Admin:
1. Navigate to: /admin/send-s-m-s
2. Select recipient type:
   - All Students: Sends to all student accounts
   - Specific Hostel: Sends to students in one hostel
   - Specific Student: Sends to one student
3. Type message (max 160 characters)
4. Optionally save as template
5. Click "Send SMS"
6. Verify SMS provider is configured in System Settings
```

### 6. Student Bookings
```
As a Student:
1. Navigate to: /student/bookings (see your bookings)
2. Navigate to: /student/bookings/available (browse rooms)
3. Click "Book Now" on a room
4. Fill booking details
5. Submit booking (goes to pending, requires manager approval)
```

### 7. Manager Booking Approval
```
As Manager:
1. Navigate to: /manager/bookings
2. View pending bookings
3. Click booking to see details
4. Approve or reject the booking
5. Student will see status update
```

## Database Tables (All Created)

- users (authentication)
- students (student details)
- hostels (hostel information)
- rooms (room details)
- beds (individual bed allocations)
- bookings (student bookings)
- allocations (bed assignments)
- payments (payment tracking)
- complaints (complaint records) ← Recently Enhanced
- sms_providers (SMS configuration)
- system_settings (app configuration)

## File Locations for Key Features

```
Routes:        routes/web.php
Student Pages: resources/views/student/
Admin Panel:   app/Filament/
Models:        app/Models/
Controllers:   app/Http/Controllers/
Policies:      app/Policies/
Migrations:    database/migrations/
```

## Common Tasks

### Add a New Hostel
1. Go to `/admin/hostels` (via Filament admin panel)
2. Click "Create"
3. Fill in hostel details
4. Save

### Add a Room to a Hostel
1. Go to `/admin/rooms` (via Filament admin panel)
2. Click "Create"
3. Select hostel
4. Enter room number and type
5. Set capacity and price
6. Save

### Add Beds to a Room
1. Go to `/admin/beds` (via Filament admin panel)
2. Click "Create"
3. Select room
4. Set bed number
5. Save

### View All Complaints
**As Admin**: `/admin/complaints`
**As Manager**: `/admin/complaints` (filtered to assigned ones)
**As Student**: `/student/complaints` (own complaints only)

## Important Notes

1. **Database**: Uses SQLite by default (database/database.sqlite)
2. **Authentication**: Built on Laravel's authentication with roles
3. **Admin Panel**: Built with Filament
4. **Middleware**: Routes are protected by role-based middleware
5. **Authorization**: All sensitive operations use policies

## Troubleshooting

### If pages don't load:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### If forms don't submit:
- Check CSRF token is present
- Verify middleware is correctly applied
- Check database connection

### If SMS doesn't send:
- Verify SMS provider configured in System Settings
- Check API credentials are correct
- Verify student phone numbers are in database

## Version Information

- **Date Created**: February 13, 2026
- **Last Updated**: February 13, 2026
- **Status**: Production Ready (with listed fixes applied)

## Support Resources

See `COMPREHENSIVE_FIXES_APPLIED.md` for detailed technical information about all fixes.
