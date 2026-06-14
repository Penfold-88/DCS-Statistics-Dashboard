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

    private AdminFilesystemService $filesystem;
    private SystemUpdateGitHubClient $githubClient;

    public function __construct(?AdminFilesystemService $filesystem = null, ?SystemUpdateGitHubClient $githubClient = null)
    {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
        $this->githubClient = $githubClient ?? new SystemUpdateGitHubClient();
    }

    public function run(?string $specificVersion, callable $log): void
    {
        \DcsStats\Core\AdminBootstrap::panel();
        \DcsStats\Core\SupportBootstrap::updateChannel();
        \DcsStats\Core\SupportBootstrap::versionTracker();

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
                $this->filesystem->removeDirectory($upgradeDir);
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
        $this->filesystem->removeDirectory($upgradeDir);

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
        $branchResult = $this->githubClient->fetchBranch($repo, $branch);

        if ($branchResult['http_code'] !== 200 || !$branchResult['data']) {
            $log("Selected branch was not found: $branch");
            if ($branch === 'master') {
                $log('Trying fallback branch: main');
                $branch = 'main';
                $apiUrl = "https://api.github.com/repos/$repo/zipball/$branch";
                $branchResult = $this->githubClient->fetchBranch($repo, $branch);
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

    private function logLatestReleaseForSpecificVersion(string $repo, ?string $specificVersion, callable $log): void
    {
        if ($specificVersion === null) {
            return;
        }

        $log('Checking release information...');
        $latestReleaseTag = $this->githubClient->latestReleaseTag($repo);
        if ($latestReleaseTag !== null) {
            $log('Latest release: ' . $latestReleaseTag);
        }
    }

    private function backupConfigurationFiles(string $rootPath, string $backupDir, callable $log): void
    {
        $log('Backing up configuration files...');
        $configBackupDir = $backupDir . '/config-backup-' . date('Ymd-His');
        if (!is_dir($configBackupDir)) {
            mkdir($configBackupDir, 0755, true);
        }

        foreach (BackupFileCatalog::configurationFiles() as $file) {
            $sourcePath = $rootPath . '/' . $file;
            if (file_exists($sourcePath)) {
                $destPath = $configBackupDir . '/' . $file;
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                if (copy($sourcePath, $destPath)) {
                    $log("Backed up: /$file");
                } else {
                    $log("Failed to backup: /$file");
                }
            }
        }

        $log("Config backup complete: $configBackupDir");
    }

    private function downloadArchive(string $apiUrl, string $zipFile, callable $log): bool
    {
        $download = $this->githubClient->downloadArchive($apiUrl, $zipFile);
        if (!$download['success']) {
            $log('Download failed. HTTP Code: ' . $download['http_code']);
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

        if (!$this->filesystem->archiveHasSafePaths($zip)) {
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
                $relPath = $this->filesystem->normalizePath(substr($filePath, strlen($rootPath) + 1));
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
            $relPath = $this->filesystem->normalizePath(substr($filePath, strlen($newCodeDir) + 1));
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
            $relPath = $this->filesystem->normalizePath(substr($filePath, strlen($rootPath) + 1));

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
        \DcsStats\Core\SupportBootstrap::installCheckin();
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

        $configFile = DCS_ROOT_PATH . '/app/Core/AdminConfig.php';
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

    private function buildVersionLabel(string $branch, ?string $commitDate, ?string $commitSha): string
    {
        $date = $commitDate ? date('Y-m-d', strtotime($commitDate)) : date('Y-m-d');
        $shortSha = $commitSha ? substr($commitSha, 0, 12) : 'unknown';

        return $branch . ' @ ' . $date . ' #' . $shortSha;
    }

    private function shouldPreserve(string $relPath): bool
    {
        return $this->filesystem->shouldPreservePath($relPath, self::PRESERVED_PATHS);
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

}
