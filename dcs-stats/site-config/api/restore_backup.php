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

function normalizeRestorePath($path) {
    return str_replace('\\', '/', (string)$path);
}

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

function ensureRestoreDir($dir) {
    if (is_dir($dir)) {
        return true;
    }

    return mkdir($dir, 0755, true);
}

function isSafeZipPath($path) {
    $path = normalizeRestorePath($path);
    return $path !== '' &&
        $path[0] !== '/' &&
        strpos($path, '../') === false &&
        strpos($path, '/..') === false &&
        strpos($path, ':') === false;
}

function validateBackupZip($zip) {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (!isSafeZipPath($name)) {
            return false;
        }
    }

    return true;
}

function preserveRestorePath($relPath, $preserve) {
    $relPath = normalizeRestorePath($relPath);
    foreach ($preserve as $p) {
        $p = normalizeRestorePath($p);
        if ($relPath === $p || strpos($relPath, $p . '/') === 0) {
            return true;
        }
    }

    return false;
}

function backupTargetBeforeRestore($targetPath, $relPath, $rollbackDir, &$changedFiles) {
    if (isset($changedFiles[$relPath])) {
        return true;
    }

    $rollbackPath = $rollbackDir . '/' . $relPath;
    $changedFiles[$relPath] = [
        'target' => $targetPath,
        'rollback' => $rollbackPath,
        'existed' => file_exists($targetPath)
    ];

    if (!file_exists($targetPath)) {
        return true;
    }

    if (!ensureRestoreDir(dirname($rollbackPath))) {
        return false;
    }

    return copy($targetPath, $rollbackPath);
}

function rollbackRestoreChanges($changedFiles) {
    foreach (array_reverse($changedFiles) as $change) {
        if ($change['existed']) {
            ensureRestoreDir(dirname($change['target']));
            copy($change['rollback'], $change['target']);
        } elseif (file_exists($change['target'])) {
            unlink($change['target']);
        }
    }
}

function createPreRestoreBackup($rootPath) {
    $backupDir = $rootPath . '/backups';
    if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true)) {
        logMessage('Error: Could not create backup directory before restore');
        return false;
    }

    $backupFile = $backupDir . '/pre-restore-' . date('Ymd-His') . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        logMessage('Error: Could not create pre-restore backup');
        return false;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        $relPath = normalizeRestorePath(substr($filePath, strlen($rootPath) + 1));

        if (strpos($relPath, 'backups/') === 0 ||
            strpos($relPath, 'RESTORE_TEMP/') === 0 ||
            strpos($relPath, 'UPGRADE/') === 0) {
            continue;
        }

        if ($file->isDir()) {
            $zip->addEmptyDir($relPath);
        } else {
            $zip->addFile($filePath, $relPath);
        }
    }

    $zip->close();
    logMessage('Pre-restore backup created: ' . basename($backupFile));
    return true;
}

$input = json_decode(file_get_contents('php://input'), true);
requireCSRFToken($input);

$filename = $input['backup'] ?? '';

if (empty($filename)) {
    logMessage('Error: No backup specified');
    exit;
}

// Validate filename format
if (!preg_match('/^backup-\d{8}-\d{6}(?:-[A-Za-z0-9_.-]+-[A-Za-z0-9_.-]+)?\.zip$/', $filename)) {
    logMessage('Error: Invalid backup filename');
    exit;
}

$rootPath = dirname(__DIR__, 2);
$backupFile = $rootPath . '/backups/' . $filename;
$restoreDir = $rootPath . '/RESTORE_TEMP';
$rollbackDir = $rootPath . '/RESTORE_ROLLBACK';

if (!file_exists($backupFile)) {
    logMessage('Error: Backup file not found');
    exit;
}

logMessage("Starting restore from: $filename");

if (!createPreRestoreBackup($rootPath)) {
    logMessage('Restore cancelled to avoid changing live files without a recovery point.');
    exit;
}

// Create restore directory
rrmdir($restoreDir);
rrmdir($rollbackDir);
if (!ensureRestoreDir($restoreDir) || !ensureRestoreDir($rollbackDir)) {
    logMessage('Error: Could not create restore workspace');
    exit;
}

// Extract backup
$zip = new ZipArchive();
if ($zip->open($backupFile) !== TRUE) {
    logMessage('Error: Failed to open backup file');
    exit;
}

if (!validateBackupZip($zip)) {
    $zip->close();
    rrmdir($restoreDir);
    rrmdir($rollbackDir);
    logMessage('Error: Backup contains unsafe file paths');
    exit;
}

logMessage('Extracting backup...');
if (!$zip->extractTo($restoreDir)) {
    $zip->close();
    rrmdir($restoreDir);
    rrmdir($rollbackDir);
    logMessage('Error: Failed to extract backup');
    exit;
}
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
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($restoreDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
$changedFiles = [];
$restoredFiles = 0;

try {
    foreach ($iterator as $file) {
        $filePath = $file->getRealPath();
        $relPath = normalizeRestorePath(substr($filePath, strlen($restoreDir) + 1));
        $targetPath = $rootPath . '/' . $relPath;

        if (preserveRestorePath($relPath, $preserve)) {
            continue;
        }

        if ($file->isDir()) {
            if (!ensureRestoreDir($targetPath)) {
                throw new RuntimeException("Failed to create directory: $relPath");
            }
            continue;
        }

        if (!ensureRestoreDir(dirname($targetPath))) {
            throw new RuntimeException("Failed to create parent directory: $relPath");
        }

        if (!backupTargetBeforeRestore($targetPath, $relPath, $rollbackDir, $changedFiles)) {
            throw new RuntimeException("Failed to stage rollback copy: $relPath");
        }

        if (!copy($filePath, $targetPath)) {
            throw new RuntimeException("Failed to restore file: $relPath");
        }

        $restoredFiles++;
        logMessage("Restored: $relPath");
    }
} catch (Throwable $e) {
    logMessage('Error: ' . $e->getMessage());
    logMessage('Rolling back changed files...');
    rollbackRestoreChanges($changedFiles);
    rrmdir($restoreDir);
    rrmdir($rollbackDir);
    logMessage('Restore failed. Live files have been rolled back where changes were made.');
    exit;
}

// Clean up
logMessage('Cleaning up...');
rrmdir($restoreDir);
rrmdir($rollbackDir);

// Log the action
logAdminAction('BACKUP_RESTORE', [
    'backup' => $filename,
    'restored_files' => $restoredFiles,
    'admin' => getCurrentAdmin()['username']
]);

logMessage('Restore complete!');
logMessage('Please refresh your browser to see the restored version.');
