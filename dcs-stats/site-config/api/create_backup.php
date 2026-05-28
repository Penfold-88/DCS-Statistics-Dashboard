<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../admin_functions.php';

requireAdmin();
requirePermission('manage_updates');
requireCSRFToken();

set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

function logMessage($msg) {
    echo $msg . "\n";
    @ob_flush();
    flush();
}

$rootPath = dirname(__DIR__, 2);
$backupDir = $rootPath . '/backups';

// Create backup directory if it doesn't exist
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Get current version and branch info
require_once __DIR__ . '/../version_tracker.php';
$versionInfo = getCurrentVersionInfo();
$currentVersion = $versionInfo['version'] ?? (defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : '1.0.0');
$currentBranch = $versionInfo['branch'] ?? 'main';

$safeVersion = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $currentVersion);
$backupName = 'backup-' . date('Ymd-His') . '-' . $currentBranch . '-' . $safeVersion;
$backupFile = $backupDir . '/' . $backupName . '.zip';

logMessage("Creating backup: $backupName");
logMessage("Version: $currentVersion");
logMessage("Branch: $currentBranch");

$backupZip = new ZipArchive();
if ($backupZip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    // Priority: First backup all critical config files
    $configFiles = [
        'api_config.json',
        'site_config.json',
        '.version_meta.json',
        '.env',
        'docker-compose.yml',
        'Dockerfile',
        'Dockerfile.simple'
    ];
    
    logMessage("Backing up configuration files...");
    foreach ($configFiles as $configFile) {
        $configPath = $rootPath . '/' . $configFile;
        if (file_exists($configPath)) {
            $backupZip->addFile($configPath, $configFile);
            logMessage("✓ Config: $configFile");
        }
    }
    
    $fileCount = 0;
    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        $relPath = substr($filePath, strlen($rootPath) + 1);
        
        // Skip backup directories and temp files
        if (strpos($relPath, 'backups') === 0 || 
            strpos($relPath, 'UPGRADE') === 0 ||
            strpos($relPath, 'RESTORE_TEMP') === 0) {
            continue;
        }
        
        if ($file->isDir()) {
            $backupZip->addEmptyDir($relPath);
        } else {
            $backupZip->addFile($filePath, $relPath);
            $fileCount++;
            if ($fileCount % 100 === 0) {
                logMessage("Backed up $fileCount files...");
            }
        }
    }
    
    // Add metadata to the backup
    $metadata = [
        'version' => $currentVersion,
        'branch' => $currentBranch,
        'commit_sha' => $versionInfo['commit_sha'] ?? null,
        'commit_date' => $versionInfo['commit_date'] ?? null,
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => getCurrentAdmin()['username']
    ];
    $backupZip->addFromString('.backup_meta.json', json_encode($metadata, JSON_PRETTY_PRINT));
    
    $backupZip->close();
    
    $size = filesize($backupFile);
    $sizeFormatted = formatBytes($size);
    
    logMessage("Backup complete!");
    logMessage("Total files: $fileCount");
    logMessage("Backup size: $sizeFormatted");
    
    // Log the action
    logAdminAction('BACKUP_CREATE', [
        'backup' => $backupName . '.zip',
        'size' => $sizeFormatted,
        'admin' => getCurrentAdmin()['username']
    ]);
    
    // Clean up old backups - keep only the 5 most recent
    cleanupOldBackups($backupDir, 5);
} else {
    logMessage('Error: Failed to create backup');
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Clean up old backups, keeping only the most recent ones
 */
function cleanupOldBackups($backupDir, $maxBackups = 5) {
    if (!is_dir($backupDir)) {
        return;
    }
    
    // Get all backup files
    $backups = glob($backupDir . '/backup-*.zip');
    if (!$backups || count($backups) <= $maxBackups) {
        return;
    }
    
    // Sort by modification time (newest first)
    usort($backups, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    // Delete old backups
    $deleted = 0;
    for ($i = $maxBackups; $i < count($backups); $i++) {
        if (unlink($backups[$i])) {
            $deleted++;
            logMessage('Deleted old backup: ' . basename($backups[$i]));
        }
    }
    
    if ($deleted > 0) {
        logMessage("Cleaned up $deleted old backup(s). Keeping $maxBackups most recent.");
    }
}
