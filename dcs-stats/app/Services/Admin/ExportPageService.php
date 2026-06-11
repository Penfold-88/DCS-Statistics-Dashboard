<?php

namespace DcsStats\Services\Admin;

final class ExportPageService
{
    public function state(array $currentAdmin): array
    {
        $demoRestricted = \isDemoRestricted($currentAdmin);
        $error = null;
        $pageTitle = $demoRestricted ? 'Export Data Locked' : 'Export Data';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
            $error = $this->handleExport($demoRestricted);
        }

        return [
            'demoRestricted' => $demoRestricted,
            'error' => $error,
            'pageTitle' => $pageTitle,
            'recentExports' => $this->recentExports(),
        ];
    }

    private function handleExport(bool $demoRestricted): ?string
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return ERROR_MESSAGES['csrf_invalid'];
        }

        if ($demoRestricted) {
            return 'Demo mode is enabled. Data exports are locked on the public demo.';
        }

        $exportType = $_POST['export_type'] ?? '';
        $format = $_POST['format'] ?? 'csv';
        $dateFrom = $_POST['date_from'] ?? '';
        $dateTo = $_POST['date_to'] ?? '';

        \logAdminActivity('DATA_EXPORT', $_SESSION['admin_id'], 'export', $exportType, [
            'format' => $format,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        $params = http_build_query([
            'type' => $exportType,
            'format' => $format,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'csrf_token' => \getCSRFToken(),
        ]);

        header('Location: api/export_data.php?' . $params);
        exit;
    }

    private function recentExports(): array
    {
        $logs = json_decode(@file_get_contents(ADMIN_LOGS_FILE), true) ?: [];
        $logs = array_map('normalizeAdminLog', $logs);
        $adminMap = [];

        foreach (\getAdminUsers() as $admin) {
            $adminMap[$admin['id']] = $admin['username'];
        }

        $recentExports = [];
        foreach ($logs as $log) {
            if (($log['action'] ?? '') !== 'DATA_EXPORT') {
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
