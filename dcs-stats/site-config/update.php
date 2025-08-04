<?php
/**
 * System Update Page
 * Allows fetching and installing GitHub branches
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';

// Require admin login and permission
requireAdmin();
requirePermission('change_settings');

$currentAdmin = getCurrentAdmin();

$repoOwner = 'Penfold-88';
$repoName  = 'DCS-Statistics-Dashboard';
$exceptions = [
    '.git',
    'dcs-stats/site-config/config.php',
    'dcs-stats/site-config/data'
];

// Stream update process via Server-Sent Events
if (isset($_GET['stream'])) {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');

    function sendLog($msg) {
        echo 'data: ' . str_replace("\n", "\ndata: ", $msg) . "\n\n";
        @ob_flush();
        @flush();
    }

    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        sendLog('Invalid CSRF token');
        sendLog('done');
        exit;
    }

    $branch = $_GET['branch'] === 'Dev' ? 'Dev' : 'main';
    $doBackup = isset($_GET['backup']) && $_GET['backup'] == '1';

    $rootDir = realpath(__DIR__ . '/../..');
    $upgradeDir = $rootDir . '/UPGRADE';
    if (!is_dir($upgradeDir)) {
        mkdir($upgradeDir, 0755, true);
    }

    if ($doBackup) {
        $backupFile = $rootDir . '/backup_' . date('Ymd_His') . '.zip';
        sendLog('Creating backup...');
        $zip = new ZipArchive();
        if ($zip->open($backupFile, ZipArchive::CREATE) === true) {
            $iter = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootDir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iter as $file) {
                $filePath = $file->getPathname();
                $relative = substr($filePath, strlen($rootDir) + 1);
                if (strpos($relative, 'UPGRADE') === 0 || strpos($relative, 'backup_') === 0) continue;
                if ($file->isDir()) {
                    $zip->addEmptyDir($relative);
                } else {
                    $zip->addFile($filePath, $relative);
                }
            }
            $zip->close();
            sendLog('Backup created: ' . basename($backupFile));
        } else {
            sendLog('Failed to create backup');
        }
    }

    // Download branch zip via GitHub API
    $zipUrl = "https://api.github.com/repos/$repoOwner/$repoName/zipball/$branch";
    $zipFile = $upgradeDir . '/update.zip';
    $ch = curl_init($zipUrl);
    $fp = fopen($zipFile, 'w');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Statistics-Updater');
    sendLog('Downloading ' . $branch . ' branch...');
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    sendLog('Download complete');

    // Extract archive
    $zip = new ZipArchive();
    if ($zip->open($zipFile) === true) {
        $zip->extractTo($upgradeDir);
        $rootFolder = $zip->getNameIndex(0);
        $zip->close();
        sendLog('Extraction complete');
    } else {
        sendLog('Failed to extract archive');
        sendLog('done');
        exit;
    }

    $extractPath = $upgradeDir . '/' . $rootFolder;

    // Gather file list from new branch
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($extractPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    $newFiles = [];
    foreach ($iter as $file) {
        $newFiles[] = substr($file->getPathname(), strlen($extractPath) + 1);
    }

    // Remove files not present in new branch
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iter as $file) {
        $relative = substr($file->getPathname(), strlen($rootDir) + 1);
        if (strpos($relative, 'UPGRADE') === 0 || strpos($relative, 'backup_') === 0) continue;
        if (in_array($relative, $exceptions)) continue;
        if (!in_array($relative, $newFiles)) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
                sendLog('Removed directory ' . $relative);
            } else {
                unlink($file->getPathname());
                sendLog('Removed file ' . $relative);
            }
        }
    }

    // Copy new files into place
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($extractPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iter as $file) {
        $relative = substr($file->getPathname(), strlen($extractPath) + 1);
        if (in_array($relative, $exceptions)) continue;
        $dest = $rootDir . '/' . $relative;
        if ($file->isDir()) {
            if (!is_dir($dest)) {
                mkdir($dest, 0755, true);
                sendLog('Created directory ' . $relative);
            }
        } else {
            copy($file->getPathname(), $dest);
            sendLog('Updated file ' . $relative);
        }
    }

    // Cleanup
    $cleanup = function($dir) use (&$cleanup) {
        if (!is_dir($dir)) return;
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = "$dir/$item";
            if (is_dir($path)) {
                $cleanup($path);
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    };
    $cleanup($upgradeDir);
    @rmdir($upgradeDir);
    sendLog('Cleanup complete');
    sendLog('Update complete');
    sendLog('done');
    exit;
}

$pageTitle = 'System Update';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        #update-log {
            height: 300px;
            overflow-y: auto;
            background: var(--bg-tertiary);
            padding: 10px;
            border-radius: 4px;
            white-space: pre-wrap;
        }
    </style>
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
            <form id="update-form">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="branch">Select Branch</label>
                    <select name="branch" id="branch" class="form-control">
                        <option value="main">main</option>
                        <option value="Dev">Dev</option>
                    </select>
                </div>
                <div id="dev-warning" class="alert alert-error" style="display:none;">
                    This is not a stable release and may break your Dashboard.
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="backup" value="1"> Create Backup First</label>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" id="start-update">Start Update</button>
                </div>
            </form>
            <pre id="update-log" class="mt-2"></pre>
        </div>
    </main>
</div>
<script>
const branchSelect = document.getElementById('branch');
branchSelect.addEventListener('change', function(){
    document.getElementById('dev-warning').style.display = this.value === 'Dev' ? 'block' : 'none';
});

document.getElementById('start-update').addEventListener('click', function(){
    const branch = document.getElementById('branch').value;
    const backup = document.querySelector('[name="backup"]').checked ? 1 : 0;
    const token = document.querySelector('input[name="csrf_token"]').value;
    const log = document.getElementById('update-log');
    log.textContent = '';
    const source = new EventSource(`update.php?stream=1&branch=${encodeURIComponent(branch)}&backup=${backup}&csrf_token=${encodeURIComponent(token)}`);
    source.onmessage = function(e){
        if (e.data === 'done') {
            source.close();
        } else {
            log.textContent += e.data + "\n";
            log.scrollTop = log.scrollHeight;
        }
    };
});
</script>
</body>
</html>
