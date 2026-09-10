# Installation & Setup Guide

## Quick Start (5 minutes)

### Prerequisites
- XAMPP installed and running
- Git installed
- Modern web browser with camera

### Steps

1. **Clone Repository**
```bash
cd C:\xampp\htdocs
git clone https://github.com/Ms-Melit-2026/qr-attendance-system.git
cd qr-attendance-system
```

2. **Create Database**
```bash
mysql -u root < database/database.sql
```

3. **Configure Connection**
- Edit `config/database.php`
- Update DB credentials if needed

4. **Access Application**
```
http://localhost/qr-attendance-system/pages/login.php
```

5. **Login**
- Username: `admin`
- Password: `admin123`

---

## Detailed Setup

### 1. Install XAMPP

**Windows:**
1. Download XAMPP from https://www.apachefriends.org/
2. Run installer
3. Choose installation folder (default: C:\xampp)
4. Complete installation
5. Start Apache and MySQL from Control Panel

**Mac:**
1. Download XAMPP for Mac
2. Mount DMG file
3. Run installer
4. Installation path: /Applications/XAMPP
5. Start services

**Linux:**
```bash
sudo apt-get install xampp
```

### 2. Clone Project

```bash
# Navigate to webroot
cd /path/to/xampp/htdocs

# Clone repository
git clone https://github.com/Ms-Melit-2026/qr-attendance-system.git

# Navigate to project
cd qr-attendance-system
```

### 3. Create Database

**Method 1: Using phpMyAdmin**
1. Open http://localhost/phpmyadmin
2. Click "New"
3. Enter database name: `attendance_system`
4. Click "Create"
5. Click "Import" tab
6. Browse to `database/database.sql`
7. Click "Import"

**Method 2: Using Command Line**
```bash
mysql -u root -p < database/database.sql
# Press Enter when prompted for password (leave empty for XAMPP)
```

**Method 3: Direct SQL**
```bash
mysql -u root
CREATE DATABASE attendance_system;
USE attendance_system;
source database/database.sql;
EXIT;
```

### 4. Configure Application

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');          // Empty for XAMPP default
define('DB_NAME', 'attendance_system');
define('DB_PORT', 3306);
```

### 5. Set Permissions

**Linux/Mac:**
```bash
cd /path/to/qr-attendance-system

# Create directories
mkdir -p uploads logs

# Set permissions
chmod 755 uploads
chmod 755 logs
chmod 644 config/database.php
chmod 644 config/*.php

# If needed
sudo chown -R www-data:www-data .
```

**Windows:**
- Right-click folder → Properties
- Security tab → Edit → Check "Full Control"
- Apply to subfolders

### 6. Verify Installation

1. Open browser
2. Navigate to: `http://localhost/qr-attendance-system/pages/login.php`
3. Should see login page
4. Test with credentials:
   - Username: admin
   - Password: admin123

---

## Troubleshooting

### MySQL Connection Error

**Error**: "Connection failed: Connection refused"

**Solutions**:
1. Check if MySQL is running
2. Verify host/port in config/database.php
3. Check credentials
4. Enable MySQL port (default 3306)

### File Permissions Error

**Error**: "Permission denied"

**Solutions**:
```bash
chmod 777 config/
chmod 777 uploads/
chmod 777 logs/
```

### Headers Already Sent Error

**Error**: "Headers already sent"

**Solutions**:
1. Remove any BOM from PHP files
2. Check for spaces before `<?php`
3. Check for spaces after `?>`

### Camera Not Working

**Error**: "Camera access denied"

**Solutions**:
1. Check browser permissions
2. Use HTTPS or localhost
3. Enable camera in browser settings
4. Check if camera hardware is present

---

## Default Accounts

### Admin
```
Username: admin
Password: admin123
Email: admin@attendance.local
```

### Teacher
```
Username: teacher
Password: teacher123
Email: teacher@attendance.local
```

### Student
```
Username: student
Password: student123
Email: student@attendance.local
```

⚠️ **Change these immediately after first login!**

---

## Post-Installation

### 1. Change Admin Password
1. Login as admin
2. Click profile → Change Password
3. Enter old password: admin123
4. Enter new strong password
5. Confirm

### 2. Add Users
1. Go to Admin → User Management
2. Click "Add New User"
3. Fill details
4. Select role
5. Save

### 3. Create Classes
1. Go to Admin → Classes
2. Click "Add Class"
3. Enter class details
4. Select teacher
5. Save

### 4. Enroll Students
1. Go to Admin → Class Enrollment
2. Select class
3. Add students
4. Save

---

## Testing

### Test Admin Functions
1. Login as admin
2. Create a test class
3. Add test students
4. View dashboard

### Test Teacher Functions
1. Login as teacher
2. Go to "Generate QR Code"
3. Select class
4. Generate QR
5. Go to "Scan Attendance"
6. Test scanning

### Test Student Functions
1. Login as student
2. Go to "Mark Attendance"
3. Select class
4. Scan QR or enter manually
5. View attendance records

---

## Backup

### Daily Backup
```bash
# Backup database
mysqldump -u root -p attendance_system > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf project_backup_$(date +%Y%m%d).tar.gz /path/to/project/
```

### Schedule Automated Backup (Linux)
```bash
# Edit crontab
crontab -e

# Add line for daily backup at 2 AM
0 2 * * * mysqldump -u root -p[password] attendance_system > /backup/attendance_$(date +\%Y\%m\%d).sql
```

---

## Security Hardening

### 1. Change Permissions
```bash
chmod 644 config/*.php
chmod 755 uploads/
chmod 755 logs/
```

### 2. Hide PHP Files
Create `.htaccess` in root:
```apache
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>
```

### 3. Enable HTTPS
- Use self-signed certificate for localhost
- Use Let's Encrypt for production

### 4. Update Dependencies
```bash
# Update PHP packages (if using Composer)
composer update
```

---

## Performance Optimization

### Enable Caching
1. Edit config/database.php
2. Add caching configuration

### Database Optimization
```sql
-- Optimize tables
OPTIMIZE TABLE students;
OPTIMIZE TABLE teachers;
OPTIMIZE TABLE attendance;
OPTIMIZE TABLE classes;
```

### Compress Assets
- Minify CSS/JavaScript
- Compress images
- Enable gzip compression

---

## Next Steps

1. ✅ Installation complete
2. Create admin user account
3. Add teachers to system
4. Create classes
5. Enroll students
6. Generate QR codes
7. Start using system

---

For more help, see README.md