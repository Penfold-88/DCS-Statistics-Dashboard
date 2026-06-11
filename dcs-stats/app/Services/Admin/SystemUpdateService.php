<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateService
{
    private const PRESERVED_PATHS = [
        'site-config/data',
        'data',
        'backups',
        'UPGRADE',
        'api_config.json',
        'site_config.json',
        '.version_meta.json',
        'custom_theme.css',
        'header_custom.css',
        'menu_config.json',
        'site-config/theme_backups',
        '.env',
        '.dev',
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
        'uploads',
        'custom',
        'logs',
        '.git',
        '.gitignore',
        '.gitattributes',
    ];

    public function run(?string $specificVersion, callable $log): void
    {
        require_once DCS_ROOT_PATH . '/site-config/admin_functions.php';
        require_once DCS_ROOT_PATH . '/site-config/update_channel.php';
        require_once DCS_ROOT_PATH . '/site-config/version_tracker.php';

        if (!class_exists('ZipArchive')) {
            $log('Update cancelled: PHP ZipArchive is not available.');
            $log('Enable the PHP zip extension, then restart Apache and try again.');
            $log('For XAMPP, open php.ini, enable extension=zip, save, and restart Apache.');
            return;
        }

        $channelConfig = getUpdateChannelConfig();
        $branch = $channelConfig['branch'];
        $repo = $channelConfig['repo'];
        $currentVersion = defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : '1.0.0';
        $currentVersionInfo = getCurrentVersionInfo();
        $currentBuildLabel = $currentVersionInfo['version'] ?? $currentVersion;

        $log("Current version: $currentBuildLabel");
        $log("Update channel: {$channelConfig['channel']}");
        $log("GitHub branch: $branch");

        $apiUrl = "https://api.github.com/repos/$repo/zipball/$branch";
        if ($specificVersion !== null) {
            $apiUrl = "https://api.github.com/repos/$repo/zipball/$specificVersion";
            $log("Downloading specific version: $specificVersion");
        } else {
            $log("Downloading latest from branch: $branch");
        }

        $rootPath = DCS_ROOT_PATH;
        $upgradeParentDir = $rootPath . '/UPGRADE';
        $upgradeDir = $upgradeParentDir . '/update-' . bin2hex(random_bytes(8));
        $backupDir = $rootPath . '/backups';

        if (!is_dir($upgradeParentDir)) {
            mkdir($upgradeParentDir, 0755, true);
        }
        mkdir($upgradeDir, 0755, true);
        register_shutdown_function(function () use ($upgradeDir) {
            if (is_dir($upgradeDir)) {
                $this->removeDirectory($upgradeDir);
            }
        });

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $remoteInfo = $this->resolveRemoteBranch($repo, $branch, $specificVersion, $apiUrl, $log);
        if ($remoteInfo === null) {
            return;
        }

        $branch = $remoteInfo['branch'];
        $apiUrl = $remoteInfo['api_url'];
        $remoteCommitSha = $remoteInfo['commit_sha'];
        $remoteCommitDate = $remoteInfo['commit_date'];

        $this->logLatestReleaseForSpecificVersion($repo, $specificVersion, $log);
        $this->backupConfigurationFiles($rootPath, $backupDir, $log);

        $downloadLabel = $specificVersion ? "version $specificVersion" : "{$channelConfig['channel']} branch ($branch)";
        $log("Downloading $downloadLabel...");
        $zipFile = $upgradeDir . '/update.zip';
        if (!$this->downloadArchive($apiUrl, $zipFile, $log)) {
            return;
        }

        $newCodeDir = $this->extractArchive($zipFile, $upgradeDir, $log);
        if ($newCodeDir === null) {
            return;
        }

        $this->createFullBackup($rootPath, $backupDir, $log);
        $this->applyNewCode($rootPath, $newCodeDir, $log);

        $log('Cleaning up...');
        $this->removeDirectory($upgradeDir);

        $versionLabel = $specificVersion ?? $this->buildVersionLabel($branch, $remoteCommitDate, $remoteCommitSha);
        $currentAdmin = getCurrentAdmin();
        updateVersionMetadata(
            $versionLabel,
            $branch,
            $currentAdmin['username'] ?? 'Unknown',
            $remoteCommitSha,
            $remoteCommitDate
        );

        $this->runInstallCheckin($log);
        $this->updateConfigVersion($specificVersion, $currentVersion, $log);

        logAdminAction('SYSTEM_UPDATE', [
            'from_version' => $currentVersion,
            'to_version' => $specificVersion ?? $versionLabel,
            'branch' => $branch,
            'admin' => $currentAdmin['username'] ?? 'Unknown',
        ]);

        $log('Update complete.');
        $log('Please refresh your browser to see the changes.');
    }

    private function resolveRemoteBranch(string $repo, string $branch, ?string $specificVersion, string $apiUrl, callable $log): ?array
    {
        $remoteCommitSha = null;
        $remoteCommitDate = null;

        if ($specificVersion !== null) {
            return [
                'branch' => $branch,
                'api_url' => $apiUrl,
                'commit_sha' => $remoteCommitSha,
                'commit_date' => $remoteCommitDate,
            ];
        }

        $log('Checking GitHub branch...');
        $branchResult = $this->fetchBranch($repo, $branch);

        if ($branchResult['http_code'] !== 200 || !$branchResult['data']) {
            $log("Selected branch was not found: $branch");
            if ($branch === 'master') {
                $log('Trying fallback branch: main');
                $branch = 'main';
                $apiUrl = "https://api.github.com/repos/$repo/zipball/$branch";
                $branchResult = $this->fetchBranch($repo, $branch);
            }
        }

        if ($branchResult['http_code'] !== 200) {
            $log('Could not find a usable GitHub branch. Update cancelled.');
            $log('HTTP Code: ' . $branchResult['http_code']);
            if (!empty($branchResult['error'])) {
                $log('Connection Error: ' . $branchResult['error']);
            }
            return null;
        }

        if ($branchResult['data']) {
            $branchInfo = json_decode($branchResult['data'], true);
            $remoteCommitSha = $branchInfo['commit']['sha'] ?? null;
            $remoteCommitDate = $branchInfo['commit']['commit']['committer']['date'] ?? null;
            if ($remoteCommitSha) {
                $log('Latest branch commit: ' . substr($remoteCommitSha, 0, 12));
            }
            if ($remoteCommitDate) {
                $log('Latest branch date: ' . date('Y-m-d H:i:s', strtotime($remoteCommitDate)));
            }
        }

        return [
            'branch' => $branch,
            'api_url' => $apiUrl,
            'commit_sha' => $remoteCommitSha,
            'commit_date' => $remoteCommitDate,
        ];
    }

    private function fetchBranch(string $repo, string $branch): array
    {
        $branchUrl = "https://api.github.com/repos/$repo/branches/" . rawurlencode($branch);
        $ch = curl_init($branchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        $data = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'data' => $data,
            'error' => $error,
            'http_code' => $httpCode,
        ];
    }

    private function logLatestReleaseForSpecificVersion(string $repo, ?string $specificVersion, callable $log): void
    {
        if ($specificVersion === null) {
            return;
        }

        $log('Checking release information...');
        $releaseUrl = "https://api.github.com/repos/$repo/releases/latest";
        $ch = curl_init($releaseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        $releaseData = curl_exec($ch);
        curl_close($ch);

        if ($releaseData) {
            $release = json_decode($releaseData, true);
            if (isset($release['tag_name'])) {
                $log('Latest release: ' . $release['tag_name']);
            }
        }
    }

    private function backupConfigurationFiles(string $rootPath, string $backupDir, callable $log): void
    {
        $log('Backing up configuration files...');
        $configBackupDir = $backupDir . '/config-backup-' . date('Ymd-His');
        if (!is_dir($configBackupDir)) {
            mkdir($configBackupDir, 0755, true);
        }

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
            '/docker/Dockerfile.simple',
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
                    $log("Backed up: $file");
                } else {
                    $log("Failed to backup: $file");
                }
            }
        }

        $log("Config backup complete: $configBackupDir");
    }

    private function downloadArchive(string $apiUrl, string $zipFile, callable $log): bool
    {
        $ch = curl_init($apiUrl);
        $fp = fopen($zipFile, 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DCS-Stats-Updater');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.github.v3+json',
        ]);
        $download = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if ($download === false || $httpCode !== 200) {
            $log('Download failed. HTTP Code: ' . $httpCode);
            @unlink($zipFile);
            return false;
        }

        $log('Download complete.');
        return true;
    }

    private function extractArchive(string $zipFile, string $upgradeDir, callable $log): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            $log('Failed to open zip archive');
            return null;
        }

        if (!$this->validateUpdateZip($zip)) {
            $zip->close();
            @unlink($zipFile);
            $log('Update cancelled: downloaded archive contains unsafe file paths.');
            return null;
        }

        $zip->extractTo($upgradeDir);
        $zip->close();
        $log('Extraction complete.');

        $extractedDirs = glob($upgradeDir . '/*', GLOB_ONLYDIR);
        if (empty($extractedDirs)) {
            $log('No extracted directory found');
            return null;
        }

        $newCodeDir = $extractedDirs[0] . '/dcs-stats';
        if (!is_dir($newCodeDir)) {
            $log('dcs-stats directory not found in archive');
            return null;
        }

        return $newCodeDir;
    }

    private function createFullBackup(string $rootPath, string $backupDir, callable $log): void
    {
        $backupFile = $backupDir . '/backup-' . date('Ymd-His') . '.zip';
        $log('Creating backup...');
        $backupZip = new \ZipArchive();
        if ($backupZip->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $relPath = $this->normalizePath(substr($filePath, strlen($rootPath) + 1));
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
            $log('Backup saved to ' . $backupFile);
            $this->cleanupOldBackups($backupDir, 5, $log);
        } else {
            $log('Failed to create backup');
        }
    }

    private function applyNewCode(string $rootPath, string $newCodeDir, callable $log): void
    {
        $newFiles = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($newCodeDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relPath = $this->normalizePath(substr($filePath, strlen($newCodeDir) + 1));
            $targetPath = $rootPath . '/' . $relPath;
            $newFiles[] = $relPath;

            if ($this->shouldPreserve($relPath)) {
                $log('Preserving: ' . $relPath);
                continue;
            }

            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                    $log('Created directory: ' . $relPath);
                }
                continue;
            }

            if (!is_dir(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0755, true);
            }

            if (file_exists($targetPath) && in_array(basename($targetPath), ['api_config.json', 'site_config.json', '.env', 'users.json'])) {
                $log('Skipping config file: ' . $relPath);
                continue;
            }

            copy($filePath, $targetPath);
            $log('Updated file: ' . $relPath);
        }

        $this->removeOldFiles($rootPath, $newFiles, $log);
    }

    private function removeOldFiles(string $rootPath, array $newFiles, callable $log): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relPath = $this->normalizePath(substr($filePath, strlen($rootPath) + 1));

            if ($this->shouldPreserve($relPath)) {
                continue;
            }

            if (!in_array($relPath, $newFiles)) {
                if ($file->isDir()) {
                    rmdir($filePath);
                    $log('Removed directory: ' . $relPath);
                } else {
                    unlink($filePath);
                    $log('Removed file: ' . $relPath);
                }
            }
        }
    }

    private function runInstallCheckin(callable $log): void
    {
        $checkinFile = DCS_ROOT_PATH . '/install_checkin.php';
        if (!file_exists($checkinFile)) {
            return;
        }

        require_once $checkinFile;
        $checkinResult = runInstallCheckinIfDue(
            getCurrentVersionInfo(),
            getUpdateChannelConfig(),
            ['event' => 'update', 'force' => true]
        );
        $log('Install check-in after update: ' . ($checkinResult['status'] ?? 'unknown'));
    }

    private function updateConfigVersion(?string $newVersion, string $currentVersion, callable $log): void
    {
        if ($newVersion === null || $newVersion === $currentVersion) {
            return;
        }

        $configFile = DCS_ROOT_PATH . '/site-config/config.php';
        if (!file_exists($configFile)) {
            return;
        }

        $config = file_get_contents($configFile);
        $config = preg_replace(
            "/define\('ADMIN_PANEL_VERSION', '[^']+'/",
            "define('ADMIN_PANEL_VERSION', '$newVersion'",
            $config
        );
        file_put_contents($configFile, $config);
        $log("Updated version to: $newVersion");
    }

    private function validateUpdateZip(\ZipArchive $zip): bool
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$this->isSafeUpdateZipPath((string)$name)) {
                return false;
            }
        }

        return true;
    }

    private function isSafeUpdateZipPath(string $path): bool
    {
        $path = $this->normalizePath($path);

        return $path !== '' &&
            $path[0] !== '/' &&
            strpos($path, '../') === false &&
            strpos($path, '/..') === false &&
            strpos($path, ':') === false;
    }

    private function buildVersionLabel(string $branch, ?string $commitDate, ?string $commitSha): string
    {
        $date = $commitDate ? date('Y-m-d', strtotime($commitDate)) : date('Y-m-d');
        $shortSha = $commitSha ? substr($commitSha, 0, 12) : 'unknown';

        return $branch . ' @ ' . $date . ' #' . $shortSha;
    }

    private function shouldPreserve(string $relPath): bool
    {
        foreach (self::PRESERVED_PATHS as $path) {
            if ($relPath === $path || strpos($relPath, $path . '/') === 0) {
                return true;
            }
        }

        return false;
    }

    private function cleanupOldBackups(string $backupDir, int $maxBackups, callable $log): void
    {
        if (!is_dir($backupDir)) {
            return;
        }

        $backups = glob($backupDir . '/backup-*.zip');
        if (!$backups || count($backups) <= $maxBackups) {
            return;
        }

        usort($backups, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $deleted = 0;
        for ($i = $maxBackups; $i < count($backups); $i++) {
            if (unlink($backups[$i])) {
                $deleted++;
                $log('Deleted old backup: ' . basename($backups[$i]));
            }
        }

        if ($deleted > 0) {
            $log("Cleaned up $deleted old backup(s). Keeping $maxBackups most recent.");
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
