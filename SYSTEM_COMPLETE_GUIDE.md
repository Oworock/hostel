# Complete Hostel Management System Documentation

## 📋 System Overview

This is a comprehensive Laravel-based hostel management system designed for educational institutions. It enables students to book hostel beds and rooms while providing management tools for hostel managers and administrative controls for hostel owners.

### Key Capabilities

✅ **Multi-role User System** - Student, Manager, Admin roles with distinct permissions
✅ **Hostel Management** - Create and manage multiple hostel properties
✅ **Room & Bed Management** - Organize accommodation units by room and individual beds
✅ **Booking System** - Request, approve, and manage student bookings
✅ **Payment Tracking** - Record and monitor all payments
✅ **Complaint System** - Students can file complaints, managers can respond
✅ **Admin Dashboard** - Filament-based administrative interface
✅ **Email Verification** - Secure registration with email confirmation
✅ **Role-Based Access Control** - Fine-grained permission management

---

## 🏗️ System Architecture

### Three-Tier User Structure

```
┌─────────────────────────────────────┐
│   HOSTEL OWNER / ADMIN              │
│   - Manage hostels                  │
│   - Manage users & roles            │
│   - System configuration            │
│   - Financial reports               │
│   Access: /admin                    │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│   HOSTEL MANAGER                    │
│   - Manage rooms & beds             │
│   - Approve/reject bookings         │
│   - Handle complaints               │
│   - View occupancy                  │
│   Access: /manager                  │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│   STUDENT                           │
│   - Browse available rooms          │
│   - Submit booking requests         │
│   - Process payments                │
│   - File complaints                 │
│   Access: /student                  │
└─────────────────────────────────────┘
```

### Database Schema

```
Users
├── id (primary key)
├── name
├── email (unique)
├── password
├── role (student|manager|admin)
├── email_verified_at
└── timestamps

Hostels
├── id (primary key)
├── name
├── description
├── address
├── city
├── phone
├── owner_id (foreign key → users)
├── is_active
└── timestamps

Rooms
├── id (primary key)
├── hostel_id (foreign key)
├── room_number
├── room_type (single|double|triple|quad)
├── capacity
├── price_per_bed
├── status (available|full|maintenance)
└── timestamps

Beds
├── id (primary key)
├── room_id (foreign key)
├── bed_number
├── status (available|occupied|maintenance)
└── timestamps

Bookings
├── id (primary key)
├── user_id (foreign key → users)
├── room_id (foreign key)
├── bed_id (foreign key, nullable)
├── check_in_date
├── check_out_date
├── status (pending|approved|rejected|cancelled)
├── total_amount
├── notes
└── timestamps

Payments
├── id (primary key)
├── booking_id (foreign key)
├── user_id (foreign key)
├── amount
├── payment_method
├── payment_date
├── status (pending|completed|failed)
└── timestamps

Allocations
├── id (primary key)
├── booking_id (foreign key)
├── bed_id (foreign key)
├── allocation_date
└── timestamps

Complaints
├── id (primary key)
├── user_id (foreign key)
├── booking_id (foreign key, nullable)
├── subject
├── description
├── status (open|in_progress|resolved|closed)
├── response
└── timestamps
```

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.4 or higher
- Composer
- MySQL 8.0+ or PostgreSQL 12+
- Node.js 18+ and npm

### Installation Steps

1. **Clone/Navigate to Project**
```bash
cd /Users/oworock/Herd/Hostel
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Update Database Configuration** (in `.env`)
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hostel_management
DB_USERNAME=root
DB_PASSWORD=
```

5. **Run Migrations**
```bash
php artisan migrate
php artisan db:seed
```

6. **Build Assets**
```bash
npm run dev
```

7. **Start Development Server**
```bash
php artisan serve
```

The application is now available at: **http://localhost:8000**

---

## 🔐 Authentication & Authorization

### User Registration

Students, managers, and admins can register through the web interface. Each registration requires:
- Full Name
- Email Address
- Password
- Email Verification

### Pre-configured Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@hostel.com | password |
| Manager | manager@hostel.com | password |
| Student | student@hostel.com | password |

### Role-Based Access Control (RBAC)

The system uses middleware to control access:

```php
// In routes/web.php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Admin-only routes
});

Route::middleware(['auth', 'manager'])->prefix('manager')->group(function () {
    // Manager-only routes
});

Route::middleware(['auth', 'student'])->prefix('student')->group(function () {
    // Student-only routes
});
```

---

## 📱 Workflow Examples

### 1. Complete Booking Workflow

```
Student initiates booking
        ↓
Manager reviews request
        ↓
Manager approves/rejects
        ↓ (if approved)
Student receives notification
        ↓
Student processes payment
        ↓
Booking activated
        ↓
Allocation assigned to bed
```

### 2. Admin Hostel Setup

```
Admin creates hostel
        ↓
Admin assigns manager
        ↓
Manager adds rooms
        ↓
Manager adds beds to rooms
        ↓
System ready for bookings
```

### 3. Complaint Resolution

```
Student files complaint
        ↓
Manager is notified
        ↓
Manager investigates
        ↓
Manager provides response
        ↓
Complaint status updated
        ↓
Student views resolution
```

---

## 🎨 User Interfaces

### Admin Panel (Filament)
- **URL**: `/admin`
- **Dashboard**: System statistics and quick actions
- **Hostels**: CRUD operations for hostel properties
- **Users**: User management and role assignment
- **Rooms**: Room configuration and management
- **Beds**: Bed management and assignment
- **Bookings**: Booking overview and management
- **Payments**: Payment records and tracking
- **System Settings**: Configuration and customization

### Manager Dashboard
- **URL**: `/manager`
- **Rooms**: Manage hostel rooms
- **Bookings**: Review pending requests, approve/reject
- **Payments**: Track payments for bookings
- **Occupancy**: View bed status and occupancy rates
- **Complaints**: Review and respond to student complaints

### Student Dashboard
- **URL**: `/student`
- **Browse**: Search available rooms and beds
- **Bookings**: View booking history and status
- **Payments**: Process payments for approved bookings
- **Complaints**: File and track complaints
- **Profile**: View personal information

---

## 🔌 API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/register` - User registration
- `POST /api/logout` - User logout
- `POST /api/forgot-password` - Password reset request
- `POST /api/reset-password` - Reset password with token

### Hostels
- `GET /api/hostels` - List all hostels
- `GET /api/hostels/{id}` - Get hostel details
- `POST /api/hostels` - Create hostel (admin only)
- `PUT /api/hostels/{id}` - Update hostel (admin only)
- `DELETE /api/hostels/{id}` - Delete hostel (admin only)

### Rooms
- `GET /api/rooms` - List rooms
- `GET /api/rooms/{id}` - Get room details
- `POST /api/rooms` - Create room (manager only)
- `PUT /api/rooms/{id}` - Update room (manager only)
- `DELETE /api/rooms/{id}` - Delete room (manager only)

### Bookings
- `GET /api/bookings` - List user's bookings
- `GET /api/bookings/{id}` - Get booking details
- `POST /api/bookings` - Create booking (student)
- `PATCH /api/bookings/{id}/approve` - Approve booking (manager)
- `PATCH /api/bookings/{id}/reject` - Reject booking (manager)
- `DELETE /api/bookings/{id}` - Cancel booking

### Payments
- `GET /api/payments` - List payments
- `GET /api/payments/{id}` - Get payment details
- `POST /api/payments` - Create payment
- `PATCH /api/payments/{id}/confirm` - Confirm payment

---

## 📊 Key Models & Relationships

### User Model
```php
public function bookings() {
    return $this->hasMany(Booking::class);
}

public function payments() {
    return $this->hasMany(Payment::class);
}

public function complaints() {
    return $this->hasMany(Complaint::class);
}

public function managedHostels() {
    return $this->hasMany(Hostel::class, 'manager_id');
}
```

### Hostel Model
```php
public function owner() {
    return $this->belongsTo(User::class, 'owner_id');
}

public function rooms() {
    return $this->hasMany(Room::class);
}
```

### Room Model
```php
public function hostel() {
    return $this->belongsTo(Hostel::class);
}

public function beds() {
    return $this->hasMany(Bed::class);
}

public function bookings() {
    return $this->hasMany(Booking::class);
}
```

### Booking Model
```php
public function user() {
    return $this->belongsTo(User::class);
}

public function room() {
    return $this->belongsTo(Room::class);
}

public function bed() {
    return $this->belongsTo(Bed::class);
}

public function payments() {
    return $this->hasMany(Payment::class);
}

public function allocation() {
    return $this->hasOne(Allocation::class);
}
```

---

## 🛡️ Security Features

1. **Email Verification**: All users must verify email before accessing features
2. **Password Hashing**: Bcrypt algorithm for password storage
3. **CSRF Protection**: Automatic CSRF token validation
4. **Rate Limiting**: Prevents brute force attacks
5. **Authorization Policies**: Fine-grained permission checks
6. **Input Validation**: All user inputs validated before processing
7. **SQL Injection Prevention**: Using Eloquent ORM prepared statements
8. **XSS Protection**: Blade template escaping

---

## 📚 File Structure

```
hostel-system/
├── app/
│   ├── Models/                      # Database models
│   ├── Http/
│   │   ├── Controllers/             # Route controllers
│   │   └── Middleware/              # Custom middleware
│   ├── Filament/
│   │   └── Resources/               # Filament admin resources
│   └── Policies/                    # Authorization policies
│
├── database/
│   ├── migrations/                  # Database migrations
│   ├── seeders/                     # Database seeders
│   └── factories/                   # Model factories
│
├── resources/
│   ├── views/                       # Blade templates
│   │   ├── admin/
│   │   ├── manager/
│   │   ├── student/
│   │   └── components/
│   └── css/                         # Stylesheets
│
├── routes/
│   ├── web.php                      # Web routes
│   ├── auth.php                     # Auth routes
│   └── api.php                      # API routes
│
├── config/                          # Configuration files
├── public/                          # Public assets
├── storage/                         # Application storage
└── tests/                           # Test cases
```

---

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Run Specific Test
```bash
php artisan test tests/Unit/BookingTest.php
```

### Run with Coverage
```bash
php artisan test --coverage
```

---

## 🐛 Troubleshooting

### Common Issues

**Issue**: Routes not found  
**Solution**: Clear cache and regenerate
```bash
php artisan route:clear
php artisan route:cache
```

**Issue**: Blade view compilation errors  
**Solution**: Clear view cache
```bash
php artisan view:clear
```

**Issue**: Database connection errors  
**Solution**: Check `.env` database configuration and run migrations
```bash
php artisan migrate
```

**Issue**: Filament admin panel not accessible  
**Solution**: Ensure user has admin role and email is verified
```bash
php artisan tinker
>>> App\Models\User::find(1)->update(['role' => 'admin']);
>>> exit
```

---

## 📖 Documentation Files

- **HOSTEL_SYSTEM_OVERVIEW.md** - System features and architecture
- **HOSTEL_QUICK_START.md** - Quick start guide with workflows
- **TEST_ACCOUNTS.md** - Test credentials and scenarios
- **API_REFERENCE.md** - Complete API documentation
- **SETUP_GUIDE.md** - Detailed setup instructions
- **TESTING_GUIDE.md** - Testing procedures

---

## 🚀 Deployment

### Production Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Generate app key: `php artisan key:generate`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Optimize autoloader: `composer install --optimize-autoloader --no-dev`
- [ ] Build assets: `npm run build`
- [ ] Set proper file permissions
- [ ] Configure email driver for notifications
- [ ] Set up payment gateways if using them
- [ ] Configure backup strategy

---

## 📞 Support

For issues, features, or documentation requests, please refer to:
- System documentation files in project root
- Code comments and docstrings
- Laravel Framework Documentation: https://laravel.com
- Filament Documentation: https://filamentphp.com

---

## 📄 License

This project is built with Laravel and follows standard Laravel licensing terms.

---

**System Version**: 1.0  
**Last Updated**: 2024  
**Status**: ✅ Production Ready
