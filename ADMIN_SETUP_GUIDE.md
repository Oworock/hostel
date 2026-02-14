# Admin Setup & Management Guide

## Test Credentials

### Admin Account
- **Email:** admin@hostel.com
- **Password:** Password123
- **Role:** Admin (Full System Access)

### Manager Account
- **Email:** manager@hostel.com
- **Password:** Password123
- **Role:** Manager (Hostel Management)
- **Assigned Hostel:** Main Hostel

### Student Account
- **Email:** student@hostel.com
- **Password:** Password123
- **Role:** Student (Booking & Profile Management)

---

## Admin Dashboard Features

Access the admin panel at: `/admin`

### 1. User Management
**Path:** Admin Dashboard → User Management → Users

**Capabilities:**
- Create, read, update, delete user accounts
- Assign users to roles (Admin, Manager, Student)
- Assign hostels to managers
- View user activity logs
- Filter by role and status

**Fields:**
- Name (required)
- Email (unique, required)
- Password (hashed, required for creation)
- Phone number
- ID number
- Address
- Guardian name & phone (for students)
- Role selection
- Hostel assignment
- Active/Inactive toggle

---

### 2. Hostel Management
**Path:** Admin Dashboard → Hostel Management → Hostels

**Capabilities:**
- Create and manage multiple hostels
- Assign hostel owners/admins
- Track hostel details and contact information
- Monitor hostel status
- Bulk operations

**Fields:**
- Hostel name (required)
- Description
- Address (required)
- City (required)
- Phone number
- Owner selection
- Active/Inactive status

---

### 3. System Settings
**Path:** Admin Dashboard → System → System Settings

**Manage:**
- Application name & branding
- System configuration parameters
- Global settings
- Custom key-value settings

**Available Settings:**
- `app_name` - Application name
- `app_color` - Primary brand color
- `company_email` - Contact email
- `company_phone` - Contact phone
- `logo_url` - Logo URL
- Any custom settings as needed

---

### 4. Payment Gateway Management
**Path:** Admin Dashboard → Payments → Payment Gateways

**Supported Gateways:**
- Paystack
- Flutterwave
- Others (extensible)

**Configuration:**
- Gateway name
- Public key
- Secret key (encrypted)
- Transaction fee percentage
- Enable/Disable toggle

**Steps to Configure:**
1. Navigate to Payment Gateways
2. Click "Create Payment Gateway"
3. Enter gateway details:
   - Name (e.g., "Paystack")
   - Public Key (from your provider)
   - Secret Key (from your provider)
   - Transaction Fee (%)
4. Toggle "Active" to enable
5. Save

---

### 5. SMS Provider Management
**Path:** Admin Dashboard → SMS & Marketing → SMS Providers

**Supported Providers:**
- Twilio
- Termii
- Africa's Talking
- Custom providers (extensible)

**Configuration:**
- Provider name
- API key (encrypted)
- API secret (if required)
- Sender ID (for SMS)
- Additional configuration

**Steps to Configure:**
1. Navigate to SMS Providers
2. Click "Create SMS Provider"
3. Enter provider credentials:
   - Provider name (e.g., "Twilio")
   - API Key (from your provider)
   - API Secret (if required)
   - Sender ID (your registered number/name)
   - Custom configuration (JSON key-value)
4. Toggle "Active" to enable
5. Save

---

### 6. Room Management
**Path:** Admin Dashboard → Hostel Management → Rooms

**Capabilities:**
- Create rooms in hostels
- Set room types and capacity
- Track room availability
- Manage room status

---

### 7. Bed Management
**Path:** Admin Dashboard → Hostel Management → Beds

**Capabilities:**
- Manage individual beds
- Track bed occupancy
- Set bed status
- Associate beds with rooms

---

### 8. Student Management
**Path:** Admin Dashboard → User Management → Students

**Capabilities:**
- View all student records
- Track student information
- Monitor student bookings
- Manage student status

---

### 9. Booking Management
**Path:** Admin Dashboard → Operations → Bookings

**Capabilities:**
- View all system bookings
- Approve/reject bookings
- Track booking status
- Monitor payment status

---

### 10. Payment Tracking
**Path:** Admin Dashboard → Operations → Payments

**Capabilities:**
- View all payments
- Track payment status
- Generate payment reports
- Monitor transaction details

---

### 11. Complaint Management
**Path:** Admin Dashboard → Operations → Complaints

**Capabilities:**
- View student complaints
- Track complaint status
- Assign complaints for resolution
- Generate complaint reports

---

### 12. Allocations
**Path:** Admin Dashboard → Hostel Management → Allocations

**Capabilities:**
- Manage bed allocations
- Assign beds to students
- Track allocation history
- Manage occupancy

---

## Manager Dashboard Features

### Access
- Manager panel at: `/dashboard`
- Role-based access control

### Capabilities
1. **Room Management** - Create and manage rooms in assigned hostel
2. **Booking Management** - Approve/reject student bookings
3. **Student Management** - View students in their hostel
4. **Dashboard Statistics** - Track occupancy rates, bookings, revenue
5. **Account Management** - Update their profile

---

## Student Dashboard Features

### Access
- Student portal at: `/dashboard`
- Limited access to personal data

### Capabilities
1. **Browse Available Rooms** - View available rooms and beds
2. **Make Bookings** - Book available beds
3. **My Bookings** - View booking history and status
4. **Payment Processing** - Pay for bookings via Paystack/Flutterwave
5. **Account Management** - Update profile information
6. **Submit Complaints** - Report issues to hostel management

---

## Important Admin Tasks

### Initial Setup Checklist
- [ ] Create admin account (if needed)
- [ ] Create manager accounts
- [ ] Create test student accounts
- [ ] Create hostels
- [ ] Assign managers to hostels
- [ ] Configure payment gateways (Paystack, Flutterwave)
- [ ] Configure SMS providers
- [ ] Create rooms in hostels
- [ ] Create beds in rooms
- [ ] Configure system settings
- [ ] Test login functionality
- [ ] Test booking workflow

### Regular Maintenance
- Monitor system performance
- Review complaint reports
- Verify payment reconciliation
- Update system settings as needed
- Archive old bookings
- Generate monthly reports

---

## Security Notes

⚠️ **Important:**
- Never share API keys or secrets publicly
- Rotate payment gateway keys regularly
- Monitor all user activity
- Maintain backup of system data
- Keep SMS provider credentials secure

---

## Troubleshooting

### Cannot access admin panel?
- Verify user role is set to "admin"
- Check email verification status
- Clear browser cache

### Payment gateway not processing?
- Verify keys are correct
- Check gateway is enabled
- Test with sandbox/test credentials first
- Verify transaction fees are correctly configured

### SMS not being sent?
- Verify SMS provider is enabled
- Check API credentials
- Verify sender ID is registered
- Check account has sufficient balance

---

## Contact & Support
For issues or questions, please contact the system administrator.
