<?php
/**
 * Security Functions for DCS Statistics Website Uploader
 * Contains common security utilities used across the application
 */

/**
 * Rate limiting function to prevent API abuse
 * Uses session-based tracking with configurable limits
 * 
 * @param int $limit Maximum requests per window (default: 60)
 * @param int $window Time window in seconds (default: 60)
 * @return bool True if request is allowed, false if rate limited
 */
function checkRateLimit($limit = 60, $window = 60) {
    // Initialize session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $current_time = time();
    $requests = $_SESSION['api_requests'] ?? [];
    
    // Clean old requests outside the window
    $requests = array_filter($requests, function($timestamp) use ($current_time, $window) {
        return ($current_time - $timestamp) < $window;
    });
    
    // Check if limit exceeded
    if (count($requests) >= $limit) {
        http_response_code(429);
        header('Retry-After: ' . $window);
        echo json_encode([
            "error" => "Rate limit exceeded. Please try again later.",
            "retry_after" => $window
        ]);
        return false;
    }
    
    // Add current request timestamp
    $requests[] = $current_time;
    $_SESSION['api_requests'] = $requests;
    
    return true;
}

/**
 * Validate and sanitize JSON line data
 * Prevents JSON injection and validates required fields
 * 
 * @param string $line JSON line to validate
 * @param array $required_fields Required field names
 * @return array|null Validated data or null if invalid
 */
function validateJsonLine($line, $required_fields = []) {
    if (empty(trim($line))) {
        return null;
    }
    
    $data = json_decode(trim($line), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }
    
    if (!is_array($data)) {
        return null;
    }
    
    // Check required fields
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            return null;
        }
    }
    
    return $data;
}

/**
 * Validate and canonicalize file paths
 * Prevents path traversal attacks
 * 
 * @param string $path Path to validate
 * @param string $base_dir Base directory that path must be within
 * @return string|false Canonical path or false if invalid
 */
function validatePath($path, $base_dir) {
    $real_base = realpath($base_dir);
    $real_path = realpath($path);
    
    if ($real_base === false || $real_path === false) {
        return false;
    }
    
    // Check if path is within base directory
    if (strpos($real_path, $real_base) !== 0) {
        return false;
    }
    
    return $real_path;
}

/**
 * Validate input data with configurable rules
 * 
 * @param string $input Input to validate
 * @param array $rules Validation rules
 * @return string|false Sanitized input or false if invalid
 */
function validateInput($input, $rules = []) {
    $input = trim($input);
    
    // Length check
    if (isset($rules['max_length']) && strlen($input) > $rules['max_length']) {
        return false;
    }
    
    if (isset($rules['min_length']) && strlen($input) < $rules['min_length']) {
        return false;
    }
    
    // Pattern check
    if (isset($rules['pattern']) && !preg_match($rules['pattern'], $input)) {
        return false;
    }
    
    // Type-specific validation
    if (isset($rules['type'])) {
        switch ($rules['type']) {
            case 'player_name':
                // Allow alphanumeric, spaces, underscores, hyphens, dots, and common special chars
                if (!preg_match('/^[a-zA-Z0-9_\-\s\.\[\]|]+$/u', $input)) {
                    return false;
                }
                break;
            case 'numeric':
                if (!is_numeric($input)) {
                    return false;
                }
                break;
        }
    }
    
    return $input;
}

/**
 * Log security events for monitoring
 * 
 * @param string $event Event type
 * @param string $details Event details
 * @param string $ip IP address (optional)
 */
function logSecurityEvent($event, $details, $ip = null) {
    $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $timestamp = date('Y-m-d H:i:s');
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_entry = sprintf(
        "[%s] %s - %s - IP: %s - UA: %s\n",
        $timestamp,
        $event,
        $details,
        $ip,
        substr($user_agent, 0, 100)
    );
    
    error_log($log_entry, 3, __DIR__ . '/security.log');
}
?>