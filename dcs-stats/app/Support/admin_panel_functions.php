<?php

require_once DCS_APP_PATH . '/Support/demo_functions.php';

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

function getCurrentAdmin() {
    return \DcsStats\Core\AdminPanel::currentAdmin();
}

function formatDate($date, $format = null) {
    return \DcsStats\Core\AdminPanel::formatDate($date, $format);
}

function normalizeAdminLog($log) {
    return \DcsStats\Core\AdminPanel::normalizeLog($log);
}

function adminLogTimestamp($log) {
    return \DcsStats\Core\AdminPanel::logTimestamp(is_array($log) ? $log : []);
}

function logAdminAction($action, $details = []) {
    \DcsStats\Core\AdminPanel::logAction($action, is_array($details) ? $details : []);
}

function getPlayers($search = null, $limit = null, $offset = 0) {
    return adminDataService()->players($search, $limit, (int)$offset);
}

function getPlayerStats($ucid) {
    return adminDataService()->playerStats($ucid);
}

function getPlayerBans($activeOnly = true) {
    return adminDataService()->playerBans((bool)$activeOnly);
}

function isPlayerBanned($ucid) {
    return adminDataService()->playerIsBanned($ucid);
}

function getRecentActivity($limit = 10) {
    return \DcsStats\Core\AdminPanel::recentActivity((int)$limit);
}

function getDashboardStats() {
    return \DcsStats\Core\AdminPanel::dashboardStats();
}

function exportToCSV($data, $filename = 'export.csv') {
    adminDataService()->exportCsv($data, (string)$filename);
}

function exportToJSON($data, $filename = 'export.json') {
    adminDataService()->exportJson($data, (string)$filename);
}

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function getRoleBadge($role) {
    return \DcsStats\Core\AdminPanel::roleBadge($role);
}

function getPagination($totalItems, $perPage, $currentPage, $baseUrl) {
    return \DcsStats\Core\AdminPanel::pagination($totalItems, $perPage, $currentPage, $baseUrl);
}

function loadMaintenanceConfig() {
    return \DcsStats\Core\AdminPanel::loadMaintenanceConfig();
}

function saveMaintenanceConfig($config) {
    return \DcsStats\Core\AdminPanel::saveMaintenanceConfig($config);
}
