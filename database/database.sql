-- Smart Student Attendance Management System Database
-- MySQL Database Schema

CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Users Table (Admin, Teacher, Student)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Students Table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    student_id VARCHAR(50) UNIQUE NOT NULL,
    roll_number VARCHAR(50),
    class VARCHAR(50),
    section VARCHAR(10),
    phone VARCHAR(20),
    address TEXT,
    guardian_name VARCHAR(150),
    guardian_phone VARCHAR(20),
    enrollment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Teachers/Instructors Table
CREATE TABLE teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    teacher_id VARCHAR(50) UNIQUE NOT NULL,
    department VARCHAR(100),
    subject_specialization VARCHAR(150),
    phone VARCHAR(20),
    office_location VARCHAR(100),
    hire_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Classes/Subjects Table
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(100) NOT NULL,
    class_code VARCHAR(50) UNIQUE NOT NULL,
    teacher_id INT NOT NULL,
    semester VARCHAR(50),
    academic_year VARCHAR(10),
    schedule TEXT,
    room_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);

-- Class Enrollment Table
CREATE TABLE class_enrollment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'dropped', 'inactive') DEFAULT 'active',
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (class_id, student_id)
);

-- QR Codes Table
CREATE TABLE qr_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    qr_code_unique_id VARCHAR(100) UNIQUE NOT NULL,
    class_id INT NOT NULL,
    session_id VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    status ENUM('active', 'expired', 'used') DEFAULT 'active',
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    INDEX idx_class_id (class_id),
    INDEX idx_status (status)
);

-- Attendance Table
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    qr_code_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    time_in DATETIME,
    time_out DATETIME,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (qr_code_id) REFERENCES qr_codes(id) ON DELETE CASCADE,
    INDEX idx_student_date (student_id, attendance_date),
    INDEX idx_class_date (class_id, attendance_date),
    UNIQUE KEY unique_attendance (student_id, class_id, attendance_date, qr_code_id)
);

-- Attendance Reports Table
CREATE TABLE attendance_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    report_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    total_days INT DEFAULT 0,
    present_days INT DEFAULT 0,
    absent_days INT DEFAULT 0,
    late_days INT DEFAULT 0,
    excused_days INT DEFAULT 0,
    attendance_percentage DECIMAL(5, 2),
    report_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    INDEX idx_report_date (report_date)
);

-- Notifications Table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    student_id INT,
    notification_type ENUM('attendance', 'absence', 'late', 'report', 'system') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created_at (created_at)
);

-- Audit Log Table
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created_at (created_at)
);

-- System Settings Table
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Indexes for Performance
CREATE INDEX idx_user_role ON users(role);
CREATE INDEX idx_user_status ON users(status);
CREATE INDEX idx_student_class ON students(class);
CREATE INDEX idx_attendance_status ON attendance(status);
CREATE INDEX idx_class_teacher ON classes(teacher_id);

-- Insert default admin user (password: admin123 - hashed with password_hash())
INSERT INTO users (username, email, password, full_name, role, status) 
VALUES ('admin', 'admin@attendance.local', '$2y$10$Your_Hashed_Password_Here', 'Administrator', 'admin', 'active');

-- Insert default settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('qr_code_validity_minutes', '15', 'integer', 'QR code validity period in minutes'),
('late_threshold_minutes', '10', 'integer', 'Minutes after class start to mark as late'),
('attendance_threshold_percentage', '75', 'integer', 'Minimum attendance percentage required'),
('email_notifications_enabled', 'true', 'boolean', 'Enable email notifications'),
('system_name', 'Smart Attendance Management System', 'string', 'System name'),
('academic_year', '2024-2025', 'string', 'Current academic year');