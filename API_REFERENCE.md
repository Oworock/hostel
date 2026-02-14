# Hostel Management System - API & Code Reference

## 
```
app/
 Http/
 Controllers/   
 Admin/      
 HostelController.php       # Hostel CRUD operations         
 Manager/      
 RoomController.php         # Room management         
 BookingController.php      # Booking approval/rejection         
 Student/      
 BookingController.php      # Booking creation & management         
 DashboardController.php        # Role-based dashboards      
 Middleware/   
 AdminMiddleware.php            # Admin access control      
 ManagerMiddleware.php          # Manager access control      
 StudentMiddleware.php          # Student access control      
 Requests/   
 Models/
 User.php                           # User with roles   
 Hostel.php                         # Hostel information   
 Room.php                           # Room details   
 Bed.php                            # Individual bed spaces   
 Booking.php                        # Student bookings   
 Payment.php                        # Payment tracking   
 Student.php                        # Legacy student model   
 Allocation.php                     # Bed allocations   
 Policies/
 BookingPolicy.php                  # Booking authorization   
 RoomPolicy.php                     # Room authorization   
 Providers/

database/
 migrations/
 0001_01_01_000000_create_users_table.php   
 2026_02_11_220000_create_hostels_table.php   
 2026_02_11_105513_create_rooms_table.php   
 2026_02_11_105551_create_beds_table.php   
 2026_02_11_225400_create_bookings_table.php   
 2026_02_11_105624_create_payments_table.php   
 2026_02_11_105604_create_students_table.php   
 2026_02_11_105615_create_allocations_table.php   
 2026_02_11_105633_create_complaints_table.php   
 seeders/
 HostelSeeder.php                   # Demo data seeder    

routes/
 web.php                                # Application routes
 auth.php                               # Authentication routes

resources/
 views/                                 # Blade templates (to be created)
```

## 
### Authentication Routes (Via Fortify)
```
GET   /login                    # Login page
POST  /login                    # Submit login
POST  /logout                   # Logout (requires auth)
GET   /register                 # Registration page
POST  /register                 # Submit registration
GET   /forgot-password          # Password reset request
POST  /forgot-password          # Submit password reset email
GET   /reset-password/{token}   # Password reset form
POST  /reset-password           # Update password
```

### Admin Routes (`/admin/hostels`)
```
GET    /admin/hostels              # List all hostels
GET    /admin/hostels/create       # Show create form
POST   /admin/hostels              # Store new hostel
GET    /admin/hostels/{hostel}     # Show hostel details
GET    /admin/hostels/{hostel}/edit # Show edit form
PUT    /admin/hostels/{hostel}     # Update hostel
DELETE /admin/hostels/{hostel}     # Delete hostel
```

### Manager Routes (`/manager/*`)
```
GET    /manager/rooms              # List rooms in hostel
GET    /manager/rooms/create       # Show create form
POST   /manager/rooms              # Store new room
GET    /manager/rooms/{room}       # Show room details
GET    /manager/rooms/{room}/edit  # Show edit form
PUT    /manager/rooms/{room}       # Update room
DELETE /manager/rooms/{room}       # Delete room

GET    /manager/bookings           # List hostel bookings
GET    /manager/bookings/{booking} # Show booking details
PATCH  /manager/bookings/{booking}/approve # Approve booking
PATCH  /manager/bookings/{booking}/reject  # Reject booking
DELETE /manager/bookings/{booking}/cancel  # Cancel booking
```

### Student Routes (`/student/*`)
```
GET    /student/bookings                    # List student's bookings
GET    /student/bookings/available          # Browse available rooms
GET    /student/rooms/{room}/book           # Show booking form
POST   /student/bookings                    # Create booking
GET    /student/bookings/{booking}          # Show booking details
DELETE /student/bookings/{booking}/cancel   # Cancel booking
```

### Shared Routes
```
GET  /                 # Welcome page
GET  /dashboard        # Role-based dashboard
```

## 
### User Model
```php
// Relationships
$user->hostel()           // Hostel assigned to manager/student
$user->ownedHostels()     // Hostels owned by admin
$user->bookings()         // Bookings created by student
$user->beds()             // Beds assigned to student

// Role checking
$user->isStudent()        // Returns boolean
$user->isManager()        // Returns boolean
$user->isAdmin()          // Returns boolean

// Attributes
$user->role               // 'student', 'manager', 'admin'
$user->name               // User's full name
$user->email              // Email address
$user->phone              // Phone number
$user->id_number          // Student ID or identification number
$user->address            // Physical address
$user->guardian_name      // Parent/guardian name
$user->guardian_phone     // Guardian phone
$user->hostel_id          // Assigned hostel ID
$user->is_active          // Account active status
```

### Hostel Model
```php
// Relationships
$hostel->owner()          // Hostel owner (User)
$hostel->rooms()          // All rooms in hostel
$hostel->students()       // Students in hostel
$hostel->managers()       // Managers assigned
$hostel->bookings()       // All bookings through rooms
$hostel->payments()       // All payments through bookings

// Attributes
$hostel->name             // Hostel name
$hostel->description      // Hostel details
$hostel->address          // Physical address
$hostel->city             // City name
$hostel->state            // State/province
$hostel->postal_code      // Postal code
$hostel->phone            // Contact phone
$hostel->email            // Contact email
$hostel->owner_id         // Owner user ID
$hostel->price_per_month  // Base monthly rate
$hostel->total_capacity   // Total bed count
$hostel->is_active        // Hostel status
```

### Room Model
```php
// Relationships
$room->hostel()           // Parent hostel
$room->beds()             // All beds in room
$room->bookings()         // All bookings for room
$room->availableBeds()    // Only unoccupied beds
$room->occupiedBeds()     // Only occupied beds

// Methods
$room->getOccupancyPercentage() // Returns occupancy %

// Attributes
$room->hostel_id          // Parent hostel ID
$room->room_number        // Room identifier
$room->type               // 'single', 'double', 'triple', 'quad'
$room->capacity           // Number of beds
$room->price_per_month    // Monthly price
$room->description        // Room description
$room->is_available       // Availability status
```

### Bed Model
```php
// Relationships
$bed->room()              // Parent room
$bed->user()              // Current occupant (if occupied)
$bed->booking()           // Associated booking

// Attributes
$bed->room_id             // Parent room ID
$bed->bed_number          // Bed identifier (e.g., "B1", "B2")
$bed->is_occupied         // Occupancy status
$bed->user_id             // Current occupant ID
$bed->occupied_from       // Occupancy start date
```

### Booking Model
```php
// Relationships
$booking->user()          // Student who booked
$booking->room()          // Booked room
$booking->bed()           // Assigned bed (nullable)
$booking->payments()      // Associated payments

// Status checking methods
$booking->isPending()     // status === 'pending'
$booking->isApproved()    // status === 'approved'
$booking->isRejected()    // status === 'rejected'
$booking->isCompleted()   // status === 'completed'
$booking->isCancelled()   // status === 'cancelled'

// Attributes
$booking->user_id         // Student ID
$booking->room_id         // Room ID
$booking->bed_id          // Bed ID (optional)
$booking->check_in_date   // Booking start date
$booking->check_out_date  // Booking end date
$booking->status          // Current status
$booking->total_amount    // Total cost
$booking->notes           // Additional notes
```

### Payment Model
```php
// Relationships
$payment->booking()       // Associated booking
$payment->user()          // Student who paid

// Status checking methods
$payment->isPaid()        // status === 'paid'
$payment->isPending()     // status === 'pending'
$payment->isFailed()      // status === 'failed'
$payment->isRefunded()    // status === 'refunded'

// Attributes
$payment->booking_id      // Associated booking ID
$payment->user_id         // Student ID
$payment->amount          // Payment amount
$payment->status          // Payment status
$payment->payment_method  // Payment method
$payment->transaction_id  // Transaction reference
$payment->payment_date    // Payment date
$payment->notes           // Notes
```

## 
### BookingPolicy
```php
// Only students can view their own bookings
// Managers can view/manage bookings in their hostel
// Admins have full access

public function view(User $user, Booking $booking): bool
// Can view booking if owner or manager of hostel or admin

public function update(User $user, Booking $booking): bool
// Can update booking if manager of hostel or admin
```

### RoomPolicy
```php
// Students can view all available rooms
// Managers can manage rooms in their hostel
// Admins have full access

public function view(User $user, Room $room): bool
// Can view room if admin or manager of hostel or student

public function update(User $user, Room $room): bool
// Can update room if manager of hostel or admin

public function delete(User $user, Room $room): bool
// Can delete room if manager of hostel or admin
```

## 
### Checking User Role
```php
if (auth()->user()->isAdmin()) {
    // Admin actions
}

if (auth()->user()->isManager()) {
    // Manager actions
}

if (auth()->user()->isStudent()) {
    // Student actions
}
```

### Creating a Booking
```php
$booking = Booking::create([
    'user_id' => auth()->id(),
    'room_id' => $room->id,
    'check_in_date' => $request->check_in_date,
    'check_out_date' => $request->check_out_date,
    'total_amount' => $room->price_per_month,
    'status' => 'pending'
]);
```

### Approving a Booking
```php
$booking = Booking::find($id);
$booking->update(['status' => 'approved']);

// Mark bed as occupied
if ($booking->bed_id) {
    $booking->bed->update([
        'is_occupied' => true,
        'user_id' => $booking->user_id,
        'occupied_from' => now()
    ]);
}
```

### Getting Manager's Bookings
```php
$bookings = Booking::whereHas('room', function ($query) {
    $query->where('hostel_id', auth()->user()->hostel_id);
})->get();
```

### Calculating Room Occupancy
```php
$occupancyRate = $room->getOccupancyPercentage();
// Returns: 66.67 (for 2 occupied out of 3 beds)
```

## 
### Room Validation
```php
[
    'room_number' => 'required|string',
    'type' => 'required|in:single,double,triple,quad',
    'capacity' => 'required|integer|min:1|max:10',
    'price_per_month' => 'required|numeric|min:0',
    'description' => 'nullable|string',
]
```

### Booking Validation
```php
[
    'room_id' => 'required|exists:rooms,id',
    'bed_id' => 'nullable|exists:beds,id',
    'check_in_date' => 'required|date|after:today',
    'check_out_date' => 'required|date|after:check_in_date',
]
```

### Hostel Validation
```php
[
    'name' => 'required|unique:hostels',
    'description' => 'nullable|string',
    'address' => 'required|string',
    'city' => 'required|string',
    'state' => 'nullable|string',
    'postal_code' => 'nullable|string',
    'phone' => 'nullable|string',
    'email' => 'nullable|email',
    'owner_id' => 'required|exists:users,id',
    'price_per_month' => 'required|numeric|min:0',
    'total_capacity' => 'required|integer|min:1',
]
```

## 
### Add Custom Validation
```php
// In controller
$validated = $request->validate([
    'custom_field' => 'required|custom_rule',
]);
```

### Add Model Scopes
```php
// In Model
public function scopePending($query)
{
    return $query->where('status', 'pending');
}

// Usage
Booking::pending()->get();
```

### Add Events
```php
// Listen to model events
class BookingObserver
{
    public function created(Booking $booking)
    {
        // Send notification
        Mail::send(new BookingCreated($booking));
    }
}
```

## 
### Get Manager's Dashboard Stats
```php
$hostel = auth()->user()->hostel;
$stats = [
    'total_rooms' => $hostel->rooms()->count(),
    'total_students' => $hostel->students()->count(),
    'pending_bookings' => Booking::whereHas('room', fn($q) => 
        $q->where('hostel_id', $hostel->id)
    )->where('status', 'pending')->count(),
];
```

### Get Admin's Revenue
```php
$revenue = Payment::where('status', 'paid')
    ->sum('amount');
```

### Get Available Rooms
```php
$available = Room::where('is_available', true)
    ->with('beds', 'hostel')
    ->paginate(12);
```

---

**Last Updated:** 2026-02-11
**API Version:** 1.0
