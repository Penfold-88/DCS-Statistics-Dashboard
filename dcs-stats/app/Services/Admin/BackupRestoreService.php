<?php

namespace DcsStats\Services\Admin;

final class BackupRestoreService
{
    private AdminFilesystemService $filesystem;
    private PreRestoreBackupService $preRestoreBackupService;
    private BackupRestoreFileService $restoreFileService;

    public function __construct(
        ?AdminFilesystemService $filesystem = null,
        ?PreRestoreBackupService $preRestoreBackupService = null,
        ?BackupRestoreFileService $restoreFileService = null
    ) {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
        $this->preRestoreBackupService = $preRestoreBackupService ?? new PreRestoreBackupService($this->filesystem);
        $this->restoreFileService = $restoreFileService ?? new BackupRestoreFileService($this->filesystem);
    }

    public function restoreBackup(string $filename, callable $log): void
    {
        \DcsStats\Core\AdminBootstrap::panel();

        if ($filename === '') {
            $log('Error: No backup specified');
            return;
        }

        if (!preg_match('/^backup-\d{8}-\d{6}(?:-[A-Za-z0-9_.-]+-[A-Za-z0-9_.-]+)?\.zip$/', $filename)) {
            $log('Error: Invalid backup filename');
            return;
        }

        $rootPath = DCS_ROOT_PATH;
        $backupFile = $rootPath . '/backups/' . $filename;
        try {
            $workspaceId = bin2hex(random_bytes(12));
        } catch (\Throwable $e) {
            $log('Error: Could not create a secure restore workspace');
            return;
        }
        $workspaceRoot = $rootPath . '/site-config/data';
        $restoreDir = $workspaceRoot . '/.restore-' . $workspaceId;
        $rollbackDir = $workspaceRoot . '/.rollback-' . $workspaceId;

        if (!file_exists($backupFile)) {
            $log('Error: Backup file not found');
            return;
        }

        $log("Starting restore from: $filename");

        if (!$this->preRestoreBackupService->create($rootPath, $log)) {
            $log('Restore cancelled to avoid changing live files without a recovery point.');
            return;
        }

        if (
            !$this->filesystem->ensureDirectory($restoreDir, 0700) ||
            !$this->filesystem->ensureDirectory($rollbackDir, 0700)
        ) {
            $log('Error: Could not create restore workspace');
            return;
        }

        $zip = new \ZipArchive();
        if ($zip->open($backupFile) !== true) {
            $this->filesystem->removeDirectory($restoreDir);
            $this->filesystem->removeDirectory($rollbackDir);
            $log('Error: Failed to open backup file');
            return;
        }

        if (!$this->filesystem->archiveHasSafePaths($zip)) {
            $zip->close();
            $this->filesystem->removeDirectory($restoreDir);
            $this->filesystem->removeDirectory($rollbackDir);
            $log('Error: Backup contains unsafe file paths');
            return;
        }

        $log('Extracting backup...');
        if (!$zip->extractTo($restoreDir)) {
            $zip->close();
            $this->filesystem->removeDirectory($restoreDir);
            $this->filesystem->removeDirectory($rollbackDir);
            $log('Error: Failed to extract backup');
            return;
        }
        $zip->close();

        $restoredFiles = $this->restoreFileService->restore($rootPath, $restoreDir, $rollbackDir, $log);
        if ($restoredFiles === null) {
            $this->filesystem->removeDirectory($restoreDir);
            $this->filesystem->removeDirectory($rollbackDir);
            return;
        }

        $log('Cleaning up...');
        $this->filesystem->removeDirectory($restoreDir);
        $this->filesystem->removeDirectory($rollbackDir);

        $currentAdmin = getCurrentAdmin();
        logAdminAction('BACKUP_RESTORE', [
            'backup' => $filename,
            'restored_files' => $restoredFiles,
            'admin' => $currentAdmin['username'] ?? 'Unknown',
        ]);

        $log('Restore complete!');
        $log('Please refresh your browser to see the restored version.');
    }

}
