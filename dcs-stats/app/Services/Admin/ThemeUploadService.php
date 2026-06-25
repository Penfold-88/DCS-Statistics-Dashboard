<?php

namespace DcsStats\Services\Admin;

final class ThemeUploadService
{
    private ThemeCssBackupService $cssBackupService;
    private HeaderImageActionService $headerImageActionService;

    public function __construct(
        ?ThemeAssetService $assetService = null,
        ?ImageUploadService $imageUploadService = null,
        ?ThemeCssBackupService $cssBackupService = null,
        ?HeaderImageActionService $headerImageActionService = null
    ) {
        $this->cssBackupService = $cssBackupService ?? new ThemeCssBackupService();
        $this->headerImageActionService = $headerImageActionService ?? new HeaderImageActionService($assetService, $imageUploadService);
    }

    public function uploadCss(array $files): array
    {
        return $this->cssBackupService->upload($files);
    }

    public function restoreBackup(string $backupFile): array
    {
        return $this->cssBackupService->restore($backupFile);
    }

    public function updateHeaderImageSettings(array $post, array $files): array
    {
        return $this->headerImageActionService->update($post, $files);
    }

    public function listBackups(): array
    {
        return $this->cssBackupService->list();
    }
}
