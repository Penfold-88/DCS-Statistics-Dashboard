<?php

namespace DcsStats\Services\Admin;

final class DataExportService
{
    private DataExportBuilder $builder;
    private SensitiveDataRedactor $redactor;

    public function __construct(?DataExportBuilder $builder = null, ?SensitiveDataRedactor $redactor = null)
    {
        $this->builder = $builder ?? new DataExportBuilder();
        $this->redactor = $redactor ?? new SensitiveDataRedactor();
    }

    public function buildExport(string $exportType, string $format, string $dateFrom, string $dateTo, array $currentAdmin): array
    {
        \DcsStats\Core\AdminBootstrap::panel();

        $data = [];
        $filename = '';

        switch ($exportType) {
            case 'players':
                $filename = 'players_export_' . date('Y-m-d');
                $data = $this->builder->players();
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
                $data = $this->builder->adminLogs($dateFrom, $dateTo);
                break;

            case 'full':
                if ((int)($currentAdmin['role'] ?? 0) !== ROLE_AIR_BOSS) {
                    return ['status' => 403, 'error' => 'Permission denied'];
                }
                $filename = 'full_export_' . date('Y-m-d_H-i-s');
                $data = $this->builder->full($format, $currentAdmin);
                break;

            default:
                return ['status' => 400, 'error' => 'Invalid export type'];
        }

        if (empty($data) && $exportType !== 'full') {
            return ['status' => 404, 'error' => 'No data found for the specified criteria'];
        }

        $data = $this->redactor->redact($data);
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

}
