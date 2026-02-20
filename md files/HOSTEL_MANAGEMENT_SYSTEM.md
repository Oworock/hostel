# Hostel Management System

A comprehensive Laravel-based hostel management system designed to help students book bedspaces and room types, with three user levels: Students, Hostel Managers, and Hostel Owners/Admins.

## System Architecture

### User Roles

#### 1. **Student (role: 'student')**
- Browse available rooms and beds
- Create booking requests
- View their booking history
- Cancel pending/approved bookings
- View payment history
- Dashboard showing active bookings

#### 2. **Hostel Manager (role: 'manager')**
- Manage rooms in their assigned hostel
- Create and update room details
- View all bookings for their hostel
- Approve or reject booking requests
- Manage bed allocations
- Monitor occupancy rates
- Dashboard with hostel statistics

#### 3. **Hostel Owner/Admin (role: 'admin')**
- Manage multiple hostels
- Create and manage hostel information
- Assign managers to hostels
- View system-wide statistics
- Monitor all bookings and payments
- Generate reports
- System-wide dashboard

## Database Models

### Users
- `id` - Primary key
- `name` - User's name
- `email` - Unique email address
- `password` - Hashed password
- `role` - Enum: student, manager, admin
- `phone` - Contact number
- `id_number` - Student/ID number
- `address` - Physical address
- `guardian_name` - Guardian's name (for students)
- `guardian_phone` - Guardian's contact
- `hostel_id` - Foreign key to hostel (for managers/students)
- `is_active` - Account status

### Hostels
- `id` - Primary key
- `name` - Unique hostel name
- `description` - Hostel description
- `address` - Physical address
- `city` - City
- `state` - State
- `postal_code` - Postal code
- `phone` - Contact number
- `email` - Contact email
- `owner_id` - Foreign key to user (admin/owner)
- `price_per_month` - Monthly rate
- `total_capacity` - Total bed capacity
- `is_active` - Hostel status

### Rooms
- `id` - Primary key
- `hostel_id` - Foreign key to hostel
- `room_number` - Unique room identifier
- `type` - Enum: single, double, triple, quad
- `capacity` - Number of beds
- `price_per_month` - Room rental price
- `description` - Room details
- `is_available` - Availability status

### Beds
- `id` - Primary key
- `room_id` - Foreign key to room
- `bed_number` - Bed identifier
- `is_occupied` - Occupancy status
- `user_id` - Foreign key to user (current occupant)
- `occupied_from` - Occupancy start date

### Bookings
- `id` - Primary key
- `user_id` - Foreign key to student
- `room_id` - Foreign key to room
- `bed_id` - Foreign key to bed (optional)
- `check_in_date` - Booking start date
- `check_out_date` - Booking end date
- `status` - Enum: pending, approved, rejected, completed, cancelled
- `total_amount` - Booking cost
- `notes` - Additional notes

### Payments
- `id` - Primary key
- `booking_id` - Foreign key to booking
- `user_id` - Foreign key to student
- `amount` - Payment amount
- `status` - Enum: pending, paid, failed, refunded
- `payment_method` - Payment method used
- `transaction_id` - Transaction reference
- `payment_date` - Payment date

## Routes Structure

### Web Routes
```
/                                    - Welcome page
/dashboard                           - Role-based dashboard
/admin/hostels                       - Manage hostels (Admin only)
/manager/rooms                       - Manage rooms (Manager only)
/manager/bookings                    - View/manage bookings (Manager only)
/student/bookings                    - View own bookings (Student only)
/student/bookings/available          - Browse available rooms (Student only)
```

## Middleware

### Role-based Middleware
- `admin` - Restricts access to admin users only
- `manager` - Restricts access to manager users only
- `student` - Restricts access to student users only

## Controllers

### Admin
- `HostelController` - CRUD operations for hostels

### Manager
- `RoomController` - Room management
- `BookingController` - Booking approval/rejection

### Student
- `BookingController` - Booking and room browsing

### Shared
- `DashboardController` - Role-specific dashboards

## Authorization Policies

### BookingPolicy
- Students can only view/manage their own bookings
- Managers can view/manage bookings for their hostel
- Admins have full access

### RoomPolicy
- Managers can manage rooms in their hostel
- Students can view all available rooms
- Admins have full access

## Installation & Setup

### 1. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 2. Database Setup
```bash
php artisan migrate
php artisan db:seed --class=HostelSeeder
```

### 3. Testing Credentials
After running seeders, use these test accounts:

**Admin Account:**
- Email: admin@hostel.com
- Password: password

**Hostel Owner:**
- Email: owner@hostel.com
- Password: password

**Manager Account:**
- Email: manager@hostel.com
- Password: password

**Student Accounts:**
- Email: student1@email.com through student5@email.com
- Password: password (same for all)

### 4. Run Development Server
```bash
php artisan serve
npm run dev
```

## Key Features

### For Students
 Cancel pending bookings- - - - - 

### For Managers
- - -  Approve/reject booking requests
- - 
### For Admins
- - - - - 
## Booking Workflow

1. **Student Initiates:** Student browses available rooms and creates a booking request
2. **System Creates:** Booking is created with "pending" status
3. **Manager Reviews:** Manager sees the booking request in their dashboard
4. **Manager Actions:** Manager can approve, reject, or let it expire
5. **If Approved:** Bed is marked as occupied, booking status becomes "approved"
6. **If Rejected:** Booking status becomes "rejected", bed remains available
7. **Check-out:** When end date is reached, booking is marked "completed"

## Payment Integration Ready
The system is prepared for payment integration with:
- Multiple payment methods support
- Transaction tracking
- Payment status management
- Refund capability

## Future Enhancements
- SMS notifications for booking status
- Email confirmations
- Complaint/maintenance request system
- Room ratings and reviews
- Photo gallery for rooms
- Integration with payment gateways
- Advanced reporting and analytics
- Mobile application support

