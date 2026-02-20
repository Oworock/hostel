# Test Credentials for Hostel Management System

## ✅ Working Login Credentials

The database has been reset and re-seeded with properly hashed passwords. Use the following credentials to log in:

### 1. Student Account
- **Email:** `student@hostel.com`
- **Password:** `student123`
- **Role:** Student
- **Name:** Student User
- **Phone:** +2348090000000

### 2. Hostel Manager Account
- **Email:** `manager@hostel.com`
- **Password:** `manager123`
- **Role:** Manager
- **Name:** Manager User
- **Phone:** +2348087654321

### 3. Admin/Owner Account
- **Email:** `admin@hostel.com`
- **Password:** `admin123`
- **Role:** Admin
- **Name:** Admin User
- **Phone:** +2348012345678

## Account Features by Role

### Student
- View available hostels and rooms
- Browse bed spaces
- Make bookings
- Manage own profile
- View booking history
- Make payments

### Manager
- Manage rooms and bed spaces in their hostel
- View student bookings
- Manage allocations
- View payment records
- Manage students in their hostel

### Admin
- Full system access
- Manage all hostels
- Manage all users (students and managers)
- System settings configuration
- Payment gateway configuration
- Marketing campaigns
- SMS campaign management
- User management

## Database Information

- **Database Type:** SQLite
- **Location:** `database/database.sqlite`
- **All passwords are hashed using bcrypt (Laravel's default)**

## Login URL

- **Application URL:** `http://localhost:8000` (or configured port)
- **Login Page:** `http://localhost:8000/login`

## Reset Database

If you need to reset the database with fresh seed data, run:

```bash
php artisan migrate:fresh --seed
```

This will:
1. Drop all tables
2. Re-create all tables from migrations
3. Seed the database with the three test users above
