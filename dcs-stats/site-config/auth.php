<?php
/**
 * Authentication System for Admin Panel
 * Handles login, logout, session management, and security
 */

// Define admin panel constant
define('ADMIN_PANEL', true);

// Include configuration
require_once __DIR__ . '/config.php';

function isAdminRequestHttps() {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    $forwardedProto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    if ($forwardedProto === 'https') {
        return true;
    }

    $forwardedSsl = strtolower((string)($_SERVER['HTTP_X_FORWARDED_SSL'] ?? ''));
    return $forwardedSsl === 'on' || $forwardedSsl === '1';
}

// Start session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

if (ENFORCE_HTTPS || isAdminRequestHttps()) {
    ini_set('session.cookie_secure', 1);
}

session_name(ADMIN_SESSION_NAME);
session_start();

/**
 * Initialize admin data files if they don't exist
 */
function initializeAdminData() {
    // Show setup progress if first time
    $isFirstTime = !is_dir(ADMIN_DATA_DIR);
    
    if ($isFirstTime && php_sapi_name() !== 'cli') {
        // Send setup page to browser
        if (ob_get_level() > 0) {
            ob_end_clean(); // Clear any previous output
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Setting Up Admin Panel</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: #1a1a1a;
                    color: #fff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                }
                .setup-container {
                    background: #2d2d2d;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.5);
                    text-align: center;
                    max-width: 500px;
                }
                h1 {
                    color: #4CAF50;
                    margin-bottom: 20px;
                }
                .spinner {
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid #4CAF50;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    animation: spin 1s linear infinite;
                    margin: 20px auto;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .status {
                    margin: 15px 0;
                    padding: 10px;
                    background: #1a1a1a;
                    border-radius: 5px;
                }
                .success { color: #4CAF50; }
                .error { color: #f44336; }
            </style>
            <meta http-equiv="refresh" content="3">
        </head>
        <body>
            <div class="setup-container">
                <h1>Setting Up Your Environment</h1>
                <div class="spinner"></div>
                <p>Please wait while we configure the admin panel...</p>
                <div class="status">Creating necessary directories and files...</div>
            </div>
        </body>
        </html>
        <?php
        flush();
        // Continue with setup but exit after showing the page
        sleep(1); // Give a moment for the page to display
    }
    
    // Create data directory if it doesn't exist
    if (!is_dir(ADMIN_DATA_DIR)) {
        $created = @mkdir(ADMIN_DATA_DIR, 0700, true);
        if (!$created) {
            // Try alternative approach - create parent directories first
            $parent = dirname(ADMIN_DATA_DIR);
            if (!is_dir($parent)) {
                @mkdir($parent, 0755, true);
            }
            @mkdir(ADMIN_DATA_DIR, 0700, true);
        }
    }
    @chmod(ADMIN_DATA_DIR, 0700);
    
    // If directory still doesn't exist or isn't writable, try alternative location
    if (!is_dir(ADMIN_DATA_DIR) || !is_writable(ADMIN_DATA_DIR)) {
        // Try to use system temp directory as fallback
        $tempDir = sys_get_temp_dir() . '/dcs_admin_data';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0700, true);
        }
        @chmod($tempDir, 0700);
        
        // If temp directory works, update the constant
        if (is_dir($tempDir) && is_writable($tempDir)) {
            // Override the data directory path
            if (!defined('ADMIN_DATA_DIR_OVERRIDE')) {
                define('ADMIN_DATA_DIR_OVERRIDE', $tempDir . '/');
                // Update file paths
                define('ADMIN_USERS_FILE_OVERRIDE', ADMIN_DATA_DIR_OVERRIDE . 'users.json');
                define('ADMIN_LOGS_FILE_OVERRIDE', ADMIN_DATA_DIR_OVERRIDE . 'logs.json');
                define('ADMIN_BANS_FILE_OVERRIDE', ADMIN_DATA_DIR_OVERRIDE . 'bans.json');
                define('ADMIN_SESSIONS_FILE_OVERRIDE', ADMIN_DATA_DIR_OVERRIDE . 'sessions.json');
            }
        }
    }
    
    // The installer must create the first real admin user.
    $usersFile = defined('ADMIN_USERS_FILE_OVERRIDE') ? ADMIN_USERS_FILE_OVERRIDE : ADMIN_USERS_FILE;
    if (file_exists($usersFile)) {
        @chmod($usersFile, 0600);
    }
    
    // Initialize other data files
    $dataTypes = ['logs', 'bans', 'sessions'];
    
    foreach ($dataTypes as $type) {
        $file = getDataFilePath($type);
        if (!file_exists($file)) {
            @file_put_contents($file, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        }
        @chmod($file, 0600);
    }
}

function adminDataNeedsInitialization() {
    if (!is_dir(ADMIN_DATA_DIR)) {
        return true;
    }

    foreach (['logs', 'bans', 'sessions'] as $type) {
        if (!file_exists(getDataFilePath($type))) {
            return true;
        }
    }

    return false;
}

/**
 * Get the correct file path (with override support)
 */
function getDataFilePath($type) {
    $overrideConstant = strtoupper('ADMIN_' . $type . '_FILE_OVERRIDE');
    if (defined($overrideConstant)) {
        return constant($overrideConstant);
    }
    return constant('ADMIN_' . strtoupper($type) . '_FILE');
}

/**
 * Get all admin users
 */
function getAdminUsers() {
    $usersFile = getDataFilePath('users');
    if (!file_exists($usersFile)) {
        return [];
    }
    
    $users = json_decode(file_get_contents($usersFile), true);
    return $users ?: [];
}

/**
 * Save admin users
 */
function saveAdminUsers($users) {
    $usersFile = getDataFilePath('users');
    $result = @file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    @chmod($usersFile, 0600);
    return $result !== false;
}

/**
 * Find admin user by username or email
 */
function findAdminUser($username) {
    $users = getAdminUsers();
    
    foreach ($users as $user) {
        if ($user['username'] === $username || $user['email'] === $username) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Update admin user
 */
function updateAdminUser($userId, $updates) {
    $users = getAdminUsers();
    
    foreach ($users as &$user) {
        if ($user['id'] == $userId) {
            $user = array_merge($user, $updates);
            saveAdminUsers($users);
            return true;
        }
    }
    
    return false;
}

/**
 * Check if user is locked due to failed attempts
 */
function isUserLocked($user) {
    if (!$user['locked_until']) {
        return false;
    }
    
    $lockedUntil = strtotime($user['locked_until']);
    if ($lockedUntil > time()) {
        return true;
    }
    
    // Unlock user if lock period has expired
    updateAdminUser($user['id'], [
        'failed_attempts' => 0,
        'locked_until' => null
    ]);
    
    return false;
}

/**
 * Log admin activity
 */
function logAdminActivity($action, $adminId = null, $targetType = null, $targetId = null, $details = null) {
    if (!LOG_ADMIN_ACTIONS) {
        return;
    }
    
    $logsFile = getDataFilePath('logs');
    $logs = json_decode(@file_get_contents($logsFile), true) ?: [];
    
    $log = [
        'id' => count($logs) + 1,
        'admin_id' => $adminId,
        'action' => $action,
        'target_type' => $targetType,
        'target_id' => $targetId,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'created_at' => date(DATE_FORMAT)
    ];
    
    $logs[] = $log;
    
    // Keep only recent logs based on retention policy
    $cutoffDate = date(DATE_FORMAT, strtotime('-' . LOG_RETENTION_DAYS . ' days'));
    $logs = array_filter($logs, function($log) use ($cutoffDate) {
        return $log['created_at'] > $cutoffDate;
    });
    
    @file_put_contents($logsFile, json_encode(array_values($logs), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    @chmod($logsFile, 0600);
}

/**
 * Attempt to login
 */
function attemptLogin($username, $password, $remember = false) {
    $user = findAdminUser($username);
    
    if (!$user) {
        logAdminActivity('LOGIN_FAILED', null, 'username', $username, ['reason' => 'User not found']);
        return ['success' => false, 'error' => ERROR_MESSAGES['invalid_credentials']];
    }
    
    // Check if user is locked
    if (isUserLocked($user)) {
        logAdminActivity('LOGIN_FAILED', $user['id'], 'user', $user['username'], ['reason' => 'Account locked']);
        return ['success' => false, 'error' => ERROR_MESSAGES['account_locked']];
    }
    
    // Check if user is active
    if (!$user['is_active']) {
        logAdminActivity('LOGIN_FAILED', $user['id'], 'user', $user['username'], ['reason' => 'Account inactive']);
        return ['success' => false, 'error' => ERROR_MESSAGES['invalid_credentials']];
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        // Increment failed attempts
        $failedAttempts = $user['failed_attempts'] + 1;
        $updates = ['failed_attempts' => $failedAttempts];
        
        // Lock account if too many failed attempts
        if ($failedAttempts >= LOGIN_THROTTLE_ATTEMPTS) {
            $updates['locked_until'] = date(DATE_FORMAT, time() + LOGIN_THROTTLE_WINDOW);
        }
        
        updateAdminUser($user['id'], $updates);
        logAdminActivity('LOGIN_FAILED', $user['id'], 'user', $user['username'], ['reason' => 'Invalid password']);
        
        return ['success' => false, 'error' => ERROR_MESSAGES['invalid_credentials']];
    }
    
    // Successful login
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_login_time'] = time();
    $_SESSION['admin_last_activity'] = time();
    
    // Generate CSRF token
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    
    // Update user login info
    updateAdminUser($user['id'], [
        'last_login' => date(DATE_FORMAT),
        'last_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'failed_attempts' => 0,
        'locked_until' => null
    ]);
    
    // Handle remember me
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        
        // Save session to file
        $sessionsFile = getDataFilePath('sessions');
        $sessions = json_decode(@file_get_contents($sessionsFile), true) ?: [];
        $sessions[] = [
            'id' => count($sessions) + 1,
            'admin_id' => $user['id'],
            'token_hash' => $tokenHash,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'expires_at' => date(DATE_FORMAT, time() + ADMIN_COOKIE_LIFETIME),
            'created_at' => date(DATE_FORMAT)
        ];
        $sessionsFile = getDataFilePath('sessions');
        @file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod($sessionsFile, 0600);
        
        // Set cookie
        setcookie(
            ADMIN_COOKIE_NAME,
            $user['id'] . ':' . $token,
            time() + ADMIN_COOKIE_LIFETIME,
            '/',
            '',
            ENFORCE_HTTPS,
            true
        );
    }
    
    logAdminActivity('LOGIN', $user['id'], 'user', $user['username']);
    
    return ['success' => true, 'user' => $user];
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    // Check session
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
        // Check session timeout
        if (time() - $_SESSION['admin_last_activity'] > SESSION_LIFETIME) {
            logout();
            return false;
        }
        
        $_SESSION['admin_last_activity'] = time();
        return true;
    }
    
    // Check remember me cookie
    if (isset($_COOKIE[ADMIN_COOKIE_NAME])) {
        list($userId, $token) = explode(':', $_COOKIE[ADMIN_COOKIE_NAME], 2);
        $tokenHash = hash('sha256', $token);
        
        // Find valid session
        $sessionsFile = getDataFilePath('sessions');
        $sessions = json_decode(@file_get_contents($sessionsFile), true) ?: [];
        foreach ($sessions as $session) {
            if ($session['admin_id'] == $userId && 
                $session['token_hash'] === $tokenHash &&
                strtotime($session['expires_at']) > time()) {
                
                // Restore session
                $user = null;
                $users = getAdminUsers();
                foreach ($users as $u) {
                    if ($u['id'] == $userId) {
                        $user = $u;
                        break;
                    }
                }
                
                if ($user && $user['is_active']) {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_role'] = $user['role'];
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_login_time'] = time();
                    $_SESSION['admin_last_activity'] = time();
                    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
                    
                    return true;
                }
            }
        }
        
        // Invalid cookie, remove it
        setcookie(ADMIN_COOKIE_NAME, '', time() - 3600, '/');
    }
    
    return false;
}

/**
 * Logout admin
 */
function logout() {
    if (isset($_SESSION['admin_id'])) {
        logAdminActivity('LOGOUT', $_SESSION['admin_id']);
    }
    
    // Clear session
    $_SESSION = [];
    session_destroy();
    
    // Clear remember me cookie
    if (isset($_COOKIE[ADMIN_COOKIE_NAME])) {
        setcookie(ADMIN_COOKIE_NAME, '', time() - 3600, '/');
        
        // Remove session from file
        list($userId, $token) = explode(':', $_COOKIE[ADMIN_COOKIE_NAME], 2);
        $tokenHash = hash('sha256', $token);
        
        $sessionsFile = getDataFilePath('sessions');
        $sessions = json_decode(@file_get_contents($sessionsFile), true) ?: [];
        $sessions = array_filter($sessions, function($session) use ($userId, $tokenHash) {
            return !($session['admin_id'] == $userId && $session['token_hash'] === $tokenHash);
        });
        $sessionsFile = getDataFilePath('sessions');
        @file_put_contents($sessionsFile, json_encode(array_values($sessions), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod($sessionsFile, 0600);
    }
}

/**
 * Check if admin has permission
 */
function hasPermission($permission) {
    if (!isAdminLoggedIn()) {
        return false;
    }
    
    $role = $_SESSION['admin_role'] ?? 0;
    
    // Air Boss has all permissions
    if ($role === ROLE_AIR_BOSS) {
        return true;
    }
    
    // For LSO role, check custom permissions if they exist
    if ($role === ROLE_LSO) {
        // Load custom LSO permissions
        $customPermFile = __DIR__ . '/data/lso_permissions.json';
        if (!file_exists($customPermFile)) {
            // Try alternative locations
            $customPermFile = __DIR__ . '/../lso_permissions.json';
            if (!file_exists($customPermFile)) {
                $customPermFile = sys_get_temp_dir() . '/dcs_stats/lso_permissions.json';
            }
        }
        
        if (file_exists($customPermFile)) {
            $customPerms = json_decode(file_get_contents($customPermFile), true);
            if ($customPerms && isset($customPerms[$permission])) {
                return $customPerms[$permission]['enabled'] ?? false;
            }
        }
    }
    
    // Fall back to default role permissions
    $permissions = ROLE_PERMISSIONS[$role] ?? [];
    return in_array($permission, $permissions);
}

/**
 * Build an admin-panel URL that works from both /site-config pages and nested /site-config/api pages.
 */
function adminPanelUrl($path = '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $adminPos = strpos($scriptName, '/site-config');

    if ($adminPos !== false) {
        $basePath = substr($scriptName, 0, $adminPos + strlen('/site-config'));
    } else {
        $basePath = rtrim(dirname($scriptName), '/\\');
    }

    return rtrim($basePath, '/') . '/' . ltrim($path, '/');
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . adminPanelUrl('login.php'));
        exit;
    }
}

/**
 * Require specific permission
 */
function requirePermission($permission) {
    requireAdmin();
    
    if (!hasPermission($permission)) {
        http_response_code(403);
        die(ERROR_MESSAGES['access_denied']);
    }
}

/**
 * Get CSRF token
 */
function getCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Read a CSRF token from common request locations.
 */
function getRequestCSRFToken($jsonInput = null) {
    if (isset($_POST['csrf_token'])) {
        return $_POST['csrf_token'];
    }

    $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_ADMIN_CSRF_TOKEN'] ?? '';
    if ($headerToken !== '') {
        return $headerToken;
    }

    if (is_array($jsonInput) && isset($jsonInput['csrf_token'])) {
        return $jsonInput['csrf_token'];
    }

    return '';
}

/**
 * Require a valid CSRF token for state-changing admin requests.
 */
function requireCSRFToken($jsonInput = null) {
    if (!verifyCSRFToken(getRequestCSRFToken($jsonInput))) {
        http_response_code(403);
        die(ERROR_MESSAGES['csrf_invalid']);
    }
}

/**
 * Generate CSRF token field
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(getCSRFToken()) . '">';
}

// Initialize admin data only when the data directory or supporting files are missing.
if (adminDataNeedsInitialization()) {
    initializeAdminData();
}
