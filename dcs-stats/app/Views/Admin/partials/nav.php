<?php

if (!defined('ADMIN_PANEL')) {
    die('Direct access not permitted');
}

\DcsStats\Core\SupportBootstrap::language();
require_once DCS_APP_PATH . '/bootstrap.php';

if (!isset($currentAdmin)) {
    $currentAdmin = getCurrentAdmin();
}

$adminNavigation = new \DcsStats\Services\Admin\AdminNavigationService();
$adminNavState = $adminNavigation->state($currentAdmin);
$currentPage = $adminNavState['currentPage'];
$demoMode = $adminNavState['demoMode'];
$demoRestricted = $adminNavState['demoRestricted'];
$isStatisticsPage = $adminNavState['isStatisticsPage'];
$isWebsitePage = $adminNavState['isWebsitePage'];
$isCmsPage = $adminNavState['isCmsPage'];
$isGlobalPage = $adminNavState['isGlobalPage'];
$navPermissions = $adminNavState['permissions'];

require DCS_APP_PATH . '/Views/Admin/nav.php';
