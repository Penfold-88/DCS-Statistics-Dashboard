<?php
/**
 * Site Auto Updater
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';

requireAdmin();
requirePermission('change_settings');

$currentAdmin = getCurrentAdmin();
$pageTitle = 'Auto Updater';

// Repository info
$repoOwner = 'CptExpendable';
$repoName  = 'DCS-Statistics-Dashboard';

function getBranchInfo($owner, $repo, $branch) {
    $url = "https://api.github.com/repos/$owner/$repo/commits/$branch";
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: DCS-Updater'
            ]
        ]
    ];
    $context = stream_context_create($opts);
    $data = @file_get_contents($url, false, $context);
    if (!$data) {
        return null;
    }
    $json = json_decode($data, true);
    return [
        'sha' => substr($json['sha'], 0, 7),
        'date' => date('Y-m-d H:i', strtotime($json['commit']['committer']['date'])),
    ];
}

function createBackup($sourceDir, $zipFile) {
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }
    $sourceDir = realpath($sourceDir);
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($files as $file) {
        $filePath = $file->getPathname();
        $localPath = substr($filePath, strlen($sourceDir) + 1);
        if (strpos($localPath, 'site-config/data/backups') === 0) {
            continue; // skip backups
        }
        if ($file->isDir()) {
            $zip->addEmptyDir($localPath);
        } else {
            $zip->addFile($filePath, $localPath);
        }
    }
    return $zip->close();
}

function removeDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }
    @rmdir($dir);
}

function performUpdate($branch, $targetDir, $owner, $repo) {
    $tempDir = sys_get_temp_dir() . '/dcs_update_' . uniqid();
    if (!mkdir($tempDir, 0777, true)) {
        return false;
    }
    $zipUrl = "https://github.com/$owner/$repo/archive/refs/heads/$branch.zip";
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: DCS-Updater'
            ]
        ]
    ];
    $zipData = @file_get_contents($zipUrl, false, stream_context_create($opts));
    if (!$zipData) {
        removeDirectory($tempDir);
        return false;
    }
    $zipPath = $tempDir . '/branch.zip';
    file_put_contents($zipPath, $zipData);
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        removeDirectory($tempDir);
        return false;
    }
    $zip->extractTo($tempDir);
    $zip->close();
    $extracted = glob($tempDir . '/' . $repo . '*', GLOB_ONLYDIR);
    if (!$extracted) {
        removeDirectory($tempDir);
        return false;
    }
    $srcDir = $extracted[0];

    $newFiles = [];
    $preservePaths = [
        'site-config/config.php',
        'site-config/data',
    ];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $item) {
        $srcPath = $item->getPathname();
        $relPath = substr($srcPath, strlen($srcDir) + 1);
        $destPath = $targetDir . '/' . $relPath;
        foreach ($preservePaths as $preserve) {
            if (strpos($relPath, $preserve) === 0) {
                $newFiles[] = $relPath;
                continue 2;
            }
        }
        $newFiles[] = $relPath;
        if ($item->isDir()) {
            if (!is_dir($destPath)) {
                mkdir($destPath, 0755, true);
            }
        } else {
            if (!is_dir(dirname($destPath))) {
                mkdir(dirname($destPath), 0755, true);
            }
            if (!file_exists($destPath) || filemtime($srcPath) > filemtime($destPath)) {
                copy($srcPath, $destPath);
            }
        }
    }

    $existing = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($existing as $item) {
        $relPath = substr($item->getPathname(), strlen($targetDir) + 1);
        if (in_array($relPath, $newFiles)) {
            continue;
        }
        if (strpos($relPath, 'site-config/data') === 0) {
            continue; // preserve data
        }
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }

    removeDirectory($tempDir);
    return true;
}

$latestMaster = getBranchInfo($repoOwner, $repoName, 'master');
$latestDev    = getBranchInfo($repoOwner, $repoName, 'development');

$rootDir = realpath(__DIR__ . '/..');
$currentVersion = trim(@shell_exec('git -C ' . escapeshellarg($rootDir) . ' rev-parse --short HEAD'));

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = ERROR_MESSAGES['csrf_invalid'];
        $messageType = 'error';
    } else {
        $branch = $_POST['branch'] === 'dev' ? 'development' : 'master';
        $backupDir = __DIR__ . '/data/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $backupFile = $backupDir . '/backup-' . date('Ymd-His') . '.zip';
        if (createBackup($rootDir, $backupFile)) {
            if (performUpdate($branch, $rootDir, $repoOwner, $repoName)) {
                $message = 'Update completed successfully.';
                $messageType = 'success';
            } else {
                $message = 'Update failed. Backup saved to ' . basename($backupFile);
                $messageType = 'error';
            }
        } else {
            $message = 'Backup failed - update aborted.';
            $messageType = 'error';
        }
    }
}
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
            <div class="alert alert-info">
                Current version: <?= e($currentVersion ?: 'unknown') ?>
            </div>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Update Site</h2>
                </div>
                <div class="card-content">
                    <p>A backup of the current site will be created before updating.</p>
                    <div class="alert alert-warning">
                        Development branch is not recommended for use on live websites.
                    </div>
                    <div class="alert alert-warning">
                        Deprecated files will be removed during the update process.
                    </div>
                    <form method="POST">
                        <?= csrfField() ?>
                        <div class="form-group">
                            <label for="branch">Select Branch</label>
                            <select name="branch" id="branch" class="form-control">
                                <option value="master">Master (<?= $latestMaster ? e($latestMaster['sha']) : 'n/a' ?>)</option>
                                <option value="dev">Development (<?= $latestDev ? e($latestDev['sha']) : 'n/a' ?>)</option>
                            </select>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Backup &amp; Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
