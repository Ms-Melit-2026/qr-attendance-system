<?php
/**
 * Application Constants
 */

// Application Information
define('APP_NAME', 'Smart Attendance Management System');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'Development Team');
define('APP_URL', 'http://localhost/qr-attendance-system/');

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_TEACHER', 'teacher');
define('ROLE_STUDENT', 'student');

// User Status
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');

// Attendance Status
define('ATTENDANCE_PRESENT', 'present');
define('ATTENDANCE_ABSENT', 'absent');
define('ATTENDANCE_LATE', 'late');
define('ATTENDANCE_EXCUSED', 'excused');

// QR Code Status
define('QR_STATUS_ACTIVE', 'active');
define('QR_STATUS_EXPIRED', 'expired');
define('QR_STATUS_USED', 'used');

// System Settings
define('QR_VALIDITY_MINUTES', 15);
define('LATE_THRESHOLD_MINUTES', 10);
define('ATTENDANCE_THRESHOLD', 75);

// File Upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'xlsx', 'csv']);

// Session
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('SESSION_PREFIX', 'attendance_');

// Pagination
define('ITEMS_PER_PAGE', 20);

// Report Types
define('REPORT_DAILY', 'daily');
define('REPORT_WEEKLY', 'weekly');
define('REPORT_MONTHLY', 'monthly');

// Notification Types
define('NOTIFICATION_ATTENDANCE', 'attendance');
define('NOTIFICATION_ABSENCE', 'absence');
define('NOTIFICATION_LATE', 'late');
define('NOTIFICATION_REPORT', 'report');
define('NOTIFICATION_SYSTEM', 'system');

// DateTime Formats
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd M Y');
define('DISPLAY_DATETIME_FORMAT', 'd M Y, H:i A');
?>