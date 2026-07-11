<?php

namespace DcsStats\Controllers\Admin\Api;

use DcsStats\Core\AdminAuth;
use DcsStats\Core\Csrf;
use DcsStats\Services\Admin\DataExportService;

final class DataExportController
{
    public function download(): void
    {
        AdminAuth::requirePermission('export_data');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            die('Method not allowed');
        }

        \DcsStats\Core\AdminBootstrap::panel();
        \DcsStats\Core\AdminBootstrap::demo();

        $currentAdmin = getCurrentAdmin();
        if (isDemoRestricted($currentAdmin)) {
            http_response_code(403);
            die('Demo mode is enabled. Data exports are locked on the public demo.');
        }

        Csrf::requireValid();

        $exportType = (string)($_POST['type'] ?? '');
        $format = (string)($_POST['format'] ?? 'csv');
        $dateFrom = (string)($_POST['date_from'] ?? '');
        $dateTo = (string)($_POST['date_to'] ?? '');

        if (!$this->validDate($dateFrom)) {
            http_response_code(400);
            die('Invalid start date');
        }

        if (!$this->validDate($dateTo)) {
            http_response_code(400);
            die('Invalid end date');
        }

        if (!in_array($format, EXPORT_FORMATS, true)) {
            http_response_code(400);
            die('Invalid export format');
        }

        $result = (new DataExportService())->buildExport($exportType, $format, $dateFrom, $dateTo, $currentAdmin);
        if (($result['status'] ?? 200) !== 200) {
            http_response_code((int)$result['status']);
            die($result['error'] ?? 'Export failed');
        }

        if ($format === 'csv') {
            exportToCSV($result['data'], $result['filename'] . '.csv');
        }

        exportToJSON($result['data'], $result['filename'] . '.json');
    }

    private function validDate(string $date): bool
    {
        return $date === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
    }
}
