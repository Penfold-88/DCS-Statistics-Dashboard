<?php

namespace DcsStats\Services\Admin;

final class BackupCleanupService
{
    public function cleanupOldBackups(string $backupDir, int $maxBackups, callable $log): void
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
