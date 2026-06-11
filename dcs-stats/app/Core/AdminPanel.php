<?php

namespace DcsStats\Core;

final class AdminPanel
{
    public static function currentAdmin(): ?array
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

    public static function formatDate($date, ?string $format = null): string
    {
        if (!$date) {
            return 'Never';
        }

        $timestamp = strtotime((string)$date);
        if (!$timestamp) {
            return 'Never';
        }

        return date($format ?: 'M d, Y H:i', $timestamp);
    }

    public static function normalizeLog($log): array
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

    public static function logTimestamp(array $log): int
    {
        $createdAt = $log['created_at'] ?? $log['timestamp'] ?? null;
        $timestamp = $createdAt ? strtotime((string)$createdAt) : 0;
        return $timestamp ?: 0;
    }

    public static function logAction($action, array $details = []): void
    {
        $logFile = ADMIN_LOGS_FILE;
        $logs = [];

        if (file_exists($logFile)) {
            $logs = json_decode((string)file_get_contents($logFile), true) ?: [];
        }

        $currentAdmin = self::currentAdmin();
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

        file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod($logFile, 0600);
    }

    public static function recentActivity(int $limit = 10): array
    {
        $logs = json_decode((string)@file_get_contents(ADMIN_LOGS_FILE), true) ?: [];
        $logs = array_map([self::class, 'normalizeLog'], $logs);

        usort($logs, function ($a, $b) {
            return self::logTimestamp($b) - self::logTimestamp($a);
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

    public static function dashboardStats(): array
    {
        return [
            'total_players' => 0,
            'active_players_24h' => 0,
            'active_players_7d' => 0,
            'total_bans' => 0,
            'total_admins' => 0,
            'recent_activity' => self::recentActivity(5),
        ];
    }

    public static function roleBadge($role): string
    {
        $roleNames = [
            ROLE_AIR_BOSS => ['name' => 'Air Boss', 'color' => '#ff4444', 'icon' => '✈️'],
            ROLE_LSO => ['name' => 'LSO', 'color' => '#2196F3', 'icon' => '🚦'],
        ];

        $info = $roleNames[$role] ?? ['name' => 'Unknown', 'color' => '#666', 'icon' => '❓'];

        return '<span style="display: inline-block; padding: 4px 8px; background-color: ' .
            $info['color'] . '; color: white; border-radius: 3px; font-size: 12px; font-weight: bold;">' .
            $info['icon'] . ' ' . $info['name'] . '</span>';
    }

    public static function pagination($totalItems, $perPage, $currentPage, $baseUrl): string
    {
        $totalPages = ceil($totalItems / $perPage);
        if ($totalPages <= 1) {
            return '';
        }

        $html = '<div class="pagination">';

        if ($currentPage > 1) {
            $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination-prev">Previous</a>';
        }

        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $currentPage) {
                $html .= '<span class="pagination-current">' . $i . '</span>';
            } else {
                $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link">' . $i . '</a>';
            }
        }

        if ($currentPage < $totalPages) {
            $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination-next">Next</a>';
        }

        $html .= '</div>';

        return $html;
    }

    public static function loadMaintenanceConfig(): array
    {
        $file = DCS_ROOT_PATH . '/site-config/data/maintenance.json';
        $defaults = ['enabled' => false, 'ip_whitelist' => []];

        if (file_exists($file)) {
            $data = json_decode((string)file_get_contents($file), true);
            if (is_array($data)) {
                return array_merge($defaults, $data);
            }
        }

        return $defaults;
    }

    public static function saveMaintenanceConfig($config): bool
    {
        $file = DCS_ROOT_PATH . '/site-config/data/maintenance.json';
        return file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT)) !== false;
    }
}
