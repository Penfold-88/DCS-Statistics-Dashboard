<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../admin_functions.php';
require_once __DIR__ . '/../demo_helpers.php';

requireAdmin();
requirePermission('manage_updates');

header('Content-Type: application/json');

if (isDemoRestricted()) {
    echo json_encode(['success' => false, 'error' => demoRestrictionMessage()]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
requireCSRFToken($input);

$filename = $input['backup'] ?? '';

if (empty($filename)) {
    echo json_encode(['success' => false, 'error' => 'No backup specified']);
    exit;
}

// Validate filename format (includes branch and version)
if (!preg_match('/^backup-\d{8}-\d{6}(?:-[A-Za-z0-9_.-]+-[A-Za-z0-9_.-]+)?\.zip$/', $filename)) {
    echo json_encode(['success' => false, 'error' => 'Invalid backup filename']);
    exit;
}

$rootPath = dirname(__DIR__, 2);
$backupDir = $rootPath . '/backups';
$backupDirReal = realpath($backupDir);
$backupFile = $backupDir . '/' . $filename;
$backupFileReal = realpath($backupFile);

if ($backupDirReal === false || $backupFileReal === false || !is_file($backupFileReal)) {
    echo json_encode(['success' => false, 'error' => 'Backup not found']);
    exit;
}

$backupDirPrefix = rtrim($backupDirReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
if (strpos($backupFileReal, $backupDirPrefix) !== 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid backup path']);
    exit;
}

if (unlink($backupFileReal)) {
    // Log the action
    logAdminAction('BACKUP_DELETE', [
        'backup' => $filename,
        'admin' => getCurrentAdmin()['username']
    ]);
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete backup']);
}
