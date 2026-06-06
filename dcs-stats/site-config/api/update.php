<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../admin_functions.php';
require_once __DIR__ . '/../demo_helpers.php';
require_once __DIR__ . '/../update_channel.php';

requireAdmin();
requirePermission('manage_updates');
requireCSRFToken();

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

function normalizeUpdatePath($path) {
    return str_replace('\\', '/', (string)$path);
}

function isSafeUpdateZipPath($path) {
    $path = normalizeUpdatePath($path);

    return $path !== '' &&
        $path[0] !== '/' &&
        strpos($path, '../') === false &&
        strpos($path, '/..') === false &&
        strpos($path, ':') === false;
}

function validateUpdateZip($zip) {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (!isSafeUpdateZipPath($name)) {
            return false;
        }
    }

    return true;
}

function buildVersionLabel($branch, $commitDate, $commitSha) {
    $date = $commitDate ? date('Y-m-d', strtotime($commitDate)) : date('Y-m-d');
    $shortSha = $commitSha ? substr($commitSha, 0, 12) : 'unknown';
    return $branch . ' @ ' . $date . ' #' . $shortSha;
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

if (!class_exists('ZipArchive')) {
    logMessage('Update cancelled: PHP ZipArchive is not available.');
    logMessage('Enable the PHP zip extension, then restart Apache and try again.');
    logMessage('For XAMPP, open php.ini, enable extension=zip, save, and restart Apache.');
    exit;
}

$channelConfig = getUpdateChannelConfig();
$branch = $channelConfig['branch'];
$repo = $channelConfig['repo'];
// ALWAYS backup before updates, regardless of user choice
$backup = true; // Force backup for safety
$specificVersion = !empty($_POST['version']) ? $_POST['version'] : null;

// Get current version
$currentVersion = defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : '1.0.0';
require_once dirname(__DIR__) . '/version_tracker.php';
$currentVersionInfo = getCurrentVersionInfo();
$currentBuildLabel = $currentVersionInfo['version'] ?? $currentVersion;
logMessage("Current version: $currentBuildLabel");
logMessage("Update channel: {$channelConfig['channel']}");
logMessage("GitHub branch: $branch");

// Determine download URL
if ($specificVersion) {
    // Download specific version/tag
    $apiUrl = "https://api.github.com/repos/$repo/zipball/$specificVersion";
    logMessage("Downloading specific version: $specificVersion");
} else {
    // Download latest from branch
    $apiUrl = "https://api.github.com/repos/$repo/zipball/$branch";
    logMessage("Downloading latest from branch: $branch");
}

$rootPath = dirname(__DIR__, 2); // path to dcs-stats
$upgradeParentDir = $rootPath . '/UPGRADE';
$upgradeDir = $upgradeParentDir . '/update-' . bin2hex(random_bytes(8));
$backupDir = $rootPath . '/backups';

if (!is_dir($upgradeParentDir)) {
    mkdir($upgradeParentDir, 0755, true);
}
mkdir($upgradeDir, 0755, true);
register_shutdown_function(function() use ($upgradeDir) {
    if (is_dir($upgradeDir)) {
        rrmdir($upgradeDir);
    }
});

if ($backup && !is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Check selected branch exists and log the latest commit.
logMessage("Checking GitHub branch...");
$branchUrl = "https://api.github.com/repos/$repo/branches/" . rawurlencode($branch);
$ch = curl_init($branchUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
$branchData = curl_exec($ch);
$branchCurlError = curl_error($ch);
$branchHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$specificVersion && ($branchHttpCode !== 200 || !$branchData)) {
    logMessage("Selected branch was not found: $branch");
    if ($branch === 'master') {
        logMessage("Trying fallback branch: main");
        $branch = 'main';
        $apiUrl = "https://api.github.com/repos/$repo/zipball/$branch";
        $branchUrl = "https://api.github.com/repos/$repo/branches/main";
        $ch = curl_init($branchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        $branchData = curl_exec($ch);
        $branchCurlError = curl_error($ch);
        $branchHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
}

if (!$specificVersion && $branchHttpCode !== 200) {
    logMessage("Could not find a usable GitHub branch. Update cancelled.");
    logMessage("HTTP Code: $branchHttpCode");
    if (!empty($branchCurlError)) {
        logMessage("Connection Error: $branchCurlError");
    }
    exit;
}

$remoteCommitSha = null;
$remoteCommitDate = null;
if (!$specificVersion && $branchData) {
    $branchInfo = json_decode($branchData, true);
    $remoteCommitSha = $branchInfo['commit']['sha'] ?? null;
    $remoteCommitDate = $branchInfo['commit']['commit']['committer']['date'] ?? null;
    if ($remoteCommitSha) {
        logMessage("Latest branch commit: " . substr($remoteCommitSha, 0, 12));
    }
    if ($remoteCommitDate) {
        logMessage("Latest branch date: " . date('Y-m-d H:i:s', strtotime($remoteCommitDate)));
    }
}

// Check latest release only when a specific downgrade/tag is requested for context.
$release = null;
if ($specificVersion) {
    logMessage("Checking release information...");
    $releaseUrl = "https://api.github.com/repos/$repo/releases/latest";
    $ch = curl_init($releaseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
    $releaseData = curl_exec($ch);
    curl_close($ch);

    if ($releaseData) {
        $release = json_decode($releaseData, true);
        if (isset($release['tag_name'])) {
            logMessage("Latest release: " . $release['tag_name']);
        }
    }
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
    '/custom_theme.css',
    '/header_custom.css',
    '/menu_config.json',
    '/site-config/data/api_config.json',
    '/site-config/data/users.json',
    '/site-config/data/logs.json',
    '/site-config/data/bans.json',
    '/site-config/data/sessions.json',
    '/.env',
    '/docker-compose.yml',
    '/docker-compose.override.yml',
    '/Dockerfile',
    '/Dockerfile.simple',
    '/.dockerignore',
    '/docker/docker-compose.yml',
    '/docker/docker-compose.override.yml',
    '/docker/Dockerfile',
    '/docker/Dockerfile.dockerignore',
    '/docker/Dockerfile.simple'
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

$downloadLabel = $specificVersion ? "version $specificVersion" : "{$channelConfig['channel']} branch ($branch)";
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
if (!validateUpdateZip($zip)) {
    $zip->close();
    @unlink($zipFile);
    logMessage('Update cancelled: downloaded archive contains unsafe file paths.');
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
            $relPath = normalizeUpdatePath(substr($filePath, strlen($rootPath) + 1));
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
    'custom_theme.css',
    'header_custom.css',
    'menu_config.json',
    'site-config/theme_backups',
    '.env',
    '.dev',
    
    // Docker files (user customized)
    'docker-compose.yml',
    'docker-compose.override.yml',
    'Dockerfile',
    'Dockerfile.simple',
    '.dockerignore',
    'docker/docker-compose.yml',
    'docker/docker-compose.override.yml',
    'docker/Dockerfile',
    'docker/Dockerfile.simple',
    'docker/Dockerfile.dockerignore',
    
    // User uploads or custom files
    'uploads',
    'custom',
    'logs',
    'site-config/theme_backups',
    
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
    $relPath = normalizeUpdatePath(substr($filePath, strlen($newCodeDir) + 1));
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
    $relPath = normalizeUpdatePath(substr($filePath, strlen($rootPath) + 1));

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
rrmdir($upgradeDir);

// Update version metadata using tracker
$versionLabel = $specificVersion ?? buildVersionLabel($branch, $remoteCommitDate, $remoteCommitSha);
updateVersionMetadata(
    $versionLabel,
    $branch,
    getCurrentAdmin()['username'],
    $remoteCommitSha,
    $remoteCommitDate
);

$checkinFile = dirname(__DIR__, 2) . '/install_checkin.php';
if (file_exists($checkinFile)) {
    require_once $checkinFile;
    $checkinResult = runInstallCheckinIfDue(
        getCurrentVersionInfo(),
        getUpdateChannelConfig(),
        ['event' => 'update', 'force' => true]
    );
    logMessage('Install check-in after update: ' . ($checkinResult['status'] ?? 'unknown'));
}

// Update version in config file if we have a new version number
$newVersion = $specificVersion ?? null;
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
    'to_version' => $newVersion ?? $versionLabel,
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
