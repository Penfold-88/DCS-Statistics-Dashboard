<?php

namespace DcsStats\Services\Admin;

final class DataExportBuilder
{
    public function players(): array
    {
        $data = [];
        foreach (getPlayers() as $player) {
            $stats = getPlayerStats($player['ucid']);
            $data[] = [
                'name' => $player['name'],
                'ucid' => $player['ucid'],
                'kills' => $stats['kills'],
                'deaths' => $stats['deaths'],
                'kd_ratio' => $stats['kd_ratio'],
                'sorties' => $stats['sorties'],
                'last_seen' => $stats['last_seen'],
                'is_banned' => isPlayerBanned($player['ucid']) ? 'Yes' : 'No',
            ];
        }

        return $data;
    }

    public function adminLogs(string $dateFrom, string $dateTo): array
    {
        $logs = json_decode(@file_get_contents(ADMIN_LOGS_FILE), true) ?: [];
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
                    'ip_address' => $log['ip_address'] ?? '',
                    'details' => is_array($log['details'] ?? null) ? json_encode($log['details']) : ($log['details'] ?? ''),
                ];
            }
        }

        return $data;
    }

    public function full(string $format, array $currentAdmin): array
    {
        if ($format === 'json') {
            return [
                'export_date' => date('c'),
                'export_by' => $currentAdmin['username'],
                'players' => getPlayers(),
                'bans' => getPlayerBans(false),
                'admin_users' => array_map(function ($user) {
                    unset($user['password_hash']);
                    return $user;
                }, getAdminUsers()),
                'admin_logs' => json_decode(file_get_contents(ADMIN_LOGS_FILE), true) ?: [],
            ];
        }

        $data = [];
        foreach (getPlayers() as $player) {
            $stats = getPlayerStats($player['ucid']);
            $data[] = array_merge($player, $stats);
        }

        return $data;
    }
}
