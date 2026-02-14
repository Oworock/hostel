# Hostel Management System - Setup Guide

##  System Created Successfully

Your Laravel Hostel Management System with 3 user levels has been fully set up and ready to use.

## 
### 1. Start the Development Server
```bash
php artisan serve
npm run dev  # In another terminal
```

Visit: `http://localhost:8000`

### 2. Default Test Accounts

**Admin Account (Full System Access)**
```
Email: admin@hostel.com
Password: password
```

**Hostel Owner Account**
```
Email: owner@hostel.com
Password: password
```

**Hostel Manager Account**
```
Email: manager@hostel.com
Password: password
```

**Student Accounts (5 available)**
```
Email: student1@email.com through student5@email.com
Password: password (all use same password)
```

## 
- Browse available rooms and bed spaces### 
- Make booking requests for rooms
- Track booking status (pending, approved, rejected, completed, cancelled)
- View personal dashboard with active bookings
- Cancel pending or approved bookings
- Check payment status

- Manage all rooms in assigned hostel### 
- Create new rooms with details (type, capacity, price)
- Update room information
- View all booking requests for hostel
- Approve or reject student booking requests
- Monitor room occupancy rates
- Access manager dashboard with hostel statistics

- Create and manage multiple hostels### 
- Assign managers to hostels
- View system-wide statistics
- Monitor all bookings across hostels
- Track all payments and revenue
- Access comprehensive admin dashboard
- Generate system reports

## 
### Pre-loaded Data
- **1 Hostel:** Elite Hostel (New York)
- **3 Rooms:** Single, Double, Triple occupancy
- **6 Beds:** Total across all rooms
- **8 Users:** 1 Admin, 1 Owner, 1 Manager, 5 Students

### Key Tables
- `users` - All user accounts with role-based access
- `hostels` - Hostel management
- `rooms` - Room inventory
- `beds` - Individual bed spaces
- `bookings` - Student bookings
- `payments` - Payment tracking

## 
The system uses Laravel middleware and policies to enforce:
- **Admin Middleware:** Restricts access to admin-only routes
- **Manager Middleware:** Restricts access to manager routes
- **Student Middleware:** Restricts access to student routes
- **Policies:** Fine-grained authorization for models

## 
| Route | Role | Purpose |
|-------|------|---------|
| `/dashboard` | All | Role-based dashboard |
| `/admin/hostels` | Admin | Manage hostels |
| `/manager/rooms` | Manager | Manage rooms |
| `/manager/bookings` | Manager | View/approve bookings |
| `/student/bookings/available` | Student | Browse available rooms |
| `/student/bookings` | Student | View own bookings |

## 
### User Model
```php
hasMany('bookings')
hasMany('beds')
belongsTo('hostel')
hasMany('ownedHostels', 'owner_id')
```

### Hostel Model
```php
belongsTo('owner', User)
hasMany('rooms')
hasMany('students')
hasMany('managers')
```

### Room Model
```php
belongsTo('hostel')
hasMany('beds')
hasMany('bookings')
```

### Booking Model
```php
belongsTo('user')
belongsTo('room')
belongsTo('bed')
hasMany('payments')
```

## 
### Adding New Rooms
As a Manager:
1. Navigate to `/manager/rooms`
2. Click "Create New Room"
3. Fill in room details (number, type, capacity, price)
4. Submit

### Approving Bookings
As a Manager:
1. Go to `/manager/bookings`
2. Click on pending booking
3. Click "Approve" or "Reject"
4. Student receives status notification

### Creating New Hostels
As an Admin:
1. Navigate to `/admin/hostels`
2. Click "Create New Hostel"
3. Fill in hostel details and assign owner
4. Assign managers to the hostel

## 
1. **Student Initiates:** Creates booking for a room
2. **System Creates:** Booking saved with "pending" status
3. **Manager Reviews:** Sees booking in dashboard
4. **Manager Approves/Rejects:**
 Bed marked as occupied
 Bed remains available
5. **Check-out:** System marks booking as "completed" on end date

## 
1. Booking created with total amount calculated
2. Payment record created with "pending" status
3. Manager/Student initiates payment
4. System updates payment status
5. Supports: pending, paid, failed, refunded statuses

## 
- **Laravel 12** - Backend framework
- **Livewire** - Real-time components
- **Filament** - Admin panel (optional integration)
- **SQLite** - Database (configurable)
- **Vite** - Frontend bundler

## 
1. **Customize Views:** Create views for your forms and dashboards
2. **Add Notifications:** Implement email/SMS for booking updates
3. **Payment Gateway:** Integrate with Stripe, PayPal, etc.
4. **Advanced Features:**
   - Room photos and gallery
   - Student ratings and reviews
   - Maintenance request system
   - Complaint/issue tracking
   - Custom pricing rules
   - Seasonal rates

echo Configuration## 

### Environment Variables
Check `.env` for database configuration:
```
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

### Application Settings
- Modify hostel pricing in database
- Configure booking rules in models
- Customize validation rules in controllers

## 
For issues or questions:
1. Check migration files in `database/migrations/`
2. Review model relationships in `app/Models/`
3. Check authorization in `app/Policies/`
4. Review middleware in `app/Http/Middleware/`

 Demo Data Ready## 

The system comes pre-populated with:
- 1 operational hostel with setup manager
- 3 different room types
- 6 ready-to-use bed spaces
- 5 test student accounts
- Full role demonstration data

Start exploring immediately after login!

---

**Created:** 2026-02-11
**Version:** 1.0
**Status:** Production Ready
