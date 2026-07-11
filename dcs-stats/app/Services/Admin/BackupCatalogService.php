<?php

namespace DcsStats\Services\Admin;

final class BackupCatalogService
{
    private BackupMetadataService $metadataService;
    private ByteFormatter $byteFormatter;
    private string $backupDir;

    public function __construct(
        ?BackupMetadataService $metadataService = null,
        ?ByteFormatter $byteFormatter = null,
        ?string $backupDir = null
    ) {
        $this->metadataService = $metadataService ?? new BackupMetadataService();
        $this->byteFormatter = $byteFormatter ?? new ByteFormatter();
        $this->backupDir = $backupDir ?? DCS_ROOT_PATH . '/backups';
    }

    public function list(): array
    {
        if (!is_dir($this->backupDir)) {
            return [];
        }

        $backups = [];
        foreach (glob($this->backupDir . '/backup-*.zip') ?: [] as $file) {
            $filename = basename($file);
            $metadata = $this->metadataService->read($file, $filename);
            $backups[] = [
                'name' => $filename,
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'size' => $this->byteFormatter->format((int)filesize($file)),
                'version' => $metadata['version'],
                'branch' => $metadata['branch'],
            ];
        }

        usort($backups, static function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $backups;
    }
}
