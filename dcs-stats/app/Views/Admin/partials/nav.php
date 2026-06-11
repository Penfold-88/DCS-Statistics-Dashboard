<?php

if (!defined('ADMIN_PANEL')) {
    die('Direct access not permitted');
}

require_once DCS_ROOT_PATH . '/language.php';
require_once DCS_ROOT_PATH . '/app/bootstrap.php';

if (!isset($currentAdmin)) {
    $currentAdmin = getCurrentAdmin();
}

$adminNavigation = new \DcsStats\Services\Admin\AdminNavigationService();
$adminNavState = $adminNavigation->state($currentAdmin);
$currentPage = $adminNavState['currentPage'];
$demoMode = $adminNavState['demoMode'];
$demoRestricted = $adminNavState['demoRestricted'];
$isSettingsPage = $adminNavState['isSettingsPage'];
$navPermissions = $adminNavState['permissions'];

require DCS_ROOT_PATH . '/app/Views/Admin/nav.php';
