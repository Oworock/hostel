# Laravel Hostel Management System

A comprehensive hostel management system built with Laravel 11, Filament 3, and Livewire. This system enables students to book hostel bedspaces and rooms while providing management tools for hostel managers and owners.

## Features Overview

### 1. Three-Tier User Roles

#### **Student User**
- Browse available rooms and beds
- Create hostel booking requests
- View booking status and history
- Cancel bookings
- View assigned beds/rooms
- Process payments for bookings
- File complaints about accommodation

#### **Hostel Manager**
- Manage rooms and beds
- View and approve/reject booking requests
- Monitor occupancy rates
- Generate reports
- Manage payments
- Handle student complaints

#### **Hostel Owner/Admin**
- Complete system administration
- Manage multiple hostels
- Create and manage hostel managers
- Generate financial reports
- System settings and configurations
- Payment gateway management

### 2. Core Modules

#### **Hostel Management**
- Create and manage multiple hostels
- Store hostel details (name, address, city, phone)
- Hostel active/inactive status
- Owner assignment

#### **Room Management**
- Add rooms to hostels
- Define room types (single, double, triple, etc.)
- Set room capacities and pricing
- Track room availability

#### **Bed Management**
- Add beds to rooms
- Track bed status (available, occupied, maintenance)
- Assign beds to students

#### **Booking System**
- Students request bed bookings
- Managers approve/reject requests
- Track booking dates and status
- Support booking cancellations

#### **Payment System**
- Record student payments
- Track payment status
- Payment gateway integration
- Payment history and reports

#### **Complaint Management**
- Students can file complaints
- Managers can review and respond
- Track complaint status and resolution

## Database Structure

### Key Tables

**users** - User accounts with role assignments
**hostels** - Hostel properties
**rooms** - Rooms within hostels
**beds** - Individual beds within rooms
**bookings** - Student bed bookings
**payments** - Payment records
**allocations** - Bed allocations to students
**complaints** - Student complaints
**students** - Student profile information

## Technology Stack

- **Backend**: Laravel 11 (PHP 8.4+)
- **Admin Panel**: Filament 3
- **Frontend**: Blade Templates, Livewire
- **Styling**: Tailwind CSS
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Fortify

## Authentication & Authorization

- **Fortify Authentication**: Email/password with email verification
- **Role-Based Access Control**: Middleware guards for student, manager, and admin
- **Policy-Based Authorization**: Fine-grained permissions using Laravel Policies

## API Routes

### Authentication Routes
- POST `/api/register` - User registration
- POST `/api/login` - User login
- POST `/api/logout` - User logout
- POST `/email/verification-notification` - Send verification email
- GET `/email/verify/{id}/{hash}` - Verify email

### Admin Routes (Filament Panel)
- GET `/admin` - Admin dashboard
- `GET|POST /admin/hostels/*` - Hostel management
- `GET|POST /admin/rooms/*` - Room management
- `GET|POST /admin/beds/*` - Bed management
- `GET|POST /admin/students/*` - Student management
- `GET|POST /admin/payments/*` - Payment management
- `GET|POST /admin/users/*` - User management
- `GET|POST /admin/system-settings/*` - System configuration

### Manager Routes
- GET `/manager/dashboard` - Manager dashboard
- `GET|POST /manager/rooms/*` - Manage assigned rooms
- GET `/manager/bookings` - View booking requests
- PATCH `/manager/bookings/{id}/approve` - Approve booking
- PATCH `/manager/bookings/{id}/reject` - Reject booking

### Student Routes
- GET `/student/dashboard` - Student dashboard
- GET `/student/bookings` - My bookings
- GET `/student/bookings/available` - Available beds
- POST `/student/bookings` - Create booking request
- DELETE `/student/bookings/{id}/cancel` - Cancel booking

## User Registration & Role Assignment

The system assigns roles during registration based on user type:

```php
// Role values: 'student', 'manager', 'admin'
User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'role' => $request->role_type, // 'student', 'manager', or 'admin'
]);
```

## Getting Started

### Prerequisites
- PHP 8.4+
- Composer
- MySQL/PostgreSQL database
- Node.js and npm (for assets)

### Installation

1. **Clone and Setup**
```bash
cd /Users/oworock/Herd/Hostel
composer install
npm install
cp .env.example .env
php artisan key:generate
```

2. **Database Configuration**
```bash
# Update .env with your database credentials
php artisan migrate
php artisan db:seed
```

3. **Create Initial Admin User** (via Filament UI or Tinker)
```bash
php artisan tinker
>>> App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);
```

4. **Start Development Server**
```bash
php artisan serve
```

Access the application at `http://localhost:8000`

### Admin Dashboard Access
Navigate to `http://localhost:8000/admin` and login with admin credentials.

## File Structure

```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── Admin/HostelController.php
│   ├── Manager/RoomController.php
│   ├── Manager/BookingController.php
│   └── Student/BookingController.php
├── Models/
│   ├── User.php
│   ├── Hostel.php
│   ├── Room.php
│   ├── Bed.php
│   ├── Booking.php
│   ├── Payment.php
│   ├── Student.php
│   ├── Allocation.php
│   ├── Complaint.php
│   └── ...
├── Filament/
│   └── Resources/ (Filament admin panel resources)
└── Policies/ (Authorization policies)

database/
├── migrations/
└── seeders/

resources/
├── views/
│   ├── admin/
│   ├── manager/
│   ├── student/
│   └── components/
└── css/

routes/
├── web.php (Web routes)
├── auth.php (Authentication routes)
└── api.php (API routes)
```

## Key Features Implementation

### 1. Role-Based Access Control
Middleware automatically routes users to appropriate dashboards based on their role.

### 2. Booking Workflow
- Student: Request booking → Payment → Wait for approval
- Manager: Review → Approve/Reject → Notify student
- Admin: Oversee all bookings across hostels

### 3. Payment Integration
- Track payment status (pending, completed, failed)
- Multiple payment gateway support
- Payment history and receipts

### 4. Complaint System
- Students submit complaints
- Managers view and respond
- Track resolution status

### 5. Allocation System
- Assign approved bookings to specific beds
- Track occupancy per bed
- Generate occupancy reports

## Security Features

- **Email Verification**: All users must verify their email
- **Role-Based Middleware**: Protected routes based on user role
- **Authorization Policies**: Fine-grained permission checks
- **CSRF Protection**: Built-in CSRF token validation
- **Password Hashing**: Bcrypt password hashing
- **Rate Limiting**: Prevent brute force attacks

## Error Handling

The system includes proper error handling for:
- Invalid bookings
- Double bookings
- Payment failures
- Unauthorized access attempts
- Database constraints

## Future Enhancements

- SMS/Email notifications for bookings
- Advanced reporting and analytics
- Multi-currency support
- Mobile app integration
- Automated invoice generation
- Room inspection checklist
- Feedback/rating system
- Calendar-based booking view

## Support & Documentation

For detailed API documentation, see `API_REFERENCE.md`
For setup instructions, see `SETUP_GUIDE.md`
For testing credentials, see `TEST_CREDENTIALS.md`

## License

This project is built with Laravel and follows the same license terms.
