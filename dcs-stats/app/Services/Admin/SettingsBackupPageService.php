<?php

namespace DcsStats\Services\Admin;

final class SettingsBackupPageService
{
    public function state(array $currentAdmin): array
    {
        require_once DCS_ROOT_PATH . '/language.php';
        require_once DCS_ROOT_PATH . '/site_features.php';
        require_once DCS_ROOT_PATH . '/site_metadata.php';

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$demoRestricted) {
            [$message, $messageType] = $this->handlePost();
        }

        $backupPreview = $demoRestricted ? [] : $this->buildBackup();
        $backupPreviewLabels = [
            'excluded' => [],
            'includes' => [],
        ];
        foreach (($backupPreview['includes'] ?? []) as $item) {
            $backupPreviewLabels['includes'][$item] = $this->sectionLabel($item);
        }
        foreach (($backupPreview['excluded'] ?? []) as $item) {
            $backupPreviewLabels['excluded'][$item] = $this->sectionLabel($item);
        }

        return [
            'backupPreview' => $backupPreview,
            'backupPreviewLabels' => $backupPreviewLabels,
            'demoRestricted' => $demoRestricted,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.settings_backup.title'),
        ];
    }

    private function dataPath(string $fileName): string
    {
        return DCS_ROOT_PATH . '/site-config/data/' . $fileName;
    }

    public function sectionLabel($section): string
    {
        $key = 'admin.settings_backup.section.' . preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)$section)));
        $translated = \dcs_t($key);
        return $translated === $key ? str_replace('_', ' ', $section) : $translated;
    }

    private function readJsonFile($path, $fallback = null)
    {
        if (!file_exists($path)) {
            return $fallback;
        }

        $data = json_decode((string)file_get_contents($path), true);
        return is_array($data) ? $data : $fallback;
    }

    private function writeJsonFile($path, $data): bool
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $result = @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        if ($result !== false) {
            @chmod($path, 0600);
        }

        return $result !== false;
    }

    private function readTextFile($path): ?string
    {
        return file_exists($path) ? file_get_contents($path) : null;
    }

    public function buildBackup(): array
    {
        return [
            'type' => 'dcs_statistics_dashboard_site_settings',
            'schema_version' => 1,
            'exported_at' => gmdate('c'),
            'app_version' => defined('ADMIN_PANEL_VERSION') ? ADMIN_PANEL_VERSION : null,
            'includes' => [
                'site_config',
                'site_features',
                'site_metadata',
                'menu_config',
                'chart_theme',
                'header_image_settings',
                'custom_theme_css',
                'header_custom_css',
            ],
            'data' => [
                'site_config' => $this->readJsonFile(DCS_ROOT_PATH . '/site_config.json', []),
                'site_features' => \loadSiteFeatures(),
                'site_metadata' => \loadSiteMetadata(),
                'menu_config' => $this->readJsonFile($this->dataPath('menu_config.json'), null),
                'chart_theme' => $this->readJsonFile($this->dataPath('chart_theme.json'), null),
                'header_image' => $this->readJsonFile($this->dataPath('header_image.json'), null),
                'custom_theme_css' => $this->readTextFile(DCS_ROOT_PATH . '/custom_theme.css'),
                'header_custom_css' => $this->readTextFile(DCS_ROOT_PATH . '/header_custom.css'),
            ],
            'excluded' => [
                'api_config',
                'admin_users',
                'passwords',
                'sessions',
                'logs',
                'bans',
                'maintenance_whitelist',
                'uploads',
                'backups',
                'version_metadata',
            ],
        ];
    }

    private function cleanSiteConfigForImport($config): array
    {
        if (!is_array($config)) {
            return [];
        }

        $allowedKeys = [
            'site_name',
            'default_language',
            'date_format',
            'discord_invite_url',
            'theme',
            'allow_player_search',
            'show_squadron_tab',
            'show_servers_tab',
        ];

        return array_intersect_key($config, array_flip($allowedKeys));
    }

    private function importBackup($backup, string &$error): bool
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
            $existing = $this->readJsonFile(DCS_ROOT_PATH . '/site_config.json', []);
            $safeConfig = array_merge($existing ?: [], $this->cleanSiteConfigForImport($data['site_config']));
            if (!$this->writeJsonFile(DCS_ROOT_PATH . '/site_config.json', $safeConfig)) {
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
            'menu_config' => $this->dataPath('menu_config.json'),
            'chart_theme' => $this->dataPath('chart_theme.json'),
            'header_image' => $this->dataPath('header_image.json'),
        ] as $section => $path) {
            if (isset($data[$section]) && is_array($data[$section]) && !$this->writeJsonFile($path, $data[$section])) {
                $error = \dcs_t('admin.settings_backup.restore_section_failed', ['section' => $this->sectionLabel($section)]);
                return false;
            }
        }

        foreach ([
            'custom_theme_css' => DCS_ROOT_PATH . '/custom_theme.css',
            'header_custom_css' => DCS_ROOT_PATH . '/header_custom.css',
        ] as $section => $path) {
            if (isset($data[$section]) && is_string($data[$section]) && @file_put_contents($path, $data[$section]) === false) {
                $error = \dcs_t('admin.settings_backup.restore_section_failed', ['section' => $this->sectionLabel($section)]);
                return false;
            }
        }

        return true;
    }

    private function handlePost(): array
    {
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [\dcs_t('admin.settings_backup.csrf_invalid'), 'error'];
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'export_settings') {
            $backup = $this->buildBackup();
            $fileName = 'dcs-site-settings-' . date('Y-m-d-H-i-s') . '.json';

            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            echo json_encode($backup, JSON_PRETTY_PRINT);
            exit;
        }

        if ($action === 'import_settings') {
            return $this->handleImport();
        }

        return ['', ''];
    }

    private function handleImport(): array
    {
        if (!isset($_FILES['settings_file']) || $_FILES['settings_file']['error'] !== UPLOAD_ERR_OK) {
            return [\dcs_t('admin.settings_backup.select_file'), 'error'];
        }

        if ($_FILES['settings_file']['size'] > 2 * 1024 * 1024) {
            return [\dcs_t('admin.settings_backup.file_too_large'), 'error'];
        }

        $backup = json_decode((string)file_get_contents($_FILES['settings_file']['tmp_name']), true);
        $importError = '';

        if ($this->importBackup($backup, $importError)) {
            \logAdminActivity('SITE_SETTINGS_IMPORT', $_SESSION['admin_id'], 'settings', 'site_settings_backup', [
                'schema_version' => $backup['schema_version'] ?? null,
                'exported_at' => $backup['exported_at'] ?? null,
            ]);

            return [\dcs_t('admin.settings_backup.restore_success'), 'success'];
        }

        return [$importError ?: \dcs_t('admin.settings_backup.invalid_file'), 'error'];
    }
}
