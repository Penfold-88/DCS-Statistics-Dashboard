<?php
/**
 * Initialize or Reset Version Tracking
 * This can be called to sync version metadata with actual git state
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../admin_functions.php';
require_once __DIR__ . '/../demo_helpers.php';
require_once __DIR__ . '/../version_tracker.php';

requireAdmin();
requirePermission('manage_updates');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

requireCSRFToken();
blockDemoWriteRequest(getCurrentAdmin(), true);

try {
    // Initialize version tracking
    $versionInfo = initializeVersionTracking();
    
    // Log the initialization
    logAdminAction('SYSTEM_VERSION_INIT', [
        'version' => $versionInfo['version'],
        'branch' => $versionInfo['branch'],
        'git_branch' => $versionInfo['git_branch'],
        'admin' => getCurrentAdmin()['username']
    ]);
    
    echo json_encode([
        'success' => true,
        'version_info' => $versionInfo,
        'message' => 'Version tracking initialized successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
