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
 * Perform update by extracting provided archive into target directory.
 *
 * @param string $archivePath Path to update archive
 * @param string $targetDir Directory to extract to
 * @return bool True on success, false on failure
 */
function performUpdate(string $archivePath, string $targetDir): bool {
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($archivePath) === true) {
            $zip->extractTo($targetDir);
            $zip->close();
            return true;
        }
        echo "Error: Unable to open update archive.";
        return false;
    }

    // ZipArchive is unavailable – advise admin and attempt fallback
    echo "Warning: PHP ZipArchive extension not installed. " .
         "Consider enabling it via php.ini or installing php-zip. " .
         "Attempting fallback extraction...";

    // Fallback 1: PharData
    if (class_exists('PharData')) {
        try {
            $phar = new PharData($archivePath);
            $phar->extractTo($targetDir, null, true);
            return true;
        } catch (Exception $e) {
            // Continue to next fallback
        }
    }

    // Fallback 2: system unzip command
    $unzip = trim(shell_exec('command -v unzip'));
    if ($unzip) {
        $cmd = escapeshellcmd($unzip) . ' ' . escapeshellarg($archivePath) . ' -d ' . escapeshellarg($targetDir);
        exec($cmd, $output, $retval);
        if ($retval === 0) {
            return true;
        }
    }

    echo "\nError: No suitable archive extraction method available. Update aborted.";
    return false;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = ERROR_MESSAGES['csrf_invalid'];
        $messageType = 'error';
    } elseif (!isset($_FILES['update_archive']) || $_FILES['update_archive']['error'] !== UPLOAD_ERR_OK) {
        $message = 'File upload failed.';
        $messageType = 'error';
    } else {
        $archivePath = $_FILES['update_archive']['tmp_name'];
        $targetDir = dirname(__DIR__);

        $backupDir = __DIR__ . '/data/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $backupFile = $backupDir . '/site-backup-' . date('YmdHis') . '.zip';

        if (createBackup($targetDir, $backupFile)) {
            if (performUpdate($archivePath, $targetDir)) {
                $message = 'Update completed successfully.';
                $messageType = 'success';
            } else {
                $message = 'Update failed. See errors above.';
                $messageType = 'error';
            }
        } else {
            $message = 'Backup failed. Update aborted.';
            $messageType = 'error';
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
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="update_archive">Update Archive (.zip)</label>
                    <input type="file" name="update_archive" id="update_archive" class="form-control" required>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Run Update</button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>

