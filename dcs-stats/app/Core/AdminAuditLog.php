<?php

namespace DcsStats\Core;

final class AdminAuditLog
{
    public static function readAll(?string $logsFile = null): array
    {
        $logsFile = $logsFile ?? AdminEnvironment::dataFilePath('logs');
        if (!is_file($logsFile)) {
            return [];
        }

        if (!is_readable($logsFile)) {
            error_log('Admin log file is not readable: ' . $logsFile);
            return [];
        }

        $content = file_get_contents($logsFile);
        if ($content === false) {
            error_log('Unable to read admin log file: ' . $logsFile);
            return [];
        }

        $logs = json_decode($content, true);
        if (!is_array($logs)) {
            error_log('Admin log file contains invalid JSON: ' . $logsFile);
            return [];
        }

        return $logs;
    }

    public static function prune($logs, ?int $maxLogs = null): array
    {
        if (!is_array($logs)) {
            return [];
        }

        $maxLogs = $maxLogs ?? (defined('MAX_ADMIN_LOGS') ? MAX_ADMIN_LOGS : 1000);
        $maxLogs = max(1, (int)$maxLogs);
        $cutoffDate = date(DATE_FORMAT, strtotime('-' . LOG_RETENTION_DAYS . ' days'));

        $logs = array_filter($logs, function ($log) use ($cutoffDate) {
            if (!is_array($log)) {
                return false;
            }

            $createdAt = $log['created_at'] ?? $log['timestamp'] ?? '';
            return $createdAt === '' || $createdAt > $cutoffDate;
        });

        if (count($logs) > $maxLogs) {
            $logs = array_slice($logs, -$maxLogs);
        }

        return array_values($logs);
    }

    public static function write($action, $adminId = null, $targetType = null, $targetId = null, $details = null): void
    {
        if (!LOG_ADMIN_ACTIONS) {
            return;
        }

        $logsFile = AdminEnvironment::dataFilePath('logs');
        $logs = self::readAll($logsFile);
        $nextId = 1;
        foreach ($logs as $existingLog) {
            $nextId = max($nextId, (int)($existingLog['id'] ?? 0) + 1);
        }

        $logs[] = [
            'id' => $nextId,
            'admin_id' => $adminId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date(DATE_FORMAT),
        ];

        $logs = self::prune($logs);

        @file_put_contents($logsFile, json_encode(array_values($logs), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod($logsFile, 0600);
    }
}
