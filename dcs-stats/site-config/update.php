<?php
/**
 * Dashboard Update Page
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';

// Require admin login and permission
requireAdmin();
requirePermission('change_settings');

$currentAdmin = getCurrentAdmin();

$exceptions = [
    'site-config/config.php',
    'site-config/data',
    'site-config/logs',
    'UPGRADE',
    '.git',
    '.gitignore',
    '.env',
];

$repoRoot = realpath(__DIR__ . '/../..');
$upgradeDir = $repoRoot . '/UPGRADE';

function rrmdir($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }
    rmdir($dir);
}

if (isset($_GET['run'])) {
    header('Content-Type: text/plain');
    ob_implicit_flush(true);

    $log = function($msg) {
        echo $msg . "\n";
    };

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $log('Invalid request method');
        exit;
    }

    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $log('Invalid CSRF token');
        exit;
    }

    $branch = $_POST['branch'] === 'dev' ? 'dev' : 'main';
    $doBackup = isset($_POST['backup']);

    $repoUrl = trim(shell_exec('git config --get remote.origin.url'));
    if ($repoUrl && preg_match('#github.com[:/](.+?)/(.+?)(?:\.git)?$#', $repoUrl, $matches)) {
        $owner = $matches[1];
        $repo = $matches[2];
    } elseif (defined('GITHUB_OWNER') && defined('GITHUB_REPO') && GITHUB_OWNER && GITHUB_REPO) {
        $owner = GITHUB_OWNER;
        $repo = GITHUB_REPO;
    } else {
        $log('Could not determine repository - set GITHUB_OWNER and GITHUB_REPO in config.php');
        exit;
    }

    $apiUrl = "https://api.github.com/repos/$owner/$repo/zipball/$branch";
    $log("Downloading $branch branch...");

    if (is_dir($upgradeDir)) {
        rrmdir($upgradeDir);
    }
    mkdir($upgradeDir, 0755, true);
    $zipFile = $upgradeDir . "/$branch.zip";

    $fp = fopen($zipFile, 'w');
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
    curl_exec($ch);
    if (curl_errno($ch)) {
        $log('Download error: ' . curl_error($ch));
        fclose($fp);
        exit;
    }
    curl_close($ch);
    fclose($fp);
    $log('Download complete');

    $zip = new ZipArchive();
    if ($zip->open($zipFile) === true) {
        $zip->extractTo($upgradeDir);
        $zip->close();
    } else {
        $log('Failed to extract archive');
        exit;
    }
    $log('Extraction complete');

    $dirs = glob($upgradeDir . '/*', GLOB_ONLYDIR);
    $extractRoot = $dirs[0] ?? '';
    if ($extractRoot === '') {
        $log('Cannot locate extracted directory');
        exit;
    }

    if ($doBackup) {
        $backupFile = $repoRoot . '/backup_' . date('Ymd_His') . '.zip';
        $log('Creating backup...');
        $backupZip = new ZipArchive();
        if ($backupZip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($repoRoot, RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($files as $file) {
                $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($repoRoot) + 1));
                if (in_array($rel, $exceptions)) {
                    continue;
                }
                $backupZip->addFile($file->getPathname(), $rel);
            }
            $backupZip->close();
            $log('Backup saved to ' . basename($backupFile));
        } else {
            $log('Failed to create backup');
        }
    }

    $listFiles = function($base) use ($exceptions) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            $path = str_replace('\\', '/', substr($file->getPathname(), strlen($base) + 1));
            if (in_array($path, $exceptions)) {
                continue;
            }
            $files[] = $path;
        }
        return $files;
    };

    $branchFiles = $listFiles($extractRoot);
    $currentFiles = $listFiles($repoRoot);

    foreach ($currentFiles as $file) {
        if (!in_array($file, $branchFiles)) {
            $full = $repoRoot . '/' . $file;
            if (is_dir($full)) {
                rrmdir($full);
            } else {
                unlink($full);
            }
            $log('Removed ' . $file);
        }
    }

    $copyDir = function($src, $dst) use (&$copyDir) {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir("$src/$file")) {
                $copyDir("$src/$file", "$dst/$file");
            } else {
                copy("$src/$file", "$dst/$file");
            }
        }
        closedir($dir);
    };

    $log('Copying files...');
    $copyDir($extractRoot, $repoRoot);
    $log('Update complete');

    rrmdir($upgradeDir);
    $log('Cleaned up');
    exit;
}

$pageTitle = 'Update Dashboard';
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
            <form id="updateForm" method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="branch">Select Branch</label>
                    <select name="branch" id="branch" class="form-control">
                        <option value="main">Main</option>
                        <option value="dev">Dev</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="backup" value="1"> Create backup before updating</label>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
            <div class="card mt-2">
                <div class="card-header">
                    <h2 class="card-title">Update Log</h2>
                </div>
                <div class="card-content">
                    <pre id="log" style="height:300px; overflow:auto;"></pre>
                </div>
            </div>
        </div>
    </main>
</div>
<script>
document.getElementById('branch').addEventListener('change', function() {
    if (this.value === 'dev') {
        alert('Warning: Development branch may be unstable and could break your dashboard.');
    }
});

document.getElementById('updateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const logEl = document.getElementById('log');
    logEl.textContent = '';
    fetch('update.php?run=1', {
        method: 'POST',
        body: formData
    }).then(response => {
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        function read() {
            return reader.read().then(({done, value}) => {
                if (done) return;
                logEl.textContent += decoder.decode(value, {stream: true});
                logEl.scrollTop = logEl.scrollHeight;
                return read();
            });
        }
        return read();
    });
});
</script>
</body>
</html>
