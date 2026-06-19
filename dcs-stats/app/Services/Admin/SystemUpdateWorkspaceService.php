<?php

namespace DcsStats\Services\Admin;

final class SystemUpdateWorkspaceService
{
    private AdminFilesystemService $filesystem;

    public function __construct(?AdminFilesystemService $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new AdminFilesystemService();
    }

    public function prepare(string $rootPath): ?array
    {
        $upgradeParentDir = $rootPath . '/site-config/data/.updates';
        $upgradeDir = $upgradeParentDir . '/update-' . bin2hex(random_bytes(8));
        $backupDir = $rootPath . '/backups';

        if (!$this->filesystem->ensureDirectory($upgradeParentDir, 0700)) {
            return null;
        }
        if (!$this->filesystem->ensureDirectory($upgradeDir, 0700)) {
            return null;
        }
        if (!$this->filesystem->ensureDirectory($backupDir, 0700)) {
            $this->filesystem->removeDirectory($upgradeDir);
            return null;
        }

        register_shutdown_function(function () use ($upgradeDir): void {
            if (is_dir($upgradeDir)) {
                $this->filesystem->removeDirectory($upgradeDir);
            }
        });

        return [
            'upgrade_dir' => $upgradeDir,
            'backup_dir' => $backupDir,
        ];
    }

    public function cleanup(string $upgradeDir): void
    {
        $this->filesystem->removeDirectory($upgradeDir);
    }
}
