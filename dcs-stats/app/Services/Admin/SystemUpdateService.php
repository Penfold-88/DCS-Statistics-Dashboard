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
    private SystemUpdateWorkspaceService $workspaceService;
    private SystemUpdateDownloadService $downloadService;
    private SystemUpdateFinalizer $finalizer;

    public function __construct(
        ?AdminFilesystemService $filesystem = null,
        ?SystemUpdateGitHubClient $githubClient = null,
        ?SystemUpdateBackupService $backupService = null,
        ?SystemUpdateDeploymentService $deploymentService = null,
        ?SystemUpdateArchiveService $archiveService = null,
        ?SystemUpdateRemoteService $remoteService = null,
        ?SystemUpdateWorkspaceService $workspaceService = null,
        ?SystemUpdateDownloadService $downloadService = null,
        ?SystemUpdateFinalizer $finalizer = null
    ) {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
        $this->githubClient = $githubClient ?? new SystemUpdateGitHubClient();
        $this->backupService = $backupService ?? new SystemUpdateBackupService($this->filesystem);
        $this->deploymentService = $deploymentService ?? new SystemUpdateDeploymentService($this->filesystem);
        $this->archiveService = $archiveService ?? new SystemUpdateArchiveService($this->filesystem);
        $this->remoteService = $remoteService ?? new SystemUpdateRemoteService($this->githubClient);
        $this->workspaceService = $workspaceService ?? new SystemUpdateWorkspaceService($this->filesystem);
        $this->downloadService = $downloadService ?? new SystemUpdateDownloadService($this->githubClient);
        $this->finalizer = $finalizer ?? new SystemUpdateFinalizer();
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
        $workspace = $this->workspaceService->prepare($rootPath);
        if ($workspace === null) {
            $log('Update cancelled: Could not create the update workspace.');
            return;
        }
        $upgradeDir = $workspace['upgrade_dir'];
        $backupDir = $workspace['backup_dir'];

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
        if (!$this->downloadService->download($apiUrl, $zipFile, $log)) {
            return;
        }

        $newCodeDir = $this->archiveService->extractArchive($zipFile, $upgradeDir, $log);
        if ($newCodeDir === null) {
            return;
        }

        $this->backupService->createFullBackup($rootPath, $backupDir, $log);
        $this->deploymentService->applyNewCode($rootPath, $newCodeDir, $log);

        $log('Cleaning up...');
        $this->workspaceService->cleanup($upgradeDir);

        $this->finalizer->complete(
            $specificVersion,
            $currentVersion,
            $branch,
            $remoteCommitSha,
            $remoteCommitDate,
            $log
        );

        $log('Update complete.');
        $log('Please refresh your browser to see the changes.');
    }

}
