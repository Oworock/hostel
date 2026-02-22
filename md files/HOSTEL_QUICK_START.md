# Quick Start Guide - Hostel Management System

## System Status
✅ **System is fully operational** with 3 user roles pre-configured:
- 1 Admin/Owner user
- 1 Manager user  
- 1 Student user

## Running the Application

### Start Development Server
```bash
cd /Users/oworock/Herd/Hostel
php artisan serve
```

The application will be available at: `http://localhost:8000`

## Access Points

### 1. Main Landing Page
- **URL**: `http://localhost:8000/`
- **Access**: Public

### 2. Admin/Owner Dashboard (Filament Panel)
- **URL**: `http://localhost:8000/admin`
- **Credentials**: 
  - Email: `admin@example.com`
  - Password: `password`
- **Features**:
  - Create and manage hostels
  - Manage users and roles
  - View system statistics
  - Configure payment gateways
  - System settings

### 3. Manager Dashboard
- **URL**: `http://localhost:8000/dashboard` (auto-redirects to manager area if logged in as manager)
- **Credentials**:
  - Email: `manager@example.com`
  - Password: `password`
- **Features**:
  - Manage rooms and beds
  - Review booking requests
  - Approve/reject bookings
  - Track payments

### 4. Student Dashboard
- **URL**: `http://localhost:8000/dashboard` (auto-redirects to student area if logged in as student)
- **Credentials**:
  - Email: `student@example.com`
  - Password: `password`
- **Features**:
  - Browse available rooms
  - Submit booking requests
  - View booking status
  - Process payments
  - File complaints

## Common Workflows

### Workflow 1: Admin Creates a Hostel

1. Navigate to `http://localhost:8000/admin`
2. Login as admin
3. Go to **Hostels** section
4. Click **+ New Hostel**
5. Fill in hostel details:
   - Name: e.g., "Students Paradise Hostel"
   - Address: Building location
   - City: City name
   - Phone: Contact number
   - Owner: Select from dropdown
   - Status: Toggle active/inactive
6. Click **Create** to save

### Workflow 2: Manager Creates Rooms

1. Login as manager
2. Go to **Rooms** section
3. Click **+ New Room**
4. Fill in room details:
   - Hostel: Select hostel
   - Room Number: e.g., "101"
   - Room Type: Single/Double/Triple/Quad
   - Capacity: Number of beds
   - Price per Bed: Monthly rental cost
   - Status: Available/Full/Maintenance
5. Save the room

### Workflow 3: Manager Adds Beds to Room

1. After creating a room, go to **Beds**
2. Click **+ New Bed**
3. Fill in bed details:
   - Room: Select the room you created
   - Bed Number: e.g., "1", "2", etc.
   - Status: Available/Occupied/Maintenance
4. Save the bed

### Workflow 4: Student Books a Bed

1. Login as student
2. Click **Browse Available Rooms**
3. Select a room with available beds
4. Click **Book Now**
5. Fill booking details:
   - Check-in Date: When student will move in
   - Duration: Number of months
6. Submit booking request
7. Payment section appears after approval

### Workflow 5: Manager Approves Booking

1. Login as manager
2. Go to **Bookings** → **Pending**
3. Review student's booking request
4. Click **Approve** or **Reject**
5. Student receives notification

### Workflow 6: Student Pays for Booking

1. Login as student
2. Go to **My Bookings**
3. Click on approved booking
4. Process payment via available payment methods
5. Payment confirmed and booking activated

## Database

### Reset Database (if needed)
```bash
# Run all migrations
php artisan migrate --fresh

# Seed initial data
php artisan db:seed
```

### Tinker Console - Quick Test
```bash
php artisan tinker

# Create a test hostel
>>> $hostel = App\Models\Hostel::create([
    'name' => 'Test Hostel',
    'address' => '123 Student Lane',
    'city' => 'College City',
    'phone' => '555-1234',
    'owner_id' => 1,
    'is_active' => true
]);

# List all hostels
>>> App\Models\Hostel::all();

# Exit
>>> exit;
```

## Troubleshooting

### Issue: Route not found error
**Solution**: Clear cache and regenerate routes
```bash
php artisan route:clear
php artisan route:cache
```

### Issue: Cannot access admin panel
**Solution**: Ensure Filament is properly installed
```bash
php artisan filament:install
```

### Issue: Database errors
**Solution**: Check database connection in `.env` and run migrations
```bash
php artisan migrate
```

### Issue: Asset compilation errors
**Solution**: Rebuild frontend assets
```bash
npm run dev
```

## API Response Examples

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "student@example.com",
    "password": "password"
  }'
```

### Create Booking
```bash
curl -X POST http://localhost:8000/api/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "room_id": 1,
    "check_in_date": "2024-03-15",
    "duration_months": 6
  }'
```

## Key Files & Locations

| Component | Location |
|-----------|----------|
| Models | `app/Models/` |
| Controllers | `app/Http/Controllers/` |
| Routes | `routes/web.php` |
| Migrations | `database/migrations/` |
| Blade Views | `resources/views/` |
| Filament Resources | `app/Filament/Resources/` |
| Config | `config/` |

## System Capabilities

✅ Multi-hostel support
✅ Room and bed management
✅ Student booking system
✅ Payment tracking
✅ Role-based access control
✅ Complaint management
✅ Admin dashboard
✅ Manager dashboard
✅ Student dashboard
✅ Email verification
✅ Filament admin panel

## Next Steps

1. **Create test data**: Use admin panel to add hostels, rooms, and beds
2. **Test booking flow**: Login as student and create a booking request
3. **Approve bookings**: Login as manager and approve requests
4. **Process payments**: Complete payment for approved bookings
5. **Monitor system**: Check admin dashboard for statistics

## Support Resources

- **Full Documentation**: See `HOSTEL_SYSTEM_OVERVIEW.md`
- **API Reference**: See `API_REFERENCE.md`
- **Setup Instructions**: See `SETUP_GUIDE.md`

---

**System Ready!** 🎉 Start booking hostels now!
