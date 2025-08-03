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
 * Retrieve the current local version from version.txt, falling back to
 * the ADMIN_PANEL_VERSION constant if the file is missing.
 *
 * @return string|null Version string or null when unavailable
 */
function getLocalVersion(): ?string {
    $path = __DIR__ . '/version.txt';
    if (is_file($path)) {
        $version = trim(file_get_contents($path));
        if ($version !== '') {
            return $version;
        }
    }
    return defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : null;
}

/**
 * Retrieve remote version string from GitHub for a given branch.
 *
 * Attempts to read a simple version.txt file first, falling back to parsing
 * config.php if the version file is unavailable.
 *
 * @param string $branch Branch name
 * @return string|null Version string or null on failure
 */
function getRemoteVersion(string $branch): ?string {
    $base = sprintf(
        'https://raw.githubusercontent.com/%s/%s/%s/dcs-stats/site-config/',
        GITHUB_REPO_OWNER,
        GITHUB_REPO_NAME,
        $branch
    );

    // Try dedicated version.txt file first
    $data = fetchRemoteFile($base . 'version.txt');
    if ($data !== null) {
        $version = trim($data);
        if ($version !== '') {
            return $version;
        }
    }

    // Fall back to parsing ADMIN_PANEL_VERSION from config.php
    $data = fetchRemoteFile($base . 'config.php');
    if ($data !== null && preg_match("/define\('ADMIN_PANEL_VERSION',\s*'([^']+)'\);/", $data, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Retrieve list of file paths in the repository for a given branch.
 *
 * @param string $branch Branch name
 * @return array|null Array of file paths or null on failure
 */
function getRemoteFileList(string $branch): ?array {
    $url = sprintf(
        'https://api.github.com/repos/%s/%s/git/trees/%s?recursive=1',
        GITHUB_REPO_OWNER,
        GITHUB_REPO_NAME,
        $branch
    );
    $data = fetchRemoteFile($url);
    if ($data === null) {
        return null;
    }
    $json = json_decode($data, true);
    if (!is_array($json) || empty($json['tree'])) {
        return null;
    }
    $files = [];
    foreach ($json['tree'] as $node) {
        if ($node['type'] === 'blob' && strpos($node['path'], 'dcs-stats/') === 0) {
            $files[] = $node['path'];
        }
    }
    return $files;
}

/**
 * Retrieve the last commit timestamp for a file in the specified branch.
 *
 * @param string $branch Branch name
 * @param string $path   File path within the repository
 * @return int|null UNIX timestamp of last commit or null on failure
 */
function getRemoteCommitDate(string $branch, string $path): ?int {
    $url = sprintf(
        'https://api.github.com/repos/%s/%s/commits?sha=%s&path=%s&per_page=1',
        GITHUB_REPO_OWNER,
        GITHUB_REPO_NAME,
        $branch,
        rawurlencode($path)
    );
    $data = fetchRemoteFile($url);
    if ($data === null) {
        return null;
    }
    $json = json_decode($data, true);
    if (!is_array($json) || empty($json[0]['commit']['committer']['date'])) {
        return null;
    }
    return strtotime($json[0]['commit']['committer']['date']);
}

/**
 * Download and apply individual files from a branch to the target directory.
 *
 * @param string $branch Branch name
 * @param string $targetDir Directory to update
 * @return array|null List of updated files or null on failure
 */
function updateFromBranchFiles(string $branch, string $targetDir): ?array {
    $files = getRemoteFileList($branch);
    if ($files === null) {
        return null;
    }
    $updated = [];
    foreach ($files as $path) {
        $remoteTime = getRemoteCommitDate($branch, $path);
        if ($remoteTime === null) {
            return null;
        }
        $relative = substr($path, strlen('dcs-stats/'));
        $localPath = $targetDir . '/' . $relative;
        $localTime = is_file($localPath) ? filemtime($localPath) : 0;
        if ($remoteTime <= $localTime) {
            continue;
        }
        $url = sprintf(
            'https://raw.githubusercontent.com/%s/%s/%s/%s',
            GITHUB_REPO_OWNER,
            GITHUB_REPO_NAME,
            $branch,
            $path
        );
        $data = fetchRemoteFile($url);
        if ($data === null) {
            return null;
        }
        if (is_file($localPath) && sha1_file($localPath) === sha1($data)) {
            touch($localPath, $remoteTime);
            continue;
        }
        $dir = dirname($localPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($localPath, $data);
        touch($localPath, $remoteTime);
        $updated[] = $relative;
    }
    return $updated;
}

$message = '';
$messageType = '';
$updatedFiles = [];
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
        $localVersion = getLocalVersion();
        if ($remoteVersion === null) {
            $message = 'Unable to retrieve remote version.';
            $messageType = 'error';
        } elseif ($localVersion === null) {
            $message = 'Unable to determine local version.';
            $messageType = 'error';
        } elseif (version_compare($remoteVersion, $localVersion, '<=')) {
            $message = 'Already up to date.';
            $messageType = 'success';
        } else {
            $targetDir = dirname(__DIR__);
            $backupDir = __DIR__ . '/data/backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            $backupFile = $backupDir . '/site-backup-' . date('YmdHis') . '.zip';

            if (createBackup($targetDir, $backupFile)) {
                $updatedFiles = updateFromBranchFiles($branch, $targetDir);
                if ($updatedFiles === null) {
                    $message = 'Failed to download update files.';
                    $messageType = 'error';
                } else {
                    $message = 'Update to version ' . $remoteVersion . ' completed successfully.';
                    $messageType = 'success';
                }
            } else {
                $message = 'Backup failed. Update aborted.';
                $messageType = 'error';
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
            <?php if ($updatedFiles): ?>
                <div class="update-log">
                    <h2>Updated Files</h2>
                    <pre><?= e(implode("\n", $updatedFiles)) ?></pre>
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

