<?php

namespace DcsStats\Services\Admin;

final class SettingsBackupImporter
{
    private SettingsBackupFileStore $fileStore;
    private SettingsBackupSiteConfigSanitizer $siteConfigSanitizer;
    private CssContentValidator $cssValidator;

    public function __construct(
        ?SettingsBackupFileStore $fileStore = null,
        ?SettingsBackupSiteConfigSanitizer $siteConfigSanitizer = null,
        ?CssContentValidator $cssValidator = null
    ) {
        $this->fileStore = $fileStore ?? new SettingsBackupFileStore();
        $this->siteConfigSanitizer = $siteConfigSanitizer ?? new SettingsBackupSiteConfigSanitizer();
        $this->cssValidator = $cssValidator ?? new CssContentValidator();
    }

    public function sectionLabel($section): string
    {
        $key = 'admin.settings_backup.section.' . preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)$section)));
        $translated = \dcs_t($key);
        return $translated === $key ? str_replace('_', ' ', $section) : $translated;
    }

    public function import($backup, string &$error): bool
    {
        if (!is_array($backup) || ($backup['type'] ?? '') !== 'dcs_statistics_dashboard_site_settings') {
            $error = \dcs_t('admin.settings_backup.invalid_file');
            return false;
        }

        $data = $backup['data'] ?? null;
        if (!is_array($data)) {
            $error = \dcs_t('admin.settings_backup.no_settings_data');
            return false;
        }

        if (isset($data['site_config']) && is_array($data['site_config'])) {
            $existing = $this->fileStore->readJsonFile(DCS_ROOT_PATH . '/site_config.json', []);
            $safeConfig = array_merge($existing ?: [], $this->siteConfigSanitizer->clean($data['site_config']));
            if (!$this->fileStore->writeJsonFile(DCS_ROOT_PATH . '/site_config.json', $safeConfig)) {
                $error = \dcs_t('admin.settings_backup.restore_site_config_failed');
                return false;
            }
        }

        if (isset($data['site_features']) && is_array($data['site_features']) && !\saveSiteFeatures($data['site_features'])) {
            $error = \dcs_t('admin.settings_backup.restore_site_features_failed');
            return false;
        }

        if (isset($data['site_metadata']) && is_array($data['site_metadata']) && !\saveSiteMetadata($data['site_metadata'])) {
            $error = \dcs_t('admin.settings_backup.restore_site_metadata_failed');
            return false;
        }

        foreach ([
            'menu_config' => $this->fileStore->dataPath('menu_config.json'),
            'chart_theme' => $this->fileStore->dataPath('chart_theme.json'),
            'header_image' => $this->fileStore->dataPath('header_image.json'),
            'cms_pages' => $this->fileStore->dataPath('pages.json'),
        ] as $section => $path) {
            if (isset($data[$section]) && is_array($data[$section]) && !$this->fileStore->writeJsonFile($path, $data[$section])) {
                $error = \dcs_t('admin.settings_backup.restore_section_failed', ['section' => $this->sectionLabel($section)]);
                return false;
            }
        }

        foreach ([
            'custom_theme_css' => DCS_ROOT_PATH . '/custom_theme.css',
            'header_custom_css' => DCS_ROOT_PATH . '/header_custom.css',
        ] as $section => $path) {
            if (isset($data[$section]) && is_string($data[$section])) {
                if (!$this->cssValidator->isSafe($data[$section]) || @file_put_contents($path, $data[$section], LOCK_EX) === false) {
                    $error = \dcs_t('admin.settings_backup.restore_section_failed', ['section' => $this->sectionLabel($section)]);
                    return false;
                }
            }
        }

        return true;
    }
}
