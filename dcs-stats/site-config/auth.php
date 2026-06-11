<?php
/**
 * Authentication System for Admin Panel
 * Handles login, logout, session management, and security
 */

// Define admin panel constant
define('ADMIN_PANEL', true);

if (!defined('DCS_SKIP_SESSION')) {
    define('DCS_SKIP_SESSION', true);
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

// Include configuration
require_once __DIR__ . '/config.php';

function sendAdminSecurityHeaders() {
    \DcsStats\Core\AdminEnvironment::sendSecurityHeaders();
}

sendAdminSecurityHeaders();

function isAdminRequestHttps() {
    return \DcsStats\Core\AdminEnvironment::requestIsHttps();
}

function prepareAdminSessionStorage() {
    \DcsStats\Core\AdminEnvironment::prepareSessionStorage();
}

\DcsStats\Core\AdminEnvironment::startSession();

/**
 * Initialize admin data files if they don't exist
 */
function initializeAdminData() {
    \DcsStats\Core\AdminDataInitializer::initialize();
}

function adminDataNeedsInitialization() {
    return \DcsStats\Core\AdminDataInitializer::needsInitialization();
}

/**
 * Get the correct file path (with override support)
 */
function getDataFilePath($type) {
    return \DcsStats\Core\AdminEnvironment::dataFilePath((string)$type);
}

/**
 * Get all admin users
 */
function getAdminUsers() {
    return \DcsStats\Core\AdminUsers::all();
}

/**
 * Save admin users
 */
function saveAdminUsers($users) {
    return \DcsStats\Core\AdminUsers::save(is_array($users) ? $users : []);
}

/**
 * Find admin user by username or email
 */
function findAdminUser($username) {
    return \DcsStats\Core\AdminUsers::findByUsernameOrEmail((string)$username);
}

/**
 * Update admin user
 */
function updateAdminUser($userId, $updates) {
    return \DcsStats\Core\AdminUsers::update((int)$userId, is_array($updates) ? $updates : []);
}

/**
 * Check if user is locked due to failed attempts
 */
function isUserLocked($user) {
    return \DcsStats\Core\AdminUsers::unlockIfExpired(is_array($user) ? $user : []);
}

/**
 * Keep activity logs bounded so admin pages do not load unbounded JSON.
 */
function pruneAdminLogs($logs, $maxLogs = null) {
    return \DcsStats\Core\AdminAuditLog::prune($logs, $maxLogs === null ? null : (int)$maxLogs);
}

/**
 * Log admin activity
 */
function logAdminActivity($action, $adminId = null, $targetType = null, $targetId = null, $details = null) {
    \DcsStats\Core\AdminAuditLog::write($action, $adminId, $targetType, $targetId, $details);
}

/**
 * Attempt to login
 */
function attemptLogin($username, $password, $remember = false) {
    return \DcsStats\Core\AdminSessionAuth::attemptLogin($username, $password, (bool)$remember);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return \DcsStats\Core\AdminSessionAuth::isLoggedIn();
}

/**
 * Logout admin
 */
function logout() {
    \DcsStats\Core\AdminSessionAuth::logout();
}

/**
 * Check if admin has permission
 */
function hasPermission($permission) {
    return \DcsStats\Core\AdminSessionAuth::hasPermission($permission);
}

/**
 * Build an admin-panel URL that works from both /site-config pages and nested /site-config/api pages.
 */
function adminPanelUrl($path = '') {
    return \DcsStats\Core\AdminSessionAuth::panelUrl((string)$path);
}

/**
 * Require admin login
 */
function requireAdmin() {
    \DcsStats\Core\AdminSessionAuth::requireLogin();
}

/**
 * Require specific permission
 */
function requirePermission($permission) {
    \DcsStats\Core\AdminSessionAuth::requirePermission($permission);
}

/**
 * Get CSRF token
 */
function getCSRFToken() {
    return \DcsStats\Core\Csrf::token();
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return \DcsStats\Core\Csrf::verify((string)$token);
}

/**
 * Read a CSRF token from common request locations.
 */
function getRequestCSRFToken($jsonInput = null) {
    return \DcsStats\Core\Csrf::requestToken(is_array($jsonInput) ? $jsonInput : null);
}

/**
 * Require a valid CSRF token for state-changing admin requests.
 */
function requireCSRFToken($jsonInput = null) {
    \DcsStats\Core\Csrf::requireValid(is_array($jsonInput) ? $jsonInput : null);
}

/**
 * Generate CSRF token field
 */
function csrfField() {
    return \DcsStats\Core\Csrf::field();
}

// Initialize admin data only when the data directory or supporting files are missing.
if (adminDataNeedsInitialization()) {
    initializeAdminData();
}
