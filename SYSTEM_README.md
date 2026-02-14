# 🏠 Laravel Hostel Management System

A comprehensive Laravel-based hostel management system designed for students, hostel managers, and administrators. The system enables students to book bed spaces, managers to manage their hostels, and administrators to oversee the entire operation.

## 🎯 Features

### For Students
- ✅ Browse available hostels and rooms
- ✅ View bed space details
- ✅ Make hostel bookings
- ✅ Track booking status
- ✅ Make online payments via Paystack/Flutterwave
- ✅ View booking history
- ✅ Manage personal profile
- ✅ Receive SMS notifications

### For Hostel Managers
- ✅ Manage assigned hostel details
- ✅ Create and manage rooms
- ✅ Manage bed allocations
- ✅ View student bookings
- ✅ Approve/reject booking requests
- ✅ Track occupancy rates
- ✅ Generate reports
- ✅ Manage student accounts

### For Admin/Owner
- ✅ Full system administration
- ✅ Manage all hostels
- ✅ Manage users (students, managers, admins)
- ✅ System settings and customization
- ✅ Payment gateway configuration (Paystack, Flutterwave)
- ✅ Marketing campaign management
- ✅ SMS marketing system with custom providers
- ✅ Financial reports and analytics
- ✅ Impersonate users for support
- ✅ System logo and branding management

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- Node.js & npm
- SQLite or MySQL

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Hostel
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed test data**
   ```bash
   php artisan db:seed --class=ResetUsersSeeder
   ```

8. **Build assets**
   ```bash
   npm run build
   ```

9. **Start the development server**
   ```bash
   php artisan serve
   ```

10. **Access the application**
    - Frontend: http://localhost:8000
    - Admin Panel: http://localhost:8000/admin

## 👤 Login Credentials

### Test Users

| Role | Email | Password | Access |
|------|-------|----------|--------|
| Admin | `admin@hostel.com` | `admin123` | `/admin` |
| Manager | `manager@hostel.com` | `manager123` | `/admin` |
| Student | `student@hostel.com` | `student123` | Dashboard |

## 📱 Admin Dashboard Features

Access the admin panel at `/admin` with your admin credentials.

### Modules Available

#### 1. **Hostel Management**
- Create, read, update, delete hostels
- Assign managers to hostels
- View hostel statistics
- Manage hostel configurations

#### 2. **Room Management**
- Create and manage rooms in hostels
- Define room types (single, double, triple, etc.)
- Set room pricing
- Track room availability

#### 3. **Bed Management**
- Add beds to rooms
- Track bed allocation status
- Manage bed assignments
- View occupancy status

#### 4. **Student Management**
- View all registered students
- Enable/disable student accounts
- Update student information
- Assign students to hostels

#### 5. **User Management**
- Manage admin accounts
- Manage manager accounts
- Create new users
- Control user roles and permissions

#### 6. **Booking Management**
- View all bookings
- Approve/reject pending bookings
- Track booking status
- Generate booking reports

#### 7. **Payment Management**
- View all payments
- Track transaction status
- Generate payment reports
- Monitor revenue

#### 8. **Payment Gateway Setup**
- Configure Paystack integration
- Configure Flutterwave integration
- Manage API keys securely
- Set transaction fees

#### 9. **System Settings**
- Customize application name
- Upload and manage logo
- Change primary and secondary colors
- Update footer text
- Set system-wide limits
- Configure email settings

#### 10. **Marketing Campaigns**
- Create marketing campaigns
- Manage campaign schedules
- Track campaign performance

#### 11. **SMS Marketing**
- Configure SMS providers (Termii, Afrimotion, AWS SNS, etc.)
- Create SMS campaigns
- Send bulk SMS messages
- Track SMS delivery
- View SMS analytics

#### 12. **User Impersonation**
- Log in as any student or manager
- Debug user issues
- Test user workflows
- Provide support

## 📊 Manager Dashboard Features

Managers can access their specific hostel management tools:

- **Dashboard**: Quick statistics and metrics
- **Rooms**: Create and manage rooms
- **Beds**: Manage bed allocations
- **Bookings**: View and manage student bookings
- **Students**: Manage students in their hostel
- **Reports**: Generate occupancy and revenue reports
- **Settings**: Manage hostel-specific settings

## 👨‍🎓 Student Dashboard Features

Students have access to:

- **Available Bookings**: Browse and search hostels
- **My Bookings**: View active and past bookings
- **Profile**: Update personal information
- **Payment History**: View past payments
- **Notifications**: SMS and email notifications about bookings

## 💳 Payment Integration

### Paystack Integration
1. Get your Paystack public and secret keys from [Paystack Dashboard](https://paystack.com)
2. Add keys in Admin → Payment Gateways → Configure Paystack
3. Configure webhook URL: `https://yourapp.com/webhook/paystack`

### Flutterwave Integration
1. Get your Flutterwave keys from [Flutterwave Dashboard](https://flutterwave.com)
2. Add keys in Admin → Payment Gateways → Configure Flutterwave
3. Configure webhook URL: `https://yourapp.com/webhook/flutterwave`

## 📱 SMS Marketing Setup

### Supported Providers
- **Termii**: Nigerian SMS provider
- **Afrimotion**: African SMS provider
- **AWS SNS**: Amazon Simple Notification Service
- **Twillio**: Global SMS provider (Custom)

### Configuration Steps
1. Go to Admin Dashboard → SMS Providers
2. Click "Create Provider"
3. Select provider and enter API credentials
4. Test the connection
5. Set as active

### Sending SMS Campaigns
1. Go to SMS Campaigns
2. Create new campaign
3. Select provider
4. Add message template
5. Select recipient list
6. Schedule or send immediately

## 🔐 Security Features

- Password hashing with bcrypt
- CSRF protection
- Role-based access control (RBAC)
- Secure payment processing
- Two-factor authentication ready
- Rate limiting on login attempts
- Session management

## 📁 Project Structure

```
Hostel/
├── app/
│   ├── Models/              # Eloquent models
│   ├── Http/
│   │   ├── Controllers/     # Application controllers
│   │   └── Middleware/      # Custom middleware
│   ├── Filament/
│   │   ├── Resources/       # Filament CRUD resources
│   │   ├── Pages/           # Custom pages
│   │   └── Widgets/         # Dashboard widgets
│   └── Providers/
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── routes/                  # Application routes
├── resources/
│   ├── views/               # Blade templates
│   └── css/                 # Stylesheets
├── config/                  # Configuration files
└── public/                  # Public assets
```

## 🗄️ Database Structure

### Core Tables
- **users**: User accounts (students, managers, admins)
- **hostels**: Hostel information and configuration
- **rooms**: Room details and types
- **beds**: Individual bed details and allocation
- **bookings**: Student booking records
- **allocations**: Bed-student allocations
- **payments**: Payment transaction records
- **students**: Student profile information

### Configuration Tables
- **system_settings**: System-wide configurations
- **payment_gateways**: Payment provider configurations
- **sms_providers**: SMS provider configurations
- **sms_campaigns**: SMS marketing campaigns
- **marketing_campaigns**: General marketing campaigns

## 🔧 Environment Configuration

Key environment variables in `.env`:

```
APP_NAME="Hostel Management System"
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

MAIL_DRIVER=log
MAIL_FROM_ADDRESS=noreply@hostel.com

PAYSTACK_PUBLIC_KEY=pk_...
PAYSTACK_SECRET_KEY=sk_...

FLUTTERWAVE_PUBLIC_KEY=FLWPUBK_...
FLUTTERWAVE_SECRET_KEY=FLWSECK_...
```

## 📞 Support

For issues or questions:
1. Check the system logs: `storage/logs/laravel.log`
2. Review the TEST_CREDENTIALS.md file
3. Check Filament admin panel for system health
4. Contact system administrator

## 🤝 Contributing

1. Create a feature branch
2. Make your changes
3. Test thoroughly
4. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 📅 Version

- **Current Version**: 1.0.0
- **Laravel Version**: 11.x
- **Filament Version**: 3.x

---

**Last Updated**: February 2026  
**Maintained by**: Development Team
