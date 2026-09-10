<?php
/**
 * Helper functions for the attendance system
 */

/**
 * Sanitize input
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone
 */
function validatePhone($phone) {
    return preg_match('/^[0-9\s\-\+\(\)]{7,}$/', $phone);
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd M Y') {
    if (!$date) return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'd M Y, H:i A') {
    if (!$datetime) return 'N/A';
    return date($format, strtotime($datetime));
}

/**
 * Format time for display
 */
function formatTime($time, $format = 'H:i A') {
    if (!$time) return 'N/A';
    return date($format, strtotime($time));
}

/**
 * Check if date is today
 */
function isToday($date) {
    return date('Y-m-d', strtotime($date)) === date('Y-m-d');
}

/**
 * Get time difference in minutes
 */
function getTimeDifferenceInMinutes($start_time, $end_time) {
    $start = strtotime($start_time);
    $end = strtotime($end_time);
    return ($end - $start) / 60;
}

/**
 * Get status badge color
 */
function getStatusBadgeColor($status) {
    $colors = [
        'present' => 'success',
        'absent' => 'danger',
        'late' => 'warning',
        'excused' => 'info',
        'active' => 'success',
        'inactive' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}

/**
 * Get attendance status badge HTML
 */
function getStatusBadge($status) {
    $color = getStatusBadgeColor($status);
    return '<span class="badge badge-' . $color . '">' . ucfirst($status) . '</span>';
}

/**
 * Format percentage
 */
function formatPercentage($percentage) {
    return number_format($percentage, 2) . '%';
}

/**
 * Pagination helper
 */
function getPagination($total_items, $items_per_page, $current_page) {
    $total_pages = ceil($total_items / $items_per_page);
    $offset = ($current_page - 1) * $items_per_page;

    return [
        'total_items' => $total_items,
        'items_per_page' => $items_per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

/**
 * Get date range
 */
function getDateRange($type = 'monthly') {
    $today = date('Y-m-d');
    switch ($type) {
        case 'daily':
            return ['start' => $today, 'end' => $today];
        case 'weekly':
            return [
                'start' => date('Y-m-d', strtotime('monday this week')),
                'end' => date('Y-m-d', strtotime('sunday this week'))
            ];
        case 'monthly':
            return [
                'start' => date('Y-m-01'),
                'end' => date('Y-m-t')
            ];
        default:
            return ['start' => $today, 'end' => $today];
    }
}

/**
 * Calculate attendance percentage color
 */
function getPercentageColor($percentage) {
    if ($percentage >= 75) return 'success';
    if ($percentage >= 50) return 'warning';
    return 'danger';
}

/**
 * Log user action
 */
function logAction($db, $user_id, $action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $old_values_json = $old_values ? json_encode($old_values) : null;
        $new_values_json = $new_values ? json_encode($new_values) : null;

        $query = 'INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

        $db->prepare($query);
        $db->bind('i', $user_id);
        $db->bind('s', $action);
        $db->bind('s', $table_name);
        $db->bind('i', $record_id);
        $db->bind('s', $old_values_json);
        $db->bind('s', $new_values_json);
        $db->bind('s', $ip_address);
        $db->bind('s', $user_agent);
        $db->execute();
    } catch (Exception $e) {
        // Log silently
    }
}

/**
 * Get system setting
 */
function getSetting($db, $setting_key, $default = null) {
    try {
        $db->prepare('SELECT setting_value FROM system_settings WHERE setting_key = ?');
        $db->bind('s', $setting_key);
        $db->execute();
        $result = $db->getSingleResult();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Set system setting
 */
function setSetting($db, $setting_key, $setting_value, $setting_type = 'string') {
    try {
        $db->prepare('INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                     VALUES (?, ?, ?) 
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_type = VALUES(setting_type)');
        $db->bind('s', $setting_key);
        $db->bind('s', $setting_value);
        $db->bind('s', $setting_type);
        $db->execute();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: ' . $url);
    exit;
}

/**
 * Get and clear message
 */
function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Display alert
 */
function displayAlert($message, $type = 'info') {
    $alert_type = 'alert-' . $type;
    echo '<div class="alert ' . $alert_type . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($message);
    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
    echo '<span aria-hidden="true">&times;</span></button></div>';
}
?>