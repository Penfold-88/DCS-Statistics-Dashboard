<?php

namespace DcsStats\Services\Admin;

final class LogsPageService
{
    public function state(): array
    {
        $filterAction = $_GET['action'] ?? '';
        $filterAdmin = $_GET['admin'] ?? '';
        $filterDateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
        $filterDateTo = $_GET['date_to'] ?? date('Y-m-d');
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = RECORDS_PER_PAGE;
        $allLogs = json_decode((string)@file_get_contents(ADMIN_LOGS_FILE), true) ?: [];
        $prunedLogs = function_exists('pruneAdminLogs') ? \pruneAdminLogs($allLogs) : $allLogs;

        if (count($prunedLogs) !== count($allLogs)) {
            @file_put_contents(ADMIN_LOGS_FILE, json_encode($prunedLogs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
            @chmod(ADMIN_LOGS_FILE, 0600);
            $allLogs = $prunedLogs;
        }

        $allLogs = array_map('normalizeAdminLog', $allLogs);
        $users = \getAdminUsers();
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user['id']] = $user['username'];
        }

        $filteredLogs = $this->filterLogs($allLogs, $userMap, $filterAction, $filterAdmin, $filterDateFrom, $filterDateTo);
        usort($filteredLogs, static fn($a, $b) => \adminLogTimestamp($b) - \adminLogTimestamp($a));

        $totalLogs = count($filteredLogs);
        $totalPages = ceil($totalLogs / $perPage);
        $page = min($page, $totalPages ?: 1);
        $offset = ($page - 1) * $perPage;
        $logs = array_slice($filteredLogs, $offset, $perPage);
        $paginationParams = array_filter([
            'action' => $filterAction,
            'admin' => $filterAdmin,
            'date_from' => $filterDateFrom,
            'date_to' => $filterDateTo,
        ], static fn($value) => $value !== '' && $value !== null);

        $uniqueActions = array_unique(array_column($allLogs, 'action'));
        sort($uniqueActions);

        return [
            'filterAction' => $filterAction,
            'filterAdmin' => $filterAdmin,
            'filterDateFrom' => $filterDateFrom,
            'filterDateTo' => $filterDateTo,
            'logs' => $logs,
            'page' => $page,
            'pageTitle' => 'Activity Logs',
            'paginationBaseUrl' => 'logs.php' . (!empty($paginationParams) ? '?' . http_build_query($paginationParams) : ''),
            'totalLogs' => $totalLogs,
            'totalPages' => $totalPages,
            'uniqueActions' => $uniqueActions,
            'users' => $users,
        ];
    }

    private function filterLogs(array $allLogs, array $userMap, string $filterAction, string $filterAdmin, string $filterDateFrom, string $filterDateTo): array
    {
        $filteredLogs = [];

        foreach ($allLogs as $log) {
            $logDate = $log['created_at'] ? substr($log['created_at'], 0, 10) : '';
            if ($logDate < $filterDateFrom || $logDate > $filterDateTo) {
                continue;
            }

            if ($filterAction && $log['action'] !== $filterAction) {
                continue;
            }

            if ($filterAdmin && $log['admin_id'] != $filterAdmin) {
                continue;
            }

            $log['admin_username'] = $userMap[$log['admin_id']] ?? 'Unknown';
            $filteredLogs[] = $log;
        }

        return $filteredLogs;
    }
}
