<?php
/**
 * Admin Panel Configuration
 * Core configuration for the DCS Statistics Admin Panel
 */

// Prevent direct access
if (!defined('ADMIN_PANEL')) {
    die('Direct access not permitted');
}

// Admin panel settings
define('ADMIN_PANEL_VERSION', '1.0.0');
define('ADMIN_SESSION_NAME', 'dcs_admin_session');
define('ADMIN_COOKIE_NAME', 'dcs_admin_remember');
define('ADMIN_COOKIE_LIFETIME', 30 * 24 * 60 * 60); // 30 days

// GitHub repository details for updater
define('GITHUB_OWNER', ''); // e.g. 'YourGitHubUsername'
define('GITHUB_REPO', '');  // e.g. 'YourRepositoryName'

// Security settings
define('LOGIN_THROTTLE_ATTEMPTS', 5); // Max login attempts
define('LOGIN_THROTTLE_WINDOW', 900); // 15 minutes in seconds
define('CSRF_TOKEN_NAME', 'admin_csrf_token');
define('SESSION_LIFETIME', 3600); // 1 hour
define('ENFORCE_HTTPS', false); // Set to true in production

// Admin roles - Navy Carrier Theme (2 roles)
define('ROLE_AIR_BOSS', 2);        // Highest authority on flight deck operations
define('ROLE_LSO', 1);             // Landing Signal Officer - assists pilots

// Role names for display
const ROLE_NAMES = [
    ROLE_AIR_BOSS => 'Air Boss',
    ROLE_LSO => 'LSO'
];

// Role permissions
const ROLE_PERMISSIONS = [
    ROLE_AIR_BOSS => [        // Full control of flight operations
        'view_dashboard',
        'export_data',
        'view_logs',
        'manage_admins',
        'change_settings'
    ],
    ROLE_LSO => [             // Monitors pilot statistics and landings
        'view_dashboard',
        'export_data',
        'view_logs'
    ]
];

// Database configuration (optional - can use local file storage)
define('USE_DATABASE', false); // Set to true to use MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'dcs_stats_admin');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PREFIX', 'dcs_');

// File-based storage (when not using database)
define('ADMIN_DATA_DIR', __DIR__ . '/data/');
define('ADMIN_USERS_FILE', ADMIN_DATA_DIR . 'users.json');
define('ADMIN_LOGS_FILE', ADMIN_DATA_DIR . 'logs.json');
define('ADMIN_BANS_FILE', ADMIN_DATA_DIR . 'bans.json');
define('ADMIN_SESSIONS_FILE', ADMIN_DATA_DIR . 'sessions.json');

// Logging settings
define('LOG_ADMIN_ACTIONS', true);
define('LOG_RETENTION_DAYS', 90);

// Export settings
define('EXPORT_MAX_RECORDS', 10000);
define('EXPORT_FORMATS', ['csv', 'json']);

// Pagination
define('RECORDS_PER_PAGE', 25);

// Date format
define('DATE_FORMAT', 'Y-m-d H:i:s');

// Default admin user (only used for initial setup)
// IMPORTANT: These are example values only - must be changed on first login!
define('DEFAULT_ADMIN_USERNAME', 'admin');
define('DEFAULT_ADMIN_EMAIL', 'admin@example.com');
define('DEFAULT_ADMIN_PASSWORD', ''); // Must be set during installation

// Activity log action types
const LOG_ACTIONS = [
    'LOGIN' => 'Logged In',
    'LOGOUT' => 'Logged Out',
    'LOGIN_FAILED' => 'Failed Login Attempt',
    'PLAYER_VIEW' => 'Viewed Player Record',
    'PLAYER_EDIT' => 'Updated Player Record',
    'PLAYER_BAN' => 'Banned Player',
    'PLAYER_UNBAN' => 'Unbanned Player',
    'DATA_EXPORT' => 'Exported Data',
    'ADMIN_CREATE' => 'Created Admin User',
    'ADMIN_EDIT' => 'Updated Admin User',
    'ADMIN_DELETE' => 'Deleted Admin User',
    'SETTINGS_CHANGE' => 'Changed Settings'
];

// Error messages
const ERROR_MESSAGES = [
    'invalid_credentials' => 'Invalid username or password',
    'account_locked' => 'Account locked due to too many failed attempts',
    'session_expired' => 'Your session has expired. Please login again',
    'access_denied' => 'You do not have permission to access this resource',
    'csrf_invalid' => 'Security token invalid. Please refresh and try again'
];

// Success messages
const SUCCESS_MESSAGES = [
    'login_success' => 'Successfully logged in',
    'logout_success' => 'Successfully logged out',
    'player_updated' => 'Player information updated successfully',
    'player_banned' => 'Player has been banned',
    'player_unbanned' => 'Player has been unbanned',
    'admin_created' => 'Admin user created successfully',
    'settings_saved' => 'Settings saved successfully'
];