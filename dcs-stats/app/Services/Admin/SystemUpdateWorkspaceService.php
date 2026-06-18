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
        $upgradeParentDir = $rootPath . '/UPGRADE';
        $upgradeDir = $upgradeParentDir . '/update-' . bin2hex(random_bytes(8));
        $backupDir = $rootPath . '/backups';

        if (!$this->filesystem->ensureDirectory($upgradeParentDir)) {
            return null;
        }
        if (!$this->filesystem->ensureDirectory($upgradeDir)) {
            return null;
        }
        if (!$this->filesystem->ensureDirectory($backupDir)) {
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
