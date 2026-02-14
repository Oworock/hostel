# Quick Reference Card - Hostel Management System

## 
```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start frontend build
npm run dev

# Open browser
http://localhost:8000
```

## 
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@hostel.com | password |
| Manager | manager@hostel.com | password |
| Student 1 | student1@email.com | password |
| Student 2 | student2@email.com | password |

## 
### Navigation
- `/` - Welcome
- `/dashboard` - Role-based dashboard
- `/login` - Login page
- `/register` - Registration page

### Admin (`/admin/hostels`)
- `GET /admin/hostels` - List hostels
- `GET /admin/hostels/create` - Create form
- `POST /admin/hostels` - Store hostel
- `GET /admin/hostels/{id}` - Show hostel
- `GET /admin/hostels/{id}/edit` - Edit form
- `PUT /admin/hostels/{id}` - Update hostel
- `DELETE /admin/hostels/{id}` - Delete hostel

### Manager (`/manager/rooms`, `/manager/bookings`)
- `GET /manager/rooms` - List rooms
- `POST /manager/rooms` - Create room
- `PUT /manager/rooms/{id}` - Update room
- `DELETE /manager/rooms/{id}` - Delete room
- `GET /manager/bookings` - List bookings
- `PATCH /manager/bookings/{id}/approve` - Approve
- `PATCH /manager/bookings/{id}/reject` - Reject

### Student (`/student/bookings`)
- `GET /student/bookings` - My bookings
- `GET /student/bookings/available` - Browse rooms
- `POST /student/bookings` - Create booking
- `DELETE /student/bookings/{id}/cancel` - Cancel

## 
```php
// User model
User::where('role', 'student')->get();
User::where('role', 'manager')->get();
$user->isStudent();
$user->isManager();
$user->isAdmin();

// Hostel model
Hostel::with('rooms', 'students')->get();
$hostel->rooms()->count();
$hostel->students()->count();

// Room model
Room::where('is_available', true)->get();
$room->availableBeds()->count();
$room->getOccupancyPercentage();

// Booking model
Booking::where('status', 'pending')->get();
$booking->isPending();
$booking->isApproved();
$booking->isRejected();

// Payment model
Payment::where('status', 'paid')->sum('amount');
$payment->isPaid();
```

## 
```bash
# Run migrations
php artisan migrate

# Refresh database
php artisan migrate:refresh

# Seed data
php artisan db:seed --class=HostelSeeder

# Fresh setup
php artisan migrate:refresh --seed

# Access database
sqlite3 database/database.sqlite
```

## 
| Purpose | Path |
|---------|------|
| Models | `app/Models/` |
| Controllers | `app/Http/Controllers/` |
| Middleware | `app/Http/Middleware/` |
| Policies | `app/Policies/` |
| Migrations | `database/migrations/` |
| Seeders | `database/seeders/` |
| Routes | `routes/web.php` |
| Config | `config/` |
| Views | `resources/views/` |

## 
```php
// Check role
if (auth()->user()->isAdmin()) { }
if (auth()->user()->isManager()) { }
if (auth()->user()->isStudent()) { }

// Authorize action
$this->authorize('view', $booking);
$this->authorize('update', $room);

// Policy methods
// BookingPolicy::view() - Who can view
// BookingPolicy::update() - Who can update
// RoomPolicy::view() - Who can view
// RoomPolicy::update() - Who can update
```

## 
```php
// In routes
Route::middleware('admin')->group(function () {
    // Admin routes
});

Route::middleware('manager')->group(function () {
    // Manager routes
});

Route::middleware('student')->group(function () {
    // Student routes
});
```

## 
### Create a Room (Manager)
```php
Room::create([
    'hostel_id' => auth()->user()->hostel_id,
    'room_number' => 'R101',
    'type' => 'double',
    'capacity' => 2,
    'price_per_month' => 500,
]);
```

### Create a Booking (Student)
```php
Booking::create([
    'user_id' => auth()->id(),
    'room_id' => 1,
    'check_in_date' => '2026-03-01',
    'check_out_date' => '2026-06-01',
    'status' => 'pending',
    'total_amount' => 1500,
]);
```

### Approve Booking (Manager)
```php
$booking->update(['status' => 'approved']);
$booking->bed->update([
    'is_occupied' => true,
    'user_id' => $booking->user_id,
    'occupied_from' => now(),
]);
```

## 
```php
// Student's active bookings
auth()->user()->bookings()
    ->whereIn('status', ['pending', 'approved'])
    ->get();

// Manager's hostel bookings
Booking::whereHas('room', function ($q) {
    $q->where('hostel_id', auth()->user()->hostel_id);
})->get();

// Room occupancy rate
$occupiedBeds = $room->occupiedBeds()->count();
$totalBeds = $room->beds()->count();
$rate = ($occupiedBeds / $totalBeds) * 100;

// Total revenue
Payment::where('status', 'paid')
    ->sum('amount');

// Pending bookings
Booking::where('status', 'pending')
    ->with('user', 'room')
    ->get();
```

## 
```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=BookingTest

# Run with coverage
php artisan test --coverage
```

## 
```bash
# Server
php artisan serve

# Database
php artisan migrate
php artisan migrate:refresh
php artisan db:seed --class=HostelSeeder

# Make commands
php artisan make:model ModelName
php artisan make:controller ControllerName
php artisan make:migration migration_name
php artisan make:seeder SeederName

# Tinker shell
php artisan tinker

# Cache
php artisan cache:clear
php artisan view:cache

# Optimize
php artisan optimize
php artisan optimize:clear
```

## 
```
resources/views/
 layouts/
 app.blade.php   
 admin.blade.php   
 manager.blade.php   
 student.blade.php   
 admin/
 dashboard.blade.php   
 hostels/   
 index.blade.php       
 create.blade.php       
 edit.blade.php       
 show.blade.php       
 manager/
 dashboard.blade.php   
 rooms/   
 index.blade.php      
 create.blade.php      
 edit.blade.php      
 show.blade.php      
 bookings/   
 index.blade.php       
 show.blade.php       
 student/
 dashboard.blade.php   
 bookings/   
 index.blade.php       
 available.blade.php       
 create.blade.php       
 show.blade.php       
 auth/
 login.blade.php    
 register.blade.php    
 password-reset.blade.php    
```

## 
### Student Booking Flow
1. Student logs in
2. Navigate to `/student/bookings/available`
3. Browse rooms
 `/student/rooms/{id}/book`
5. Fill booking form
6. POST to `/student/bookings`
7. View at `/student/bookings/{id}`

### Manager Approval Flow
1. Manager logs in
2. Navigate to `/manager/bookings`
3. Click pending booking
4. View `/manager/bookings/{id}`
5. Click approve/reject
6. System updates status

### Admin Hostel Management
1. Admin logs in
2. Navigate to `/admin/hostels`
3. Create/Edit/Delete hostels
4. Assign managers

## 
| Feature | Student | Manager | Admin |
|---------|---------|---------|-------|
| Browse Rooms | |  |  | 
|  |Book  | Room |  | 
| Manage | |   | Rooms | 
| Approve | |   | Bookings | 
| View Hostel | |   | Stats | 
|  | Create |  | Hostel | 
| View | |   | Revenue | 
|  | System |  | Admin | 

## 
- `HOSTEL_MANAGEMENT_SYSTEM.md` - Complete system guide
- `SETUP_GUIDE.md` - Installation & configuration
- `API_REFERENCE.md` - Code reference & examples
- `SYSTEM_SUMMARY.txt` - Technical overview
- `IMPLEMENTATION_CHECKLIST.md` - What's done & what's next
- `QUICK_REFERENCE.md` - This file

---

**Print this page for quick reference while developing!**

Last Updated: 2026-02-11
