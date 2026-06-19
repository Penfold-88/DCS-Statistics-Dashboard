<?php

namespace DcsStats\Services\Admin;

final class AdminExportResponder
{
    public function csv($data, string $filename = 'export.csv'): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        foreach ($data as $row) {
            fputcsv($output, array_map([$this, 'safeCsvCell'], $row));
        }

        fclose($output);
        exit;
    }

    public function json($data, string $filename = 'export.json'): void
    {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    private function safeCsvCell($value)
    {
        if (!is_scalar($value) && $value !== null) {
            $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $value = (string)$value;
        if (preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'" . $value;
        }

        return $value;
    }
}
