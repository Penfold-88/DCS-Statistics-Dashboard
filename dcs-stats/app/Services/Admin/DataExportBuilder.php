<?php

namespace DcsStats\Services\Admin;

use DcsStats\Core\AdminAuditLog;

final class DataExportBuilder
{
    public function players(bool $includeIdentifiers = false): array
    {
        $data = [];
        foreach (getPlayers() as $player) {
            $stats = getPlayerStats($player['ucid']);
            $row = [
                'name' => $player['name'],
                'kills' => $stats['kills'],
                'deaths' => $stats['deaths'],
                'kd_ratio' => $stats['kd_ratio'],
                'sorties' => $stats['sorties'],
                'last_seen' => $stats['last_seen'],
                'is_banned' => isPlayerBanned($player['ucid']) ? 'Yes' : 'No',
            ];
            if ($includeIdentifiers) {
                $row = ['name' => $row['name'], 'ucid' => $player['ucid']] + array_slice($row, 1, null, true);
            }
            $data[] = $row;
        }

        return $data;
    }

    public function adminLogs(string $dateFrom, string $dateTo): array
    {
        $logs = AdminAuditLog::readAll(ADMIN_LOGS_FILE);
        $logs = array_map('normalizeAdminLog', $logs);
        $users = getAdminUsers();
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user['id']] = $user['username'];
        }

        $data = [];
        foreach ($logs as $log) {
            $logDate = $log['created_at'] ? substr($log['created_at'], 0, 10) : '';
            if ($logDate >= $dateFrom && $logDate <= $dateTo) {
                $data[] = [
                    'date' => $log['created_at'] ?? '',
                    'admin' => $userMap[$log['admin_id']] ?? 'Unknown',
                    'action' => LOG_ACTIONS[$log['action']] ?? $log['action'],
                    'target_type' => $log['target_type'] ?? '',
                    'target_id' => $log['target_id'] ?? '',
                    'details' => is_array($log['details'] ?? null) ? json_encode($log['details']) : ($log['details'] ?? ''),
                ];
            }
        }

        return $data;
    }

    public function full(string $format, array $currentAdmin): array
    {
        /*
         * Full exports are intentionally restricted to Air Boss administrators by
         * the export controller/UI. They are designed for site ownership,
         * migration and audit use, and may include player identifiers, admin
         * account metadata, ban records and admin activity records.
         */
        if ($format === 'json') {
            return [
                'export_date' => date('c'),
                'export_by' => $currentAdmin['username'],
                'players' => $this->allowFields(getPlayers(), [
                    'name', 'ucid', 'discord_id', 'first_seen', 'last_seen', 'created_at', 'updated_at',
                ]),
                'bans' => $this->allowFields(getPlayerBans(false), [
                    'ucid', 'name', 'player_name', 'reason', 'is_active', 'created_at', 'banned_at',
                    'banned_by', 'expires_at', 'unbanned_at', 'unbanned_by',
                ]),
                'admin_users' => $this->allowFields(getAdminUsers(), [
                    'id', 'username', 'email', 'role', 'permissions', 'created_at', 'last_login', 'is_active',
                ]),
                'admin_logs' => $this->allowFields(
                    AdminAuditLog::readAll(ADMIN_LOGS_FILE),
                    ['admin_id', 'action', 'target_type', 'target_id', 'details', 'created_at', 'timestamp']
                ),
            ];
        }

        return $this->players(true);
    }

    private function allowFields(array $records, array $allowedFields): array
    {
        $allowed = array_flip($allowedFields);
        $filtered = [];
        foreach ($records as $record) {
            if (is_array($record)) {
                $filtered[] = array_intersect_key($record, $allowed);
            }
        }

        return $filtered;
    }
}
