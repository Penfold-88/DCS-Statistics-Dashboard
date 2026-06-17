<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateService
{
    private AdminFilesystemService $filesystem;
    private SystemUpdateGitHubClient $githubClient;
    private SystemUpdateBackupService $backupService;
    private SystemUpdateDeploymentService $deploymentService;
    private SystemUpdateArchiveService $archiveService;
    private SystemUpdateRemoteService $remoteService;

    public function __construct(
        ?AdminFilesystemService $filesystem = null,
        ?SystemUpdateGitHubClient $githubClient = null,
        ?SystemUpdateBackupService $backupService = null,
        ?SystemUpdateDeploymentService $deploymentService = null,
        ?SystemUpdateArchiveService $archiveService = null,
        ?SystemUpdateRemoteService $remoteService = null
    ) {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
        $this->githubClient = $githubClient ?? new SystemUpdateGitHubClient();
        $this->backupService = $backupService ?? new SystemUpdateBackupService($this->filesystem);
        $this->deploymentService = $deploymentService ?? new SystemUpdateDeploymentService($this->filesystem);
        $this->archiveService = $archiveService ?? new SystemUpdateArchiveService($this->filesystem);
        $this->remoteService = $remoteService ?? new SystemUpdateRemoteService($this->githubClient);
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

        $remoteInfo = $this->remoteService->resolveRemoteBranch($repo, $branch, $specificVersion, $apiUrl, $log);
        if ($remoteInfo === null) {
            return;
        }

        $branch = $remoteInfo['branch'];
        $apiUrl = $remoteInfo['api_url'];
        $remoteCommitSha = $remoteInfo['commit_sha'];
        $remoteCommitDate = $remoteInfo['commit_date'];

        $this->remoteService->logLatestReleaseForSpecificVersion($repo, $specificVersion, $log);
        $this->backupService->backupConfigurationFiles($rootPath, $backupDir, $log);

        $downloadLabel = $specificVersion ? "version $specificVersion" : "{$channelConfig['channel']} branch ($branch)";
        $log("Downloading $downloadLabel...");
        $zipFile = $upgradeDir . '/update.zip';
        if (!$this->downloadArchive($apiUrl, $zipFile, $log)) {
            return;
        }

        $newCodeDir = $this->archiveService->extractArchive($zipFile, $upgradeDir, $log);
        if ($newCodeDir === null) {
            return;
        }

        $this->backupService->createFullBackup($rootPath, $backupDir, $log);
        $this->deploymentService->applyNewCode($rootPath, $newCodeDir, $log);

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

}
