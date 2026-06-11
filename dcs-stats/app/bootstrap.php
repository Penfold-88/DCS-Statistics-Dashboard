<?php
/**
 * V1.3 application bootstrap.
 *
 * This is the first framework layer for the dashboard. Keep it small while
 * legacy pages are migrated into controllers and views.
 */

if (!defined('DCS_ROOT_PATH')) {
    define('DCS_ROOT_PATH', dirname(__DIR__));
}

if (!defined('DCS_APP_PATH')) {
    define('DCS_APP_PATH', __DIR__);
}

spl_autoload_register(function ($class) {
    $prefix = 'DcsStats\\';
    $prefixLength = strlen($prefix);

    if (strncmp($class, $prefix, $prefixLength) !== 0) {
        return;
    }

    $relativeClass = substr($class, $prefixLength);
    $file = DCS_APP_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

require_once DCS_ROOT_PATH . '/config_path.php';

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$isAdminRequest = strpos($scriptName, '/site-config/') !== false
    || substr($scriptName, -strlen('/site-config')) === '/site-config';

if (!$isAdminRequest && !defined('DCS_SKIP_SESSION') && session_status() === PHP_SESSION_NONE) {
    $sessionDir = DCS_ROOT_PATH . '/site-config/data/php-sessions';

    if (!is_dir($sessionDir)) {
        @mkdir($sessionDir, 0700, true);
    }

    if (is_dir($sessionDir) && is_writable($sessionDir)) {
        @chmod($sessionDir, 0700);
        session_save_path($sessionDir);
    }

    session_start();
}
