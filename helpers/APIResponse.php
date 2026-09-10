<?php
/**
 * API Response Handler
 */

class APIResponse {
    
    /**
     * Send success response
     */
    public static function success($data = null, $message = 'Success', $code = 200) {
        http_response_code($code);
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Send error response
     */
    public static function error($message = 'Error', $code = 400, $errors = null) {
        http_response_code($code);
        return [
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Send JSON response
     */
    public static function json($response) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    /**
     * Handle validation errors
     */
    public static function validationError($errors) {
        return self::error('Validation failed', 422, $errors);
    }

    /**
     * Handle not found
     */
    public static function notFound($message = 'Resource not found') {
        return self::error($message, 404);
    }

    /**
     * Handle unauthorized
     */
    public static function unauthorized($message = 'Unauthorized') {
        return self::error($message, 401);
    }

    /**
     * Handle forbidden
     */
    public static function forbidden($message = 'Forbidden') {
        return self::error($message, 403);
    }
}

/**
 * Validation Helper
 */
class Validator {
    private $errors = [];

    /**
     * Required field validation
     */
    public function required($field, $value) {
        if (empty($value)) {
            $this->errors[$field] = ucfirst($field) . ' is required';
        }
        return $this;
    }

    /**
     * Email validation
     */
    public function email($field, $value) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Invalid email format';
        }
        return $this;
    }

    /**
     * Minimum length validation
     */
    public function minLength($field, $value, $length) {
        if (!empty($value) && strlen($value) < $length) {
            $this->errors[$field] = ucfirst($field) . ' must be at least ' . $length . ' characters';
        }
        return $this;
    }

    /**
     * Maximum length validation
     */
    public function maxLength($field, $value, $length) {
        if (!empty($value) && strlen($value) > $length) {
            $this->errors[$field] = ucfirst($field) . ' must not exceed ' . $length . ' characters';
        }
        return $this;
    }

    /**
     * Numeric validation
     */
    public function numeric($field, $value) {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = ucfirst($field) . ' must be numeric';
        }
        return $this;
    }

    /**
     * Date validation
     */
    public function date($field, $value, $format = 'Y-m-d') {
        if (!empty($value)) {
            $date = DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->errors[$field] = ucfirst($field) . ' must be in ' . $format . ' format';
            }
        }
        return $this;
    }

    /**
     * Custom validation
     */
    public function custom($field, $condition, $message) {
        if (!$condition) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * Get all errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Check if validation passed
     */
    public function passed() {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function failed() {
        return !$this->passed();
    }
}
?>