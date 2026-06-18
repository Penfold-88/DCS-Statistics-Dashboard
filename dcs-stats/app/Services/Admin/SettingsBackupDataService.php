<?php

namespace DcsStats\Services\Admin;

final class SettingsBackupDataService
{
    private SettingsBackupFileStore $fileStore;
    private SettingsBackupSiteConfigSanitizer $siteConfigSanitizer;
    private SettingsBackupBuilder $builder;
    private SettingsBackupImporter $importer;

    public function __construct(
        ?SettingsBackupFileStore $fileStore = null,
        ?SettingsBackupSiteConfigSanitizer $siteConfigSanitizer = null,
        ?SettingsBackupBuilder $builder = null,
        ?SettingsBackupImporter $importer = null
    ) {
        $this->fileStore = $fileStore ?? new SettingsBackupFileStore();
        $this->siteConfigSanitizer = $siteConfigSanitizer ?? new SettingsBackupSiteConfigSanitizer();
        $this->builder = $builder ?? new SettingsBackupBuilder($this->fileStore);
        $this->importer = $importer ?? new SettingsBackupImporter($this->fileStore, $this->siteConfigSanitizer);
    }

    public function sectionLabel($section): string
    {
        return $this->importer->sectionLabel($section);
    }

    public function buildBackup(): array
    {
        return $this->builder->build();
    }

    public function importBackup($backup, string &$error): bool
    {
        return $this->importer->import($backup, $error);
    }

}
