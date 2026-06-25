<?php

namespace DcsStats\Services\Admin;

final class BackupService
{
    private BackupCatalogService $catalogService;
    private BackupDeletionService $deletionService;
    private BackupCreationService $creationService;

    public function __construct(
        ?AdminFilesystemService $filesystem = null,
        ?BackupCleanupService $cleanupService = null,
        ?BackupMetadataService $metadataService = null,
        ?ByteFormatter $byteFormatter = null,
        ?BackupArchiveBuilder $archiveBuilder = null,
        ?BackupDeletionService $deletionService = null,
        ?BackupCatalogService $catalogService = null,
        ?BackupCreationService $creationService = null
    ) {
        $archiveBuilder = $archiveBuilder ?? new BackupArchiveBuilder($filesystem);
        $this->catalogService = $catalogService ?? new BackupCatalogService($metadataService, $byteFormatter);
        $this->deletionService = $deletionService ?? new BackupDeletionService();
        $this->creationService = $creationService ?? new BackupCreationService(
            $archiveBuilder,
            $cleanupService,
            $byteFormatter
        );
    }

    public function listBackups(): array
    {
        return $this->catalogService->list();
    }

    public function deleteBackup(string $filename): array
    {
        return $this->deletionService->delete($filename);
    }

    public function createBackup(callable $log): void
    {
        $this->creationService->create($log);
    }
}
