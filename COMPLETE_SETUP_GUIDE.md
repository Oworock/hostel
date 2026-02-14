# 🚀 Complete Setup and Deployment Guide

## System Overview

The Hostel Management System is built with:
- **Backend**: Laravel 11.x
- **Admin Panel**: Filament 3.x
- **Database**: SQLite (Development) / MySQL (Production)
- **Frontend**: Blade templates with Tailwind CSS
- **Payments**: Paystack & Flutterwave
- **SMS**: Multiple SMS providers integration

## Installation Steps

### 1. Prerequisites
```bash
# Check PHP version (must be 8.1+)
php -v

# Check Composer is installed
composer --version

# Check Node.js is installed
node --version
npm --version
```

### 2. Database Setup

#### SQLite (Development)
The system uses SQLite by default:
```bash
# Database file is created at database/database.sqlite
# No additional setup needed
```

#### MySQL (Production)
```bash
# Create database in MySQL
mysql -u root -p
> CREATE DATABASE hostel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
> EXIT;

# Update .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hostel_management
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Environment Configuration
```bash
# Copy environment template
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database
# Edit .env and set your database credentials
```

### 4. Install Dependencies
```bash
# Install PHP packages
composer install

# Install Node packages
npm install

# Build assets
npm run build
```

### 5. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed --class=ResetUsersSeeder
```

### 6. Start Application
```bash
# Development server
php artisan serve

# In another terminal, compile assets (if needed)
npm run dev
```

Access:
- Frontend: http://localhost:8000
- Admin: http://localhost:8000/admin

## User Management

### Creating New Users

#### Via Admin Panel
1. Go to `/admin`
2. Navigate to **Users**
3. Click **Create**
4. Fill in details (name, email, role, etc.)
5. Set password
6. Click **Create**

#### Via Artisan Command
```bash
php artisan tinker
> DB::table('users')->insert([
    'name' => 'New User',
    'email' => 'user@hostel.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    'role' => 'student', // or 'manager', 'admin'
    'is_active' => true,
    'created_at' => now(),
    'updated_at' => now(),
  ]);
```

### Assigning Hostels to Managers

1. Go to Admin → Users
2. Find and edit the manager user
3. In the **Hostel** field, select the hostel
4. Click **Save**

### Impersonating Users

As admin, to login as another user:
1. Go to Admin → Users
2. Find the user
3. Click the **Impersonate** button (if available)
4. You're now logged in as that user
5. Click "Back to Admin" to return to your admin account

## Hostel Setup

### Creating a Hostel

1. Go to Admin → **Hostels**
2. Click **Create**
3. Fill in details:
   - **Name**: Hostel name
   - **Address**: Physical location
   - **Phone**: Contact number
   - **Manager**: Assign a manager
4. Click **Create**

### Creating Rooms

1. Go to Admin → **Rooms** (or Manager Dashboard → Rooms)
2. Click **Create**
3. Fill in:
   - **Hostel**: Select the hostel
   - **Room Number**: Unique identifier
   - **Room Type**: Single, Double, Triple, etc.
   - **Capacity**: Number of beds
   - **Price per Month**: Rental price
4. Click **Create**

### Adding Beds

1. Go to Admin → **Beds** (or Manager → Beds)
2. Click **Create**
3. Fill in:
   - **Room**: Select the room
   - **Bed Number**: Identifier
   - **Status**: Available or Occupied
4. Click **Create**

## Payment Gateway Configuration

### Paystack Setup

1. Get your API keys from [Paystack Dashboard](https://dashboard.paystack.com)
   - Public Key: starts with `pk_`
   - Secret Key: starts with `sk_`

2. In Admin Panel:
   - Go to **Payment Gateways**
   - Create new gateway or edit existing
   - Select **Paystack**
   - Paste your keys
   - Click **Save**

3. Configure Webhook (Optional):
   - In Paystack settings, add webhook URL
   - URL: `https://yourapp.com/webhook/paystack`
   - Select events: `charge.success`, `charge.failed`

### Flutterwave Setup

1. Get your API keys from [Flutterwave Dashboard](https://dashboard.flutterwave.com)
   - Public Key: starts with `FLWPUBK_`
   - Secret Key: starts with `FLWSECK_`

2. In Admin Panel:
   - Go to **Payment Gateways**
   - Create new gateway
   - Select **Flutterwave**
   - Paste your keys
   - Click **Save**

3. Configure Webhook:
   - In Flutterwave settings, add webhook URL
   - URL: `https://yourapp.com/webhook/flutterwave`

## SMS Gateway Configuration

### Step 1: Choose Provider

Supported providers:
- **Termii** (Nigeria)
- **Afrimotion** (Africa)
- **AWS SNS** (Global)
- **Custom HTTP** (Any provider with HTTP API)

### Step 2: Add Provider

1. Go to Admin → **SMS Providers**
2. Click **Create**
3. Fill in:
   - **Name**: Provider name
   - **Provider Type**: Select provider
   - **API Key**: Your API credentials
   - **API URL**: Provider endpoint
4. Click **Test Connection** to verify
5. Mark as **Active**

### Step 3: Create SMS Campaign

1. Go to Admin → **SMS Campaigns**
2. Click **Create**
3. Fill in:
   - **Title**: Campaign name
   - **Provider**: Select configured provider
   - **Message**: Your SMS template
   - **Recipients**: Student list or group
   - **Schedule**: Immediate or scheduled
4. Click **Send**

## System Settings

### Accessing System Settings

1. Go to Admin → **System Customization**
2. Update:
   - **App Name**: Change application name
   - **Logo**: Upload new logo
   - **Description**: Update app description
   - **Colors**: Primary and secondary colors
   - **Footer Text**: Custom footer
   - **System Limits**: Max beds per room, max students per hostel

## Dashboard Widgets

### Admin Dashboard Shows
- Total Hostels
- Total Bookings
- Active Bookings
- Total Revenue
- Total Students
- System Users

### Manager Dashboard Shows
- Total Rooms
- Total Students
- Occupancy Rate
- Pending Bookings

### Student Dashboard Shows
- Active Booking Status
- Pending Bookings
- Completed Bookings

## Common Operations

### Making a Booking (As Student)
1. Login as student
2. Go to **Available Bookings**
3. Search/filter hostels
4. Select a room
5. Click **Book**
6. Complete payment via Paystack/Flutterwave
7. Wait for manager approval

### Approving Bookings (As Manager)
1. Go to **Bookings**
2. Click on pending booking
3. Click **Approve** or **Reject**
4. Add notes if needed
5. Click **Save**

### Generating Reports
1. Go to Admin → **Payments**
2. Filter by date range
3. Click **Export** (if available)
4. Download CSV or PDF

## Troubleshooting

### 500 Error on Login
- Check database connection in `.env`
- Run migrations: `php artisan migrate`
- Check storage permissions: `chmod -R 775 storage`

### Filament Resources Not Showing
- Clear cache: `php artisan cache:clear`
- Ensure User model has correct role checks
- Verify `shouldRegisterNavigation()` is public

### Payment Not Processing
- Verify API keys in Payment Gateway settings
- Check webhook logs in `storage/logs/`
- Ensure payment gateway is marked as active

### SMS Not Sending
- Verify SMS provider credentials
- Check SMS provider has sufficient balance
- Review SMS campaign logs
- Test provider connection

### Assets Not Loading
- Rebuild assets: `npm run build`
- Clear browser cache
- Check `public/` directory permissions

## Database Backup & Restore

### Backup SQLite
```bash
# Automatic backup
cp database/database.sqlite database/database.sqlite.backup

# Or use artisan
php artisan db:backup
```

### Backup MySQL
```bash
mysqldump -u root -p hostel_management > backup.sql
```

### Restore MySQL
```bash
mysql -u root -p hostel_management < backup.sql
```

## Performance Optimization

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Build frontend assets
npm run build
```

## Production Deployment

### 1. Prepare Server
```bash
# Install dependencies
sudo apt-get update
sudo apt-get install -y php8.2 php8.2-fpm php8.2-mysql php8.2-curl composer nodejs npm
```

### 2. Clone Repository
```bash
cd /var/www
git clone <repo> hostel
cd hostel
```

### 3. Install & Configure
```bash
composer install --no-dev
npm install
npm run build

cp .env.example .env
php artisan key:generate

# Configure .env for production
```

### 4. Setup Database
```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --class=ResetUsersSeeder
```

### 5. Configure Web Server

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/hostel/public;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 6. File Permissions
```bash
chown -R www-data:www-data /var/www/hostel
chmod -R 755 /var/www/hostel
chmod -R 775 /var/www/hostel/storage
chmod -R 775 /var/www/hostel/bootstrap/cache
```

### 7. SSL Certificate
```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### 8. Enable HTTPS
Update your `.env`:
```
APP_URL=https://yourdomain.com
SESSION_SECURE_COOKIES=true
```

## Regular Maintenance

### Daily
- Monitor error logs: `tail -f storage/logs/laravel.log`
- Check payment processing

### Weekly
- Database backup
- Review user registrations
- Check SMS delivery rates

### Monthly
- Security updates: `composer update`
- System logs cleanup
- Performance review

## Support & Documentation

- **System README**: See `SYSTEM_README.md`
- **Test Credentials**: See `TEST_CREDENTIALS.md`
- **API Reference**: See `API_REFERENCE.md`
- **Laravel Docs**: https://laravel.com/docs
- **Filament Docs**: https://filamentphp.com/docs

---

**Version**: 1.0.0  
**Last Updated**: February 2026
