<?php
/**
 * Session Configuration and Management
 */

// Secure session settings
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
    'secure' => true, // HTTPS only
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout check
if (isset($_SESSION['CREATED'])) {
    $session_lifetime = 1800; // 30 minutes
    if (time() - $_SESSION['CREATED'] > $session_lifetime) {
        session_destroy();
        header('Location: ' . APP_URL . 'pages/login.php?timeout=1');
        exit();
    }
} else {
    $_SESSION['CREATED'] = time();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check user role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Check multiple roles
 */
function hasAnyRole($roles) {
    if (!isset($_SESSION['role'])) return false;
    return in_array($_SESSION['role'], $roles);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . 'pages/login.php');
        exit();
    }
}

/**
 * Redirect if doesn't have required role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ' . APP_URL . 'pages/unauthorized.php');
        exit();
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Logout user
 */
function logout() {
    $_SESSION = [];
    session_destroy();
}
?>