<?php

namespace DcsStats\Services\Admin;

final class BackupDeletionService
{
    public function delete(string $filename): array
    {
        if ($filename === '') {
            return ['success' => false, 'error' => 'No backup specified'];
        }

        if (!preg_match('/^backup-\d{8}-\d{6}(?:-[A-Za-z0-9_.-]+-[A-Za-z0-9_.-]+)?\.zip$/', $filename)) {
            return ['success' => false, 'error' => 'Invalid backup filename'];
        }

        $backupDir = DCS_ROOT_PATH . '/backups';
        $backupDirReal = realpath($backupDir);
        $backupFileReal = realpath($backupDir . '/' . $filename);

        if ($backupDirReal === false || $backupFileReal === false || !is_file($backupFileReal)) {
            return ['success' => false, 'error' => 'Backup not found'];
        }

        $backupDirPrefix = rtrim($backupDirReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strpos($backupFileReal, $backupDirPrefix) !== 0) {
            return ['success' => false, 'error' => 'Invalid backup path'];
        }

        if (!unlink($backupFileReal)) {
            return ['success' => false, 'error' => 'Failed to delete backup'];
        }

        \DcsStats\Core\AdminBootstrap::panel();
        $currentAdmin = getCurrentAdmin();
        logAdminAction('BACKUP_DELETE', [
            'backup' => $filename,
            'admin' => $currentAdmin['username'] ?? 'Unknown',
        ]);

        return ['success' => true];
    }
}
