# Smart Student Attendance Management System using QR Code Technology

## Overview

A comprehensive web-based attendance management system that uses QR code technology to automate student attendance tracking. Built with PHP, MySQL, and Bootstrap for a modern, responsive user interface.

### Key Features

✅ **QR Code Generation** - Teachers can generate unique QR codes for each class session
✅ **QR Code Scanning** - Students scan QR codes to mark attendance
✅ **Real-time Attendance** - Automatic time-in/time-out tracking
✅ **Role-based Access** - Admin, Teacher, and Student dashboards
✅ **Attendance Reports** - Daily, Weekly, and Monthly reports
✅ **Late & Absence Monitoring** - Automatic late marking based on threshold
✅ **Analytics Dashboard** - Visual attendance statistics
✅ **Export Reports** - PDF/Excel export functionality
✅ **Notifications** - Email/SMS alerts for absences
✅ **Audit Logging** - Complete system activity tracking
✅ **Search & Filtering** - Advanced search capabilities
✅ **Multi-user Support** - Support for multiple administrators and teachers

---

## System Requirements

- **Server**: XAMPP 7.4+
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Database**: MySQL/MariaDB
- **Browser**: Modern browser with Camera support (Chrome, Firefox, Safari, Edge)
- **Libraries**: 
  - Bootstrap 4.5+
  - Font Awesome 5.15+
  - jQuery 3.5+
  - jsQR (QR code scanning)
  - QRCode.js (QR code generation)

---

## Installation & Setup

### Step 1: Download and Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP on your system
3. Start Apache and MySQL services from the XAMPP Control Panel

### Step 2: Clone the Repository

```bash
cd C:/xampp/htdocs  # Windows
# or
cd /Applications/XAMPP/htdocs  # Mac
# or
cd /opt/lampp/htdocs  # Linux

git clone https://github.com/Ms-Melit-2026/qr-attendance-system.git
cd qr-attendance-system
```

### Step 3: Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" and create a database named `attendance_system`
3. Import the database schema:
   - Go to Import tab
   - Select `database/database.sql`
   - Click Import

**OR** Run SQL directly:

```sql
mysql -u root -p < database/database.sql
```

### Step 4: Configure Database Connection

1. Open `config/database.php`
2. Update the following if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');  // Leave empty for XAMPP default
define('DB_NAME', 'attendance_system');
```

### Step 5: Set File Permissions

```bash
# Create necessary directories
mkdir -p uploads logs

# Set permissions (Linux/Mac)
chmod 755 uploads
chmod 755 logs
chmod 644 config/database.php
```

### Step 6: Access the Application

Open your browser and navigate to:
```
http://localhost/qr-attendance-system/pages/login.php
```

---

## Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Role**: Administrator

### Demo Teacher Account
- **Username**: `teacher`
- **Password**: `teacher123`
- **Role**: Teacher/Instructor

### Demo Student Account
- **Username**: `student`
- **Password**: `student123`
- **Role**: Student

⚠️ **Important**: Change these passwords after first login!

---

## Project Structure

```
qr-attendance-system/
├── config/
│   ├── database.php          # Database configuration
│   ├── constants.php         # Application constants
│   └── session.php           # Session management
├── classes/
│   ├── Database.php          # Database wrapper class
│   ├── User.php              # User management
│   ├── Student.php           # Student management
│   ├── Teacher.php           # Teacher management
│   ├── ClassManagement.php   # Class management
│   ├── QRCode.php            # QR code generation
│   ├── Attendance.php        # Attendance tracking
│   ├── Report.php            # Report generation
│   ├── Notification.php      # Notifications
│   └── Export.php            # Export functionality
├── helpers/
│   ├── helpers.php           # Helper functions
│   └── APIResponse.php       # API response handler
├── pages/
│   ├── login.php             # Login page
│   ├── logout.php            # Logout
│   ├── admin/
│   │   ├── dashboard.php     # Admin dashboard
│   │   ├── users.php         # User management
│   │   ├── students.php      # Student management
│   │   ├── teachers.php      # Teacher management
│   │   ├── classes.php       # Class management
│   │   ├── reports.php       # Report generation
│   │   └── settings.php      # System settings
│   ├── teacher/
│   │   ├── dashboard.php     # Teacher dashboard
│   │   ├── scan-attendance.php      # Attendance scanning
│   │   ├── generate-qr.php   # QR code generation
│   │   ├── classes.php       # My classes
│   │   ├── attendance.php    # Attendance records
│   │   └── reports.php       # Class reports
│   └── student/
│       ├── dashboard.php     # Student dashboard
│       ├── mark-attendance.php      # Mark attendance
│       ├── attendance.php    # View attendance
│       └── reports.php       # Personal reports
├── database/
│   └── database.sql          # Database schema
├── uploads/                  # User uploads directory
├── logs/                     # Application logs
└── README.md                 # This file
```

---

## User Roles & Permissions

### Admin
- ✓ User management (Create, Edit, Delete)
- ✓ Student management
- ✓ Teacher management
- ✓ Class management
- ✓ View all reports
- ✓ System settings
- ✓ Audit logs

### Teacher/Instructor
- ✓ Generate QR codes for classes
- ✓ Scan student attendance
- ✓ View class attendance records
- ✓ Generate class reports
- ✓ Manage their classes
- ✓ View student attendance

### Student
- ✓ Mark attendance via QR code
- ✓ View personal attendance records
- ✓ View attendance statistics
- ✓ Download attendance reports
- ✓ View notifications

---

## How to Use

### For Teachers: Marking Attendance

1. **Generate QR Code**:
   - Login with teacher credentials
   - Go to "Generate QR Code"
   - Select class and validity period
   - Display the QR code in classroom
   - Optionally print or download

2. **Scan Student Attendance**:
   - Go to "Scan Attendance"
   - Select the class
   - Students scan QR or enter manually
   - Attendance is marked automatically

### For Students: Marking Attendance

1. **Via QR Code Scanner**:
   - Login with student credentials
   - Go to "Mark Attendance"
   - Select your class
   - Point camera at QR code
   - Attendance marked automatically

2. **Manual Entry**:
   - Select class
   - Enter QR code manually
   - Submit

### For Admins: Managing System

1. **User Management**:
   - Create new admin/teacher/student accounts
   - Edit user details
   - Activate/Deactivate users
   - Change user roles

2. **Generate Reports**:
   - View attendance by student
   - View attendance by class
   - Generate monthly reports
   - Export to PDF/Excel

---

## Database Schema

### Key Tables

**users** - System users (admin, teacher, student)
**students** - Student profiles
**teachers** - Teacher/instructor profiles
**classes** - Class definitions
**class_enrollment** - Student-class relationships
**qr_codes** - Generated QR codes
**attendance** - Attendance records
**attendance_reports** - Generated reports
**notifications** - System notifications
**audit_logs** - Activity logging
**system_settings** - Configuration settings

---

## Features in Detail

### 1. QR Code Technology
- Unique QR code generated for each class session
- 15-minute validity by default (configurable)
- Automatic expiration after validity period
- Can be printed or displayed digitally

### 2. Attendance Tracking
- Automatic time-in recording
- Optional time-out tracking
- Late detection based on configurable threshold
- Absence marking for students not present
- Excused absence functionality

### 3. Reporting System
- Daily attendance reports
- Weekly attendance summaries
- Monthly attendance statistics
- Class-wise attendance analysis
- Student-wise attendance tracking
- Export to PDF and Excel formats

### 4. Analytics
- Attendance percentage calculation
- Trend analysis
- Late arrival tracking
- Absence monitoring
- Statistical visualizations

### 5. Notifications
- Absence alerts
- Late attendance notifications
- System announcements
- Email integration ready

### 6. Security
- Password hashing (bcrypt)
- Session management
- Role-based access control
- SQL injection prevention
- XSS protection
- CSRF token validation
- Audit logging

---

## Configuration

### System Settings (in database)

```sql
-- QR code validity (minutes)
qr_code_validity_minutes = 15

-- Late threshold (minutes after class start)
late_threshold_minutes = 10

-- Minimum attendance percentage
attendance_threshold_percentage = 75

-- Email notifications enabled
email_notifications_enabled = true
```

Edit these in Admin Panel → Settings

---

## API Endpoints

The system provides REST API endpoints for integration:

```
POST   /api/attendance/mark          - Mark attendance
GET    /api/attendance/student       - Get student attendance
GET    /api/attendance/class         - Get class attendance
GET    /api/qrcode/verify            - Verify QR code
GET    /api/reports/generate         - Generate report
```

---

## Troubleshooting

### Issue: Database connection failed
**Solution**: Check database.php configuration and ensure MySQL is running

### Issue: Camera not working
**Solution**: 
- Enable camera permissions in browser
- Use HTTPS or localhost
- Check browser compatibility

### Issue: QR code not scanning
**Solution**:
- Ensure good lighting
- Hold camera steady
- Check QR code validity hasn't expired
- Try manual entry

### Issue: Attendance not saving
**Solution**:
- Check database permissions
- Verify student enrollment in class
- Check server error logs in `/logs/error.log`

### Issue: Session timeout
**Solution**: Adjust session timeout in `config/session.php`

---

## Performance Optimization

1. **Database Indexes**: Indexes on frequently queried columns
2. **Pagination**: Large result sets are paginated
3. **Caching**: Settings cached in sessions
4. **Lazy Loading**: Images and resources load on demand

---

## Security Best Practices

1. Change default passwords immediately
2. Use HTTPS in production
3. Regularly backup database
4. Update PHP and MySQL regularly
5. Disable directory listing
6. Use strong passwords
7. Implement 2FA (can be added)
8. Regular security audits

---

## Backup & Recovery

### Backup Database

```bash
mysqldump -u root -p attendance_system > backup.sql
```

### Restore Database

```bash
mysql -u root -p attendance_system < backup.sql
```

---

## Future Enhancements

- [ ] Two-factor authentication (2FA)
- [ ] Mobile app (React Native/Flutter)
- [ ] Biometric attendance
- [ ] SMS/Email notifications
- [ ] RFID card support
- [ ] Facial recognition
- [ ] Advanced analytics dashboard
- [ ] AI-based absence prediction
- [ ] Integration with SIS

---

## Frequently Asked Questions

**Q: Can I modify the QR code validity?**
A: Yes, go to Admin → Settings and change `qr_code_validity_minutes`

**Q: Can students mark attendance multiple times?**
A: No, the system prevents duplicate attendance on same date

**Q: How do I reset a student's attendance?**
A: Admin can delete individual attendance records from reports

**Q: Can teachers export attendance reports?**
A: Yes, teachers can export class reports to PDF/Excel

**Q: Is there a mobile app?**
A: Currently web-based. Mobile app planned for future.

---

## Support & Contact

For issues, suggestions, or contributions:
- GitHub Issues: [Issues Page]
- Email: support@attendance-system.local

---

## License

This project is open source and available under the MIT License.

---

## Credits

Developed by the Smart Attendance Management System Team

**Technologies Used**:
- PHP 7.4+
- MySQL 5.7+
- Bootstrap 4.5
- jQuery 3.5+
- Font Awesome 5.15+
- jsQR library
- QRCode.js library

---

## Version History

### v1.0.0 (2024)
- Initial release
- QR code attendance system
- Basic reporting features
- User role management
- Admin dashboard

---

**Last Updated**: September 2024
**Version**: 1.0.0