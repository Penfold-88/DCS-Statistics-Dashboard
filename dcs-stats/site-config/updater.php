<?php
/**
 * Admin panel updater interface and utilities
 * Provides backup and update functionality with graceful
 * handling when the ZipArchive extension is unavailable.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';

// Require admin login and proper permission
requireAdmin();
requirePermission('change_settings');

// Current admin for display purposes
$currentAdmin = getCurrentAdmin();

// GitHub repository information
define('GITHUB_REPO_OWNER', 'Penfold-88');
define('GITHUB_REPO_NAME', 'DCS-Statistics-Dashboard');

/**
 * Fetch a remote file via file_get_contents or cURL as a fallback.
 *
 * @param string $url URL to fetch
 * @return string|null File contents or null on failure
 */
function fetchRemoteFile(string $url): ?string {
    if (ini_get('allow_url_fopen')) {
        $data = @file_get_contents($url);
        if ($data !== false) {
            return $data;
        }
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'DCS-Stats-Updater',
            CURLOPT_TIMEOUT => 10,
        ]);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data !== false) {
            return $data;
        }
    }

    return null;
}

/**
 * Create a zip backup of the provided directory.
 *
 * @param string $source Directory to backup
 * @param string $destination Destination zip file
 * @return bool True on success, false on failure
 */
function createBackup(string $source, string $destination): bool {
    if (!class_exists('ZipArchive')) {
        echo "Error: PHP ZipArchive extension not installed. " .
             "Please enable the zip extension in php.ini or install the php-zip package. Update aborted.";
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        echo "Error: Unable to create backup archive.";
        return false;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $skipDir = realpath(dirname($destination));

    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();

            // Skip backup directory to avoid self-inclusion
            if ($skipDir && strpos($filePath, $skipDir) === 0) {
                continue;
            }

            $relativePath = substr($filePath, strlen($source) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();
    return true;
}

/**
 * Recursively copy a directory tree.
 *
 * @param string $src Source directory
 * @param string $dst Destination directory
 */
function recurseCopy(string $src, string $dst): void {
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        if (is_dir($srcPath)) {
            recurseCopy($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}

/**
 * Recursively remove a directory tree.
 *
 * @param string $dir Directory to remove
 */
function rrmdir(string $dir): void {
    if (!is_dir($dir)) {
        return;
    }
    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $item) {
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            rrmdir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

/**
 * Retrieve remote version string from GitHub for a given branch.
 *
 * @param string $branch Branch name
 * @return string|null Version string or null on failure
 */
function getRemoteVersion(string $branch): ?string {
    $url = sprintf(
        'https://raw.githubusercontent.com/%s/%s/%s/dcs-stats/site-config/config.php',
        GITHUB_REPO_OWNER,
        GITHUB_REPO_NAME,
        $branch
    );
    $data = fetchRemoteFile($url);
    if ($data === null) {
        return null;
    }
    if (preg_match("/define\('ADMIN_PANEL_VERSION',\s*'([^']+)'\);/", $data, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Download a zip archive for the given branch from GitHub.
 *
 * @param string $branch Branch name
 * @return string|null Path to downloaded archive or null on failure
 */
function downloadBranchArchive(string $branch): ?string {
    $url = sprintf(
        'https://github.com/%s/%s/archive/refs/heads/%s.zip',
        GITHUB_REPO_OWNER,
        GITHUB_REPO_NAME,
        $branch
    );
    $data = fetchRemoteFile($url);
    if ($data === null) {
        return null;
    }
    $tmp = tempnam(sys_get_temp_dir(), 'update_');
    file_put_contents($tmp, $data);
    return $tmp;
}

/**
 * Perform update by extracting provided archive into target directory.
 *
 * @param string $archivePath Path to update archive
 * @param string $targetDir Directory to extract to
 * @return bool True on success, false on failure
 */
function performUpdate(string $archivePath, string $targetDir): bool {
    $extractPath = sys_get_temp_dir() . '/dcs_update_' . uniqid();
    mkdir($extractPath, 0755, true);
    $extracted = false;

    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($archivePath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
            $extracted = true;
        } else {
            echo "Error: Unable to open update archive.";
            rrmdir($extractPath);
            return false;
        }
    } else {
        echo "Warning: PHP ZipArchive extension not installed. " .
             "Consider enabling it via php.ini or installing php-zip. " .
             "Attempting fallback extraction...";

        if (class_exists('PharData')) {
            try {
                $phar = new PharData($archivePath);
                $phar->extractTo($extractPath, null, true);
                $extracted = true;
            } catch (Exception $e) {
                // continue to next fallback
            }
        }

        if (!$extracted) {
            $unzip = trim(shell_exec('command -v unzip'));
            if ($unzip) {
                $cmd = escapeshellcmd($unzip) . ' ' . escapeshellarg($archivePath) . ' -d ' . escapeshellarg($extractPath);
                exec($cmd, $output, $retval);
                $extracted = ($retval === 0);
            }
        }
    }

    if (!$extracted) {
        rrmdir($extractPath);
        echo "\nError: No suitable archive extraction method available. Update aborted.";
        return false;
    }

    // Locate extracted repo root containing dcs-stats
    $rootItems = scandir($extractPath);
    $sourceDir = '';
    foreach ($rootItems as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $candidate = $extractPath . '/' . $item . '/dcs-stats';
        if (is_dir($candidate)) {
            $sourceDir = $candidate;
            break;
        }
    }

    if (!$sourceDir) {
        rrmdir($extractPath);
        echo 'Error: Extracted archive missing dcs-stats folder.';
        return false;
    }

    recurseCopy($sourceDir, $targetDir);
    rrmdir($extractPath);
    return true;
}

$message = '';
$messageType = '';
$branch = $_POST['branch'] ?? 'master';
if (!in_array($branch, ['master', 'dev'], true)) {
    $branch = 'master';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = ERROR_MESSAGES['csrf_invalid'];
        $messageType = 'error';
    } else {
        $remoteVersion = getRemoteVersion($branch);
        if ($remoteVersion === null) {
            $message = 'Unable to retrieve remote version.';
            $messageType = 'error';
        } elseif (version_compare($remoteVersion, ADMIN_PANEL_VERSION, '<=')) {
            $message = 'Already up to date.';
            $messageType = 'success';
        } else {
            $archivePath = downloadBranchArchive($branch);
            if (!$archivePath) {
                $message = 'Failed to download update archive.';
                $messageType = 'error';
            } else {
                $targetDir = dirname(__DIR__);
                $backupDir = __DIR__ . '/data/backups';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }
                $backupFile = $backupDir . '/site-backup-' . date('YmdHis') . '.zip';

                if (createBackup($targetDir, $backupFile)) {
                    if (performUpdate($archivePath, $targetDir)) {
                        $message = 'Update to version ' . $remoteVersion . ' completed successfully.';
                        $messageType = 'success';
                    } else {
                        $message = 'Update failed. See errors above.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Backup failed. Update aborted.';
                    $messageType = 'error';
                }
                unlink($archivePath);
            }
        }
    }
}

$pageTitle = 'System Updater';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-wrapper">
    <?php include 'nav.php'; ?>
    <main class="admin-main">
        <header class="admin-header">
            <h1><?= $pageTitle ?></h1>
            <div class="admin-user-menu">
                <div class="admin-user-info">
                    <div class="admin-username"><?= e($currentAdmin['username']) ?></div>
                    <div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div>
                </div>
                <a href="logout.php" class="btn btn-secondary btn-small">Logout</a>
            </div>
        </header>
        <div class="admin-content">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="branch">Select Branch</label>
                    <select name="branch" id="branch" class="form-control">
                        <option value="master" <?= $branch === 'master' ? 'selected' : '' ?>>master</option>
                        <option value="dev" <?= $branch === 'dev' ? 'selected' : '' ?>>dev</option>
                    </select>
                    <div id="dev-warning" class="alert alert-warning" style="display: <?= $branch === 'dev' ? 'block' : 'none' ?>; margin-top:8px;">
                        This is not a stable release. Use at your own risk.
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Run Update</button>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
document.getElementById('branch').addEventListener('change', function() {
    document.getElementById('dev-warning').style.display = this.value === 'dev' ? 'block' : 'none';
});
</script>
</body>
</html>

