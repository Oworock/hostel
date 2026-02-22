# 📚 Hostel Management System - Complete Documentation

## Quick Navigation

- 🚀 **Getting Started**: See [COMPLETE_SETUP_GUIDE.md](COMPLETE_SETUP_GUIDE.md)
- 🧪 **Testing**: See [TESTING_CHECKLIST.md](TESTING_CHECKLIST.md)
- 👤 **Login Credentials**: See [TEST_CREDENTIALS.md](TEST_CREDENTIALS.md)
- 📖 **System Overview**: See [SYSTEM_README.md](SYSTEM_README.md)
- 🔗 **API Reference**: See [API_REFERENCE.md](API_REFERENCE.md)

---

## System at a Glance

### Architecture
```
┌─────────────────────────────────────────┐
│     Laravel Hostel Management System    │
├─────────────────────────────────────────┤
│                                         │
│  Frontend Layer (Blade + Tailwind)      │
│  ├─ Student Dashboard                  │
│  ├─ Manager Dashboard                  │
│  └─ Admin Panel (Filament)              │
│                                         │
│  Business Logic Layer                   │
│  ├─ Controllers                         │
│  ├─ Services                            │
│  └─ Middleware                          │
│                                         │
│  Data Layer (Eloquent ORM)              │
│  ├─ Models                              │
│  ├─ Migrations                          │
│  └─ Factories/Seeders                   │
│                                         │
│  Database (SQLite/MySQL)                │
│  ├─ Users, Hostels, Rooms, Beds         │
│  ├─ Bookings, Payments                  │
│  ├─ SMS/Marketing Campaigns             │
│  └─ System Settings                     │
│                                         │
│  External Integrations                  │
│  ├─ Paystack (Payments)                 │
│  ├─ Flutterwave (Payments)              │
│  └─ SMS Providers (Marketing)           │
│                                         │
└─────────────────────────────────────────┘
```

### Technology Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| Framework | Laravel | 12.51.0 |
| Admin Panel | Filament | 3.x |
| Database | SQLite/MySQL | - |
| Frontend | Blade + Tailwind | - |
| Asset Building | Vite | Latest |
| Language | PHP | 8.1+ |
| Node | Node.js | 14+ |

---

## Core Features

### 1. User Management (3 Roles)

#### Student Role
- Browse hostels and rooms
- Make bookings
- Process payments
- Track booking status
- Manage profile
- View booking history

#### Manager Role
- Manage assigned hostel
- Create and manage rooms
- Manage bed allocations
- View student bookings
- Approve/reject bookings
- Generate reports
- Track occupancy

#### Admin Role
- Manage all hostels
- Manage all users
- System configuration
- Payment gateway setup
- SMS provider management
- Marketing campaigns
- Financial reporting
- User impersonation

### 2. Hostel & Room Management

- Create/edit/delete hostels
- Organize rooms by hostel
- Define room types and capacity
- Set pricing per room
- Track bed assignments
- Monitor occupancy rates

### 3. Booking System

- Students browse available spaces
- Create booking requests
- Managers approve/reject
- Automatic status updates
- Booking cancellation
- Booking history

### 4. Payment Processing

- **Paystack Integration**
  - Real-time payment processing
  - Secure transaction handling
  - Payment verification
  - Revenue tracking

- **Flutterwave Integration**
  - Multiple payment methods
  - Transaction logging
  - Webhook handling
  - Revenue reports

### 5. SMS Marketing

- **Multiple Providers**
  - Termii (Nigeria)
  - Afrimotion (Africa)
  - AWS SNS (Global)
  - Custom HTTP providers

- **Campaign Management**
  - Create SMS campaigns
  - Schedule messages
  - Target recipients
  - Track delivery
  - View analytics

### 6. System Administration

- **Settings Management**
  - Application name
  - Logo upload
  - Color customization
  - Footer configuration
  - System limits

- **User Management**
  - User creation/editing
  - Role assignment
  - Account activation/deactivation
  - User impersonation

- **Payment Configuration**
  - Gateway setup
  - API key management
  - Webhook configuration
  - Transaction monitoring

---

## File Structure

```
Hostel/
├── app/
│   ├── Models/                          # Database models
│   │   ├── User.php                     # User model with roles
│   │   ├── Hostel.php                   # Hostel details
│   │   ├── Room.php                     # Room information
│   │   ├── Bed.php                      # Bed allocation
│   │   ├── Booking.php                  # Booking records
│   │   ├── Payment.php                  # Payment transactions
│   │   ├── Student.php                  # Student profiles
│   │   ├── SystemSetting.php            # System configuration
│   │   ├── PaymentGateway.php           # Payment gateways
│   │   ├── SmsProvider.php              # SMS providers
│   │   ├── SmsCampaign.php              # SMS campaigns
│   │   ├── MarketingCampaign.php        # Marketing campaigns
│   │   ├── Allocation.php               # Bed allocations
│   │   ├── Complaint.php                # Student complaints
│   │   └── UserManagement.php           # User management
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php  # Main dashboard
│   │   │   ├── Manager/                 # Manager routes
│   │   │   │   ├── RoomController.php
│   │   │   │   └── BookingController.php
│   │   │   ├── Student/                 # Student routes
│   │   │   │   └── BookingController.php
│   │   │   └── Payment/                 # Payment routes
│   │   │       ├── PaystackController.php
│   │   │       └── FlutterwaveController.php
│   │   │
│   │   └── Middleware/
│   │       ├── StudentMiddleware.php
│   │       ├── ManagerMiddleware.php
│   │       └── AdminMiddleware.php
│   │
│   ├── Filament/
│   │   ├── Resources/                   # CRUD resources
│   │   │   ├── HostelResource.php
│   │   │   ├── RoomResource.php
│   │   │   ├── BedResource.php
│   │   │   ├── StudentResource.php
│   │   │   ├── UserResource.php
│   │   │   ├── BookingResource.php
│   │   │   ├── PaymentResource.php
│   │   │   ├── PaymentGatewayResource.php
│   │   │   ├── SmsProviderResource.php
│   │   │   ├── SmsCampaignResource.php
│   │   │   ├── MarketingCampaignResource.php
│   │   │   ├── AllocationResource.php
│   │   │   ├── ComplaintResource.php
│   │   │   └── SystemSettingResource.php
│   │   │
│   │   ├── Pages/
│   │   │   ├── Dashboard.php            # Main dashboard
│   │   │   └── SystemCustomization.php  # Settings page
│   │   │
│   │   └── Widgets/
│   │       ├── AdminStatsOverview.php
│   │       ├── ManagerStatsOverview.php
│   │       ├── StudentStatsOverview.php
│   │       ├── BookingChart.php
│   │       ├── ManagerBookingChart.php
│   │       └── RevenueChart.php
│   │
│   ├── Providers/
│   │   ├── Filament/
│   │   │   └── AdminPanelProvider.php
│   │   └── ... other providers
│   │
│   └── Services/
│       ├── PaymentService.php
│       ├── SmsService.php
│       ├── BookingService.php
│       └── ReportService.php
│
├── database/
│   ├── migrations/                      # Database migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_02_11_225301_create_hostels_table.php
│   │   ├── 2026_02_11_105551_create_beds_table.php
│   │   ├── 2026_02_11_105624_create_complaints_table.php
│   │   ├── 2026_02_11_233017_create_payment_gateways_table.php
│   │   ├── 2026_02_11_233018_create_sms_campaigns_table.php
│   │   ├── 2026_02_11_233018_create_sms_providers_table.php
│   │   └── ... more migrations
│   │
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── ResetUsersSeeder.php
│   │   └── ... other seeders
│   │
│   └── database.sqlite                  # SQLite database
│
├── routes/
│   ├── auth.php                         # Authentication routes
│   ├── web.php                          # Web routes
│   └── console.php                      # Console commands
│
├── resources/
│   ├── views/
│   │   ├── layouts/                     # Layout templates
│   │   ├── admin/                       # Admin templates
│   │   ├── manager/                     # Manager templates
│   │   ├── student/                     # Student templates
│   │   ├── welcome.blade.php            # Home page
│   │   └── dashboard.blade.php          # Dashboard
│   │
│   └── css/
│       └── app.css                      # Tailwind styles
│
├── public/
│   ├── css/                             # Compiled CSS
│   ├── js/                              # Compiled JS
│   └── uploads/                         # User uploads
│
├── config/
│   ├── app.php                          # App config
│   ├── database.php                     # Database config
│   ├── filament.php                     # Filament config
│   └── ... other configs
│
├── storage/
│   ├── logs/                            # Application logs
│   ├── app/                             # App storage
│   └── framework/                       # Framework cache
│
├── tests/                               # Unit & Feature tests
│
├── .env.example                         # Environment template
├── composer.json                        # PHP dependencies
├── package.json                         # Node dependencies
├── vite.config.js                       # Vite configuration
├── phpunit.xml                          # Testing configuration
│
└── Documentation Files
    ├── README.md                        # Main README
    ├── SYSTEM_README.md                 # System overview
    ├── COMPLETE_SETUP_GUIDE.md          # Setup instructions
    ├── TESTING_CHECKLIST.md             # Testing guide
    ├── TEST_CREDENTIALS.md              # Login credentials
    ├── API_REFERENCE.md                 # API documentation
    └── DOCUMENTATION_INDEX.md           # This file
```

---

## Key Models & Relationships

### User Model
```php
User
├── has many Bookings
├── has many Beds
├── belongs to Hostel
└── has many Hostels (as owner)
```

### Hostel Model
```php
Hostel
├── has many Rooms
├── has many Students
├── has many Bookings
├── belongs to User (manager)
└── has many Users (students)
```

### Room Model
```php
Room
├── has many Beds
├── has many Bookings
└── belongs to Hostel
```

### Bed Model
```php
Bed
├── has many Allocations
├── belongs to Room
└── belongs to User
```

### Booking Model
```php
Booking
├── has one Payment
├── belongs to User (student)
├── belongs to Room
└── belongs to Hostel
```

### Payment Model
```php
Payment
├── belongs to Booking
├── belongs to PaymentGateway
└── has details (amount, status, reference)
```

---

## Database Schema Overview

### Core Tables

**users**
- id, name, email, password, role
- phone, id_number, address
- guardian_name, guardian_phone
- hostel_id, is_active
- Two-factor authentication fields

**hostels**
- id, name, address, phone
- manager_id, owner_id
- created_at, updated_at

**rooms**
- id, hostel_id, room_number
- room_type, capacity, price_per_month
- created_at, updated_at

**beds**
- id, room_id, bed_number
- is_occupied, assigned_to
- created_at, updated_at

**bookings**
- id, user_id, room_id, hostel_id
- start_date, end_date, status
- created_at, updated_at

**payments**
- id, booking_id, amount, status
- gateway, reference_code
- created_at, updated_at

**students**
- id, user_id, hostel_id
- admission_number, course
- department, level
- created_at, updated_at

### Configuration Tables

**system_settings**
- id, key (setting name)
- value (setting value)

**payment_gateways**
- id, gateway_name (paystack/flutterwave)
- public_key, secret_key, is_active

**sms_providers**
- id, provider_name, api_key
- api_url, is_active

**sms_campaigns**
- id, title, message, provider_id
- scheduled_at, status

---

## Getting Started Quick Commands

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed --class=ResetUsersSeeder

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Build assets
npm run build

# Watch for changes
npm run dev

# Run tests
php artisan test

# Tinker (interactive shell)
php artisan tinker
```

---

## Important Notes

### Authentication
- Uses Laravel Fortify for authentication
- Middleware for role-based access control
- Two-factor authentication support

### Authorization
- Role-based authorization (admin, manager, student)
- Policy-based checks in controllers
- Filament resource policies for admin panel

### Database
- Uses Eloquent ORM
- Relationships are fully defined
- Migration files track schema changes

### Payment Processing
- Webhooks for payment verification
- Secure API key storage
- Transaction logging
- Revenue tracking

### SMS Marketing
- Multiple provider support
- API integration
- Delivery tracking
- Campaign scheduling

---

## Common Tasks

### Add New User
```bash
php artisan tinker
> DB::table('users')->insert([...])
```
Or: Admin → Users → Create

### Create Hostel
Admin → Hostels → Create

### Manage Rooms
Admin → Rooms → Create/Edit/Delete

### View Bookings
Admin → Bookings (view all)
Manager → Bookings (view for hostel)
Student → My Bookings

### Configure Payment
Admin → Payment Gateways → Create/Edit

### Setup SMS Provider
Admin → SMS Providers → Create/Edit

### Send SMS Campaign
Admin → SMS Campaigns → Create → Send

---

## Support & Contact

For issues, questions, or support:
1. Check the relevant documentation file
2. Review the TESTING_CHECKLIST.md
3. Check application logs: `storage/logs/laravel.log`
4. Contact the development team

---

## Version Information

- **System Name**: Laravel Hostel Management System
- **Version**: 1.0.0
- **Laravel**: 12.51.0
- **Filament**: 3.x
- **Last Updated**: February 2026
- **Status**: ✅ Production Ready

---

## Quick Links

- 📖 [Main README](README.md)
- 🚀 [Setup Guide](COMPLETE_SETUP_GUIDE.md)
- 🧪 [Testing Guide](TESTING_CHECKLIST.md)
- 👤 [Login Credentials](TEST_CREDENTIALS.md)
- 📚 [System Overview](SYSTEM_README.md)
- 🔗 [API Reference](API_REFERENCE.md)

---

**System Status**: ✅ FULLY FUNCTIONAL AND READY FOR USE
