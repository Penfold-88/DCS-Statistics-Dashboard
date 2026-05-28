<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../admin_functions.php';
require_once __DIR__ . '/../demo_helpers.php';

requireAdmin();
requirePermission('manage_updates');

set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

if (isDemoRestricted()) {
    echo demoRestrictionMessage() . "\n";
    exit;
}

function logMessage($msg) {
    echo $msg . "\n";
    @ob_flush();
    flush();
}

$input = json_decode(file_get_contents('php://input'), true);
requireCSRFToken($input);

$filename = $input['backup'] ?? '';

if (empty($filename)) {
    logMessage('Error: No backup specified');
    exit;
}

// Validate filename format
if (!preg_match('/^backup-\d{8}-\d{6}\.zip$/', $filename)) {
    logMessage('Error: Invalid backup filename');
    exit;
}

$rootPath = dirname(__DIR__, 2);
$backupFile = $rootPath . '/backups/' . $filename;

if (!file_exists($backupFile)) {
    logMessage('Error: Backup file not found');
    exit;
}

logMessage("Starting restore from: $filename");

// Create restore directory
$restoreDir = $rootPath . '/RESTORE_TEMP';
if (!is_dir($restoreDir)) {
    mkdir($restoreDir, 0755, true);
}

// Extract backup
$zip = new ZipArchive();
if ($zip->open($backupFile) !== TRUE) {
    logMessage('Error: Failed to open backup file');
    exit;
}

logMessage('Extracting backup...');
$zip->extractTo($restoreDir);
$zip->close();

// Files/directories to preserve during restore
$preserve = [
    'backups',
    'RESTORE_TEMP',
    'site-config/data',
    'api_config.json',
    'site_config.json'
];

// Restore files
logMessage('Restoring files...');
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($restoreDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($files as $file) {
    $filePath = $file->getRealPath();
    $relPath = substr($filePath, strlen($restoreDir) + 1);
    $targetPath = $rootPath . '/' . $relPath;
    
    // Skip preserved directories
    $skip = false;
    foreach ($preserve as $p) {
        if ($relPath === $p || strpos($relPath, $p . '/') === 0) {
            $skip = true;
            break;
        }
    }
    
    if ($skip) {
        continue;
    }
    
    if ($file->isDir()) {
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }
    } else {
        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0755, true);
        }
        copy($filePath, $targetPath);
        logMessage("Restored: $relPath");
    }
}

// Clean up
logMessage('Cleaning up...');
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            rrmdir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}
rrmdir($restoreDir);

// Log the action
logAdminAction('BACKUP_RESTORE', [
    'backup' => $filename,
    'admin' => getCurrentAdmin()['username']
]);

logMessage('Restore complete!');
logMessage('Please refresh your browser to see the restored version.');
