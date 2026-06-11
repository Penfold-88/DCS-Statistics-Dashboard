<?php

namespace DcsStats\Services\Admin;

final class DataExportService
{
    public function buildExport(string $exportType, string $format, string $dateFrom, string $dateTo, array $currentAdmin): array
    {
        require_once DCS_ROOT_PATH . '/site-config/admin_functions.php';

        $data = [];
        $filename = '';

        switch ($exportType) {
            case 'players':
                $filename = 'players_export_' . date('Y-m-d');
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
                break;

            case 'missions':
                if ($dateFrom === '' || $dateTo === '') {
                    return ['status' => 400, 'error' => 'Date range required for mission export'];
                }
                $filename = 'missions_export_' . date('Y-m-d');
                $data = [];
                break;

            case 'admin_logs':
                if ($dateFrom === '' || $dateTo === '') {
                    return ['status' => 400, 'error' => 'Date range required for logs export'];
                }
                $filename = 'admin_logs_export_' . date('Y-m-d');
                $data = $this->buildAdminLogsExport($dateFrom, $dateTo);
                break;

            case 'full':
                if ((int)($currentAdmin['role'] ?? 0) !== ROLE_AIR_BOSS) {
                    return ['status' => 403, 'error' => 'Permission denied'];
                }
                $filename = 'full_export_' . date('Y-m-d_H-i-s');
                $data = $this->buildFullExport($format, $currentAdmin);
                break;

            default:
                return ['status' => 400, 'error' => 'Invalid export type'];
        }

        if (empty($data) && $exportType !== 'full') {
            return ['status' => 404, 'error' => 'No data found for the specified criteria'];
        }

        $data = $this->redactSensitiveData($data);
        logAdminActivity('DATA_EXPORT_DOWNLOAD', $_SESSION['admin_id'], 'export', $exportType, [
            'format' => $format,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'filename' => $filename,
            'record_count' => is_array($data) ? count($data) : 0,
        ]);

        return [
            'status' => 200,
            'filename' => $filename,
            'data' => $data,
        ];
    }

    private function buildAdminLogsExport(string $dateFrom, string $dateTo): array
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

    private function buildFullExport(string $format, array $currentAdmin): array
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

    private function redactSensitiveData($value)
    {
        $sensitiveKeys = [
            'api_key',
            'password',
            'password_hash',
            'token',
            'token_hash',
            'csrf_token',
            'session_id',
            'secret',
        ];

        if (!is_array($value)) {
            return $value;
        }

        $redacted = [];
        foreach ($value as $key => $item) {
            $keyString = strtolower((string)$key);
            $isSensitive = false;
            foreach ($sensitiveKeys as $sensitiveKey) {
                if ($keyString === $sensitiveKey || strpos($keyString, $sensitiveKey) !== false) {
                    $isSensitive = true;
                    break;
                }
            }

            $redacted[$key] = $isSensitive ? '[REDACTED]' : $this->redactSensitiveData($item);
        }

        return $redacted;
    }
}

