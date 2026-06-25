<?php

namespace DcsStats\Services\Admin;

use DcsStats\Core\AdminAuditLog;

final class ExportPageService
{
    public function state(array $currentAdmin): array
    {
        $demoRestricted = \isDemoRestricted($currentAdmin);
        $error = null;
        $pageTitle = $demoRestricted ? 'Export Data Locked' : 'Export Data';

        return [
            'demoRestricted' => $demoRestricted,
            'error' => $error,
            'pageTitle' => $pageTitle,
            'recentExports' => $this->recentExports(),
        ];
    }

    private function recentExports(): array
    {
        $logs = AdminAuditLog::readAll(ADMIN_LOGS_FILE);
        $logs = array_map('normalizeAdminLog', $logs);
        $adminMap = [];

        foreach (\getAdminUsers() as $admin) {
            $adminMap[$admin['id']] = $admin['username'];
        }

        $recentExports = [];
        foreach ($logs as $log) {
            if (!in_array(($log['action'] ?? ''), ['DATA_EXPORT', 'DATA_EXPORT_DOWNLOAD'], true)) {
                continue;
            }

            if (\adminLogTimestamp($log) <= strtotime('-30 days')) {
                continue;
            }

            $details = $log['details'] ?? [];
            $recentExports[] = [
                'created_at' => $log['created_at'] ?? '',
                'admin' => $adminMap[$log['admin_id'] ?? null] ?? 'Unknown',
                'type' => $log['target_id'] ?? 'Unknown',
                'format' => $details['format'] ?? 'Unknown',
                'date_from' => $details['date_from'] ?? '',
                'date_to' => $details['date_to'] ?? '',
            ];
        }

        usort($recentExports, function (array $a, array $b): int {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return array_slice($recentExports, 0, 10);
    }
}
