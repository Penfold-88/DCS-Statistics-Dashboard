<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../admin_functions.php';

requireAdmin();
requirePermission('manage_updates');

set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

function logMessage($msg) {
    echo $msg . "\n";
    @ob_flush();
    flush();
}

$rootPath = dirname(__DIR__, 2); // path to dcs-stats

// Detect development mode via .dev file
$isDev = file_exists($rootPath . '/.dev');
$branch = $isDev ? 'Dev' : 'main';

// ALWAYS backup before updates, regardless of user choice
$backup = true; // Force backup for safety
$specificVersion = !empty($_POST['version']) ? $_POST['version'] : null;

// Repository configuration
// Note: GitHub redirects from Website-Uploader to Dashboard
$repo = 'Penfold-88/DCS-Statistics-Dashboard';

// Get current version
$currentVersion = defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : '1.0.0';
logMessage("Current version: $currentVersion");

// Check latest release when not in dev mode
$latestRelease = null;
if (!$isDev) {
    logMessage("Checking for latest release...");
    $releaseUrl = "https://api.github.com/repos/$repo/releases/latest";
    $ch = curl_init($releaseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
    $releaseData = curl_exec($ch);
    curl_close($ch);

    if ($releaseData) {
        $release = json_decode($releaseData, true);
        if (isset($release['tag_name'])) {
            $latestRelease = $release['tag_name'];
            logMessage("Latest release: " . $latestRelease);
            // Compare versions (handle V prefix)
            $current = ltrim($currentVersion, 'Vv');
            $latest = ltrim($latestRelease, 'Vv');
            if (version_compare($current, $latest, '>=') && !$specificVersion) {
                logMessage("You are already running the latest version.");
                exit;
            }
        }
    }
}

// Determine download URL
if ($specificVersion) {
    // Download specific version/tag
    $apiUrl = "https://api.github.com/repos/$repo/zipball/$specificVersion";
    logMessage("Downloading specific version: $specificVersion");
} elseif ($isDev) {
    // Development mode - use dev branch
    $apiUrl = "https://api.github.com/repos/$repo/zipball/$branch";
    logMessage("Development mode detected - downloading latest from branch: $branch");
} else {
    // Production mode - use latest release tag
    $target = $latestRelease ?? 'main';
    $apiUrl = "https://api.github.com/repos/$repo/zipball/$target";
    logMessage("Downloading latest release: $target");
}

$upgradeDir = $rootPath . '/UPGRADE';
$backupDir = $rootPath . '/backups';

if (!is_dir($upgradeDir)) {
    mkdir($upgradeDir, 0755, true);
}

if ($backup && !is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// CRITICAL: Backup config files before ANY update
logMessage("Backing up configuration files...");
$configBackupDir = $backupDir . '/config-backup-' . date('Ymd-His');
if (!is_dir($configBackupDir)) {
    mkdir($configBackupDir, 0755, true);
}

// List of critical config files to backup
$configFiles = [
    '/api_config.json',
    '/site_config.json',
    '/.version_meta.json',
    '/site-config/data/users.json',
    '/site-config/data/logs.json',
    '/site-config/data/bans.json',
    '/site-config/data/sessions.json',
    '/.env',
    '/docker-compose.yml',
    '/Dockerfile',
    '/Dockerfile.simple'
];

foreach ($configFiles as $file) {
    $sourcePath = $rootPath . $file;
    if (file_exists($sourcePath)) {
        $destPath = $configBackupDir . $file;
        $destDir = dirname($destPath);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        if (copy($sourcePath, $destPath)) {
            logMessage("✓ Backed up: $file");
        } else {
            logMessage("⚠ Failed to backup: $file");
        }
    }
}
logMessage("Config backup complete: $configBackupDir");

$downloadLabel = $specificVersion ? "version $specificVersion" : ($isDev ? 'dev branch' : 'latest release');
logMessage("Downloading $downloadLabel...");
$zipFile = $upgradeDir . '/update.zip';
$ch = curl_init($apiUrl);
$fp = fopen($zipFile, 'w');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/vnd.github.v3+json'
]);
$download = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
fclose($fp);

if ($download === false || $httpCode !== 200) {
    logMessage('Download failed. HTTP Code: ' . $httpCode);
    @unlink($zipFile);
    exit;
}
logMessage('Download complete.');

$zip = new ZipArchive();
if ($zip->open($zipFile) !== TRUE) {
    logMessage('Failed to open zip archive');
    exit;
}
$zip->extractTo($upgradeDir);
$zip->close();
logMessage('Extraction complete.');

$extractedDirs = glob($upgradeDir . '/*', GLOB_ONLYDIR);
if (empty($extractedDirs)) {
    logMessage('No extracted directory found');
    exit;
}
$newCodeDir = $extractedDirs[0] . '/dcs-stats';
if (!is_dir($newCodeDir)) {
    logMessage('dcs-stats directory not found in archive');
    exit;
}

if ($backup) {
    $backupFile = $backupDir . '/backup-' . date('Ymd-His') . '.zip';
    logMessage('Creating backup...');
    $backupZip = new ZipArchive();
    if ($backupZip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relPath = substr($filePath, strlen($rootPath) + 1);
            if (strpos($relPath, 'backups') === 0 || strpos($relPath, 'UPGRADE') === 0) {
                continue;
            }
            if ($file->isDir()) {
                $backupZip->addEmptyDir($relPath);
            } else {
                $backupZip->addFile($filePath, $relPath);
            }
        }
        $backupZip->close();
        logMessage('Backup saved to ' . $backupFile);
        
        // Clean up old backups - keep only the 5 most recent
        cleanupOldBackups($backupDir, 5);
    } else {
        logMessage('Failed to create backup');
    }
}

// Directories and files to NEVER overwrite during update
$exceptions = [
    // Data directories
    'site-config/data',
    'data',
    'backups',
    'UPGRADE',
    
    // Configuration files
    'api_config.json',
    'site_config.json',
    '.version_meta.json',
    '.env',
    '.dev',
    
    // Docker files (user customized)
    'docker-compose.yml',
    'docker-compose.override.yml',
    'Dockerfile',
    'Dockerfile.simple',
    '.dockerignore',
    
    // User uploads or custom files
    'uploads',
    'custom',
    'logs',
    
    // Git files
    '.git',
    '.gitignore',
    '.gitattributes'
];

$newFiles = [];
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($newCodeDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($files as $file) {
    $filePath = $file->getRealPath();
    $relPath = substr($filePath, strlen($newCodeDir) + 1);
    $targetPath = $rootPath . '/' . $relPath;
    $newFiles[] = $relPath;

    // Check if this file/directory should be preserved
    $shouldSkip = false;
    foreach ($exceptions as $ex) {
        if ($relPath === $ex || strpos($relPath, $ex . '/') === 0) {
            $shouldSkip = true;
            break;
        }
    }
    
    if ($shouldSkip) {
        logMessage('Preserving: ' . $relPath);
        continue;
    }
    
    if ($file->isDir()) {
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
            logMessage('Created directory: ' . $relPath);
        }
    } else {
        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0755, true);
        }
        
        // Extra safety: Don't overwrite critical config files
        if (file_exists($targetPath) && in_array(basename($targetPath), ['api_config.json', 'site_config.json', '.env', 'users.json'])) {
            logMessage('⚠ Skipping config file: ' . $relPath);
            continue;
        }
        
        copy($filePath, $targetPath);
        logMessage('Updated file: ' . $relPath);
    }
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::CHILD_FIRST
);
foreach ($iterator as $file) {
    $filePath = $file->getRealPath();
    $relPath = substr($filePath, strlen($rootPath) + 1);

    foreach ($exceptions as $ex) {
        if ($relPath === $ex || strpos($relPath, $ex . '/') === 0) {
            continue 2;
        }
    }

    if (!in_array($relPath, $newFiles)) {
        if ($file->isDir()) {
            rmdir($filePath);
            logMessage('Removed directory: ' . $relPath);
        } else {
            unlink($filePath);
            logMessage('Removed file: ' . $relPath);
        }
    }
}

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
rrmdir($upgradeDir);

// Update version metadata using tracker
require_once dirname(__DIR__) . '/version_tracker.php';
updateVersionMetadata(
    $specificVersion ?? ($release['tag_name'] ?? $currentVersion),
    $branch,
    getCurrentAdmin()['username']
);

// Update version in config file if we have a new version number
$newVersion = $specificVersion ?? ($release['tag_name'] ?? null);
if ($newVersion && $newVersion !== $currentVersion) {
    $configFile = dirname(__DIR__) . '/config.php';
    if (file_exists($configFile)) {
        $config = file_get_contents($configFile);
        $config = preg_replace(
            "/define\('ADMIN_PANEL_VERSION', '[^']+'/",
            "define('ADMIN_PANEL_VERSION', '$newVersion'",
            $config
        );
        file_put_contents($configFile, $config);
        logMessage("Updated version to: $newVersion");
    }
}

// Log the update action
logAdminAction('SYSTEM_UPDATE', [
    'from_version' => $currentVersion,
    'to_version' => $newVersion ?? 'latest',
    'branch' => $branch,
    'admin' => getCurrentAdmin()['username']
]);

logMessage('Update complete.');
logMessage('Please refresh your browser to see the changes.');

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
