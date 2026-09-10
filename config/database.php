<?php
/**
 * Database Configuration File
 * This file contains database connection settings for XAMPP/MySQL
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'attendance_system');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Error Reporting
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Create database connection
try {
    $conn = new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASSWORD,
        DB_NAME,
        DB_PORT
    );

    // Check connection
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Set charset to utf8mb4
    $conn->set_charset(DB_CHARSET);

    // Set timezone
    date_default_timezone_set('UTC');

} catch (Exception $e) {
    die('Database connection error: ' . $e->getMessage());
}
?>