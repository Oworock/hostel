# Test Credentials & User Accounts

## Pre-configured User Accounts

The system comes with three pre-created user accounts, one for each role:

### 1. Admin/Owner Account
**Role**: Hostel Owner/Administrator  
**Email**: `admin@hostel.com`  
**Password**: `password`  
**Access URL**: `http://localhost:8000/admin`

**Permissions**:
- Manage all hostels
- Create and manage users
- Assign manager roles
- View system-wide statistics
- Configure payment gateways
- System settings and customization
- Generate financial reports
- User management

---

### 2. Manager Account
**Role**: Hostel Manager  
**Email**: `manager@hostel.com`  
**Password**: `password`  
**Access URL**: `http://localhost:8000/manager`

**Permissions**:
- Manage assigned hostel(s)
- Add and configure rooms
- Add and manage beds
- Review booking requests
- Approve/reject bookings
- Track payments
- Generate occupancy reports
- Respond to complaints

---

### 3. Student Account
**Role**: Student  
**Email**: `student@hostel.com`  
**Password**: `password`  
**Access URL**: `http://localhost:8000/student`

**Permissions**:
- Browse available rooms and beds
- Submit booking requests
- View booking status and history
- Process payments
- Cancel bookings
- File complaints
- View assigned bed/room details

---

## Login Instructions

### Via Web Browser

1. **For Admin Panel**:
   - Navigate to `http://localhost:8000/admin`
   - Enter email: `admin@hostel.com`
   - Enter password: `password`
   - Click "Sign In"

2. **For Manager Dashboard**:
   - Navigate to `http://localhost:8000/dashboard`
   - Login with manager credentials
   - System redirects to manager panel

3. **For Student Dashboard**:
   - Navigate to `http://localhost:8000/dashboard`
   - Login with student credentials
   - System redirects to student panel

### Via API (Programmatic)

```bash
# Step 1: Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "student@hostel.com",
    "password": "password"
  }'

# Response contains authentication token
# {
#   "token": "your-auth-token",
#   "user": { ... }
# }

# Step 2: Use token for subsequent requests
curl http://localhost:8000/api/bookings \
  -H "Authorization: Bearer your-auth-token"
```

---

## Creating Additional Test Users

### Via Tinker Console

```bash
php artisan tinker

# Create additional student
>>> App\Models\User::create([
    'name' => 'Jane Student',
    'email' => 'jane@student.com',
    'password' => Hash::make('password'),
    'role' => 'student',
    'email_verified_at' => now()
]);

# Create additional manager
>>> App\Models\User::create([
    'name' => 'John Manager',
    'email' => 'john@manager.com',
    'password' => Hash::make('password'),
    'role' => 'manager',
    'email_verified_at' => now()
]);

# Exit
>>> exit
```

### Via Admin Panel

1. Login as admin
2. Navigate to **Users** section
3. Click **+ New User**
4. Fill in details:
   - Name
   - Email
   - Password
   - Role (student/manager/admin)
5. Click **Create**

---

## Test Data Scenarios

### Scenario 1: Complete Booking Flow

1. **As Admin**:
   - Create a hostel: "Tech Campus Hostel"
   - Assign a manager

2. **As Manager**:
   - Add a room: "Room 101" with 4 beds, $150/month
   - Add 4 beds to the room

3. **As Student**:
   - Browse available rooms
   - Request booking for "Room 101", Bed 1
   - Request approved by manager
   - Process payment

### Scenario 2: Multiple Hostels

```bash
# Create multiple test hostels
php artisan tinker

>>> App\Models\Hostel::create([
    'name' => 'Downtown Dorm',
    'address' => '123 Main Street',
    'city' => 'Boston',
    'phone' => '617-555-0101',
    'owner_id' => 1,
    'is_active' => true
]);

>>> App\Models\Hostel::create([
    'name' => 'Uptown Residences',
    'address' => '456 Park Avenue',
    'city' => 'Boston',
    'phone' => '617-555-0102',
    'owner_id' => 1,
    'is_active' => true
]);

>>> exit
```

### Scenario 3: Multiple Bookings

Create multiple students and test concurrent bookings:

```bash
# Create 3 test students
php artisan tinker

>>> for($i = 1; $i <= 3; $i++) {
    App\Models\User::create([
        'name' => "Test Student $i",
        'email' => "test$i@student.com",
        'password' => Hash::make('password'),
        'role' => 'student',
        'email_verified_at' => now()
    ]);
}

>>> exit
```

---

## Password Management

### Resetting a User's Password

```bash
php artisan tinker

>>> $user = App\Models\User::where('email', 'student@hostel.com')->first();
>>> $user->update(['password' => Hash::make('newpassword')]);
>>> exit
```

### Creating Password Reset Token

The system uses Laravel's password reset functionality:

```bash
php artisan tinker

>>> $user = App\Models\User::where('email', 'admin@hostel.com')->first();
>>> $token = Password::createToken($user);
>>> echo $token;
>>> exit
```

---

## Account Verification Status

All test accounts have email verification enabled:

| Email | Verified | Status |
|-------|----------|--------|
| admin@hostel.com | ✅ Yes | Active |
| manager@hostel.com | ✅ Yes | Active |
| student@hostel.com | ✅ Yes | Active |

---

## Role Permissions Matrix

| Action | Student | Manager | Admin |
|--------|---------|---------|-------|
| View Dashboard | ✅ | ✅ | ✅ |
| Browse Rooms | ✅ | ✅ | ✅ |
| Create Room | ❌ | ✅ | ✅ |
| Add Bed | ❌ | ✅ | ✅ |
| Request Booking | ✅ | ❌ | ❌ |
| Approve Booking | ❌ | ✅ | ✅ |
| Create Hostel | ❌ | ❌ | ✅ |
| Manage Users | ❌ | ❌ | ✅ |
| View Reports | ❌ | ✅ | ✅ |
| System Settings | ❌ | ❌ | ✅ |
| File Complaint | ✅ | ❌ | ❌ |
| Respond to Complaint | ❌ | ✅ | ✅ |

---

## API Authentication

### Getting Auth Token

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@hostel.com",
    "password": "password"
  }'
```

**Response**:
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@hostel.com",
    "role": "admin"
  }
}
```

### Using Auth Token

```bash
curl http://localhost:8000/api/hostels \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

---

## Common Test Requests

### 1. Get User Profile
```bash
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer {token}"
```

### 2. List All Hostels
```bash
curl http://localhost:8000/api/hostels \
  -H "Authorization: Bearer {token}"
```

### 3. Create a Booking
```bash
curl -X POST http://localhost:8000/api/bookings \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "room_id": 1,
    "check_in_date": "2024-03-15",
    "duration_months": 6
  }'
```

### 4. Approve a Booking
```bash
curl -X PATCH http://localhost:8000/api/bookings/1/approve \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Debugging Tips

### Check User Roles
```bash
php artisan tinker
>>> App\Models\User::pluck('email', 'role');
>>> exit
```

### List All Bookings
```bash
php artisan tinker
>>> App\Models\Booking::with('user', 'room')->get();
>>> exit
```

### Check Hostel Configuration
```bash
php artisan tinker
>>> App\Models\Hostel::with('rooms', 'owner')->get();
>>> exit
```

---

## Session Management

### Default Session Configuration
- Session driver: `cookie`
- Session lifetime: `120` minutes
- Secure cookies: Enabled in production

### Testing with Multiple Sessions
Open different browser windows/tabs and login as different users to simulate multi-user scenarios.

---

For more detailed API documentation, see **API_REFERENCE.md**
For setup instructions, see **SETUP_GUIDE.md**
For system overview, see **HOSTEL_SYSTEM_OVERVIEW.md**
