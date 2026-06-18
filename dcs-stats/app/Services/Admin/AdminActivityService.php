<?php

namespace DcsStats\Services\Admin;

use DcsStats\Core\AdminAuditLog;
use DcsStats\Core\AdminSessionAuth;
use DcsStats\Core\AdminUsers;

final class AdminActivityService
{
    public function currentAdmin(): ?array
    {
        if (!AdminSessionAuth::isLoggedIn()) {
            return null;
        }

        foreach (AdminUsers::all() as $user) {
            if ((int)($user['id'] ?? 0) === (int)($_SESSION['admin_id'] ?? 0)) {
                return $user;
            }
        }

        return null;
    }

    public function normalizeLog($log): array
    {
        $log = is_array($log) ? $log : [];
        $createdAt = $log['created_at'] ?? $log['timestamp'] ?? null;

        return array_merge([
            'id' => null,
            'admin_id' => 0,
            'action' => 'UNKNOWN',
            'target_type' => null,
            'target_id' => null,
            'details' => null,
            'ip_address' => $log['ip'] ?? 'unknown',
            'user_agent' => 'unknown',
            'created_at' => $createdAt,
        ], $log, [
            'created_at' => $createdAt,
            'ip_address' => $log['ip_address'] ?? $log['ip'] ?? 'unknown',
        ]);
    }

    public function logTimestamp(array $log): int
    {
        $createdAt = $log['created_at'] ?? $log['timestamp'] ?? null;
        $timestamp = $createdAt ? strtotime((string)$createdAt) : 0;
        return $timestamp ?: 0;
    }

    public function logAction($action, array $details = []): void
    {
        $logs = [];
        if (file_exists(ADMIN_LOGS_FILE)) {
            $logs = json_decode((string)file_get_contents(ADMIN_LOGS_FILE), true) ?: [];
        }

        $currentAdmin = $this->currentAdmin();
        $logs[] = [
            'action' => $action,
            'admin_id' => $_SESSION['admin_id'] ?? 0,
            'admin_username' => $currentAdmin['username'] ?? 'System',
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date(DATE_FORMAT),
        ];

        $logs = AdminAuditLog::prune($logs);
        file_put_contents(ADMIN_LOGS_FILE, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod(ADMIN_LOGS_FILE, 0600);
    }

    public function recentActivity(int $limit = 10): array
    {
        $logs = json_decode((string)@file_get_contents(ADMIN_LOGS_FILE), true) ?: [];
        $logs = array_map([$this, 'normalizeLog'], $logs);

        usort($logs, function ($a, $b) {
            return $this->logTimestamp($b) - $this->logTimestamp($a);
        });

        $userMap = [];
        foreach (AdminUsers::all() as $user) {
            $userMap[$user['id']] = $user['username'];
        }

        foreach ($logs as &$log) {
            $log['admin_username'] = $userMap[$log['admin_id']] ?? 'Unknown';
        }

        return array_slice($logs, 0, $limit);
    }

    public function dashboardStats(): array
    {
        return [
            'total_players' => 0,
            'active_players_24h' => 0,
            'active_players_7d' => 0,
            'total_bans' => 0,
            'total_admins' => 0,
            'recent_activity' => $this->recentActivity(5),
        ];
    }
}
