<?php
/**
 * Admin Panel Helper Functions
 */

require_once __DIR__ . '/demo_helpers.php';

// Ensure admin panel constant is defined
if (!defined('ADMIN_PANEL')) {
    die('Direct access not permitted');
}

function adminDataService() {
    static $service = null;
    if ($service === null) {
        $service = new \DcsStats\Services\Admin\AdminDataService();
    }

    return $service;
}

/**
 * Get current admin user
 */
function getCurrentAdmin() {
    return \DcsStats\Core\AdminPanel::currentAdmin();
}

/**
 * Format date for display
 */
function formatDate($date, $format = null) {
    return \DcsStats\Core\AdminPanel::formatDate($date, $format);
}

/**
 * Normalize older and newer admin log formats into one shape.
 */
function normalizeAdminLog($log) {
    return \DcsStats\Core\AdminPanel::normalizeLog($log);
}

function adminLogTimestamp($log) {
    return \DcsStats\Core\AdminPanel::logTimestamp(is_array($log) ? $log : []);
}

/**
 * Log admin action
 */
function logAdminAction($action, $details = []) {
    \DcsStats\Core\AdminPanel::logAction($action, is_array($details) ? $details : []);
}

/**
 * Get player data from API
 */
function getPlayers($search = null, $limit = null, $offset = 0) {
    return adminDataService()->players($search, $limit, (int)$offset);
}

/**
 * Get player statistics
 */
function getPlayerStats($ucid) {
    return adminDataService()->playerStats($ucid);
}

/**
 * Get player bans
 */
function getPlayerBans($activeOnly = true) {
    return adminDataService()->playerBans((bool)$activeOnly);
}

/**
 * Check if player is banned
 */
function isPlayerBanned($ucid) {
    return adminDataService()->playerIsBanned($ucid);
}


/**
 * Get recent admin activity
 */
function getRecentActivity($limit = 10) {
    return \DcsStats\Core\AdminPanel::recentActivity((int)$limit);
}

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    return \DcsStats\Core\AdminPanel::dashboardStats();
}

/**
 * Export data to CSV
 */
function exportToCSV($data, $filename = 'export.csv') {
    adminDataService()->exportCsv($data, (string)$filename);
}

/**
 * Export data to JSON
 */
function exportToJSON($data, $filename = 'export.json') {
    adminDataService()->exportJson($data, (string)$filename);
}

/**
 * Sanitize output
 */
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Get role badge HTML
 */
function getRoleBadge($role) {
    return \DcsStats\Core\AdminPanel::roleBadge($role);
}

/**
 * Generate pagination HTML
 */
function getPagination($totalItems, $perPage, $currentPage, $baseUrl) {
    return \DcsStats\Core\AdminPanel::pagination($totalItems, $perPage, $currentPage, $baseUrl);
}

/**
 * Load maintenance configuration
 */
function loadMaintenanceConfig() {
    return \DcsStats\Core\AdminPanel::loadMaintenanceConfig();
}

/**
 * Save maintenance configuration
 */
function saveMaintenanceConfig($config) {
    return \DcsStats\Core\AdminPanel::saveMaintenanceConfig($config);
}
