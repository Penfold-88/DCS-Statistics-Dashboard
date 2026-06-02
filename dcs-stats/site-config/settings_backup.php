<?php
/**
 * Site Settings Backup and Restore
 *
 * Exports portable, non-sensitive site settings only.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once __DIR__ . '/demo_helpers.php';
require_once dirname(__DIR__) . '/language.php';
require_once dirname(__DIR__) . '/site_features.php';
require_once dirname(__DIR__) . '/site_metadata.php';

requireAdmin();
requirePermission('manage_features');

$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);
$message = '';
$messageType = '';

if ($demoRestricted) {
    $pageTitle = dcs_t('admin.settings_backup.title');
    ?>
<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-wrapper">
    <?php include 'nav.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <h1><?= e($pageTitle) ?></h1>
            <div class="admin-user-menu">
                <div class="admin-user-info">
                    <div class="admin-username"><?= e($currentAdmin['username']) ?></div>
                    <div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div>
                </div>
                <a href="logout.php" class="btn btn-secondary btn-small"><?= e(dcs_t('admin.common.logout')) ?></a>
            </div>
        </header>

        <div class="admin-content">
            <div class="card">
                <div class="alert alert-warning">
                    Demo mode is enabled. Settings backup and restore are locked on the public demo.
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
    <?php
    exit;
}

function settingsBackupDataPath($fileName) {
    return __DIR__ . '/data/' . $fileName;
}

function settingsBackupSectionLabel($section) {
    $key = 'admin.settings_backup.section.' . preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)$section)));
    $translated = dcs_t($key);
    return $translated === $key ? str_replace('_', ' ', $section) : $translated;
}

function readJsonFileForBackup($path, $fallback = null) {
    if (!file_exists($path)) {
        return $fallback;
    }

    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : $fallback;
}

function writeJsonFileFromBackup($path, $data) {
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

function readTextFileForBackup($path) {
    return file_exists($path) ? file_get_contents($path) : null;
}

function buildSiteSettingsBackup() {
    $root = dirname(__DIR__);

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
            'header_custom_css'
        ],
        'data' => [
            'site_config' => readJsonFileForBackup($root . '/site_config.json', []),
            'site_features' => loadSiteFeatures(),
            'site_metadata' => loadSiteMetadata(),
            'menu_config' => readJsonFileForBackup(settingsBackupDataPath('menu_config.json'), null),
            'chart_theme' => readJsonFileForBackup(settingsBackupDataPath('chart_theme.json'), null),
            'header_image' => readJsonFileForBackup(settingsBackupDataPath('header_image.json'), null),
            'custom_theme_css' => readTextFileForBackup($root . '/custom_theme.css'),
            'header_custom_css' => readTextFileForBackup($root . '/header_custom.css')
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
            'version_metadata'
        ]
    ];
}

function cleanSiteConfigForImport($config) {
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
        'show_servers_tab'
    ];

    return array_intersect_key($config, array_flip($allowedKeys));
}

function importSiteSettingsBackup($backup, &$error) {
    if (!is_array($backup) || ($backup['type'] ?? '') !== 'dcs_statistics_dashboard_site_settings') {
        $error = dcs_t('admin.settings_backup.invalid_file');
        return false;
    }

    $data = $backup['data'] ?? null;
    if (!is_array($data)) {
        $error = dcs_t('admin.settings_backup.no_settings_data');
        return false;
    }

    $root = dirname(__DIR__);

    if (isset($data['site_config']) && is_array($data['site_config'])) {
        $existing = readJsonFileForBackup($root . '/site_config.json', []);
        $safeConfig = array_merge($existing ?: [], cleanSiteConfigForImport($data['site_config']));
        if (!writeJsonFileFromBackup($root . '/site_config.json', $safeConfig)) {
            $error = dcs_t('admin.settings_backup.restore_site_config_failed');
            return false;
        }
    }

    if (isset($data['site_features']) && is_array($data['site_features'])) {
        if (!saveSiteFeatures($data['site_features'])) {
            $error = dcs_t('admin.settings_backup.restore_site_features_failed');
            return false;
        }
    }

    if (isset($data['site_metadata']) && is_array($data['site_metadata'])) {
        if (!saveSiteMetadata($data['site_metadata'])) {
            $error = dcs_t('admin.settings_backup.restore_site_metadata_failed');
            return false;
        }
    }

    $jsonSections = [
        'menu_config' => settingsBackupDataPath('menu_config.json'),
        'chart_theme' => settingsBackupDataPath('chart_theme.json'),
        'header_image' => settingsBackupDataPath('header_image.json')
    ];

    foreach ($jsonSections as $section => $path) {
        if (isset($data[$section]) && is_array($data[$section])) {
            if (!writeJsonFileFromBackup($path, $data[$section])) {
                $error = dcs_t('admin.settings_backup.restore_section_failed', ['section' => settingsBackupSectionLabel($section)]);
                return false;
            }
        }
    }

    $textSections = [
        'custom_theme_css' => $root . '/custom_theme.css',
        'header_custom_css' => $root . '/header_custom.css'
    ];

    foreach ($textSections as $section => $path) {
        if (isset($data[$section]) && is_string($data[$section])) {
            if (@file_put_contents($path, $data[$section]) === false) {
                $error = dcs_t('admin.settings_backup.restore_section_failed', ['section' => settingsBackupSectionLabel($section)]);
                return false;
            }
        }
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = dcs_t('admin.settings_backup.csrf_invalid');
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'export_settings') {
            $backup = buildSiteSettingsBackup();
            $fileName = 'dcs-site-settings-' . date('Y-m-d-H-i-s') . '.json';

            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            echo json_encode($backup, JSON_PRETTY_PRINT);
            exit;
        }

        if ($action === 'import_settings') {
            if (!isset($_FILES['settings_file']) || $_FILES['settings_file']['error'] !== UPLOAD_ERR_OK) {
                $message = dcs_t('admin.settings_backup.select_file');
                $messageType = 'error';
            } elseif ($_FILES['settings_file']['size'] > 2 * 1024 * 1024) {
                $message = dcs_t('admin.settings_backup.file_too_large');
                $messageType = 'error';
            } else {
                $backup = json_decode(file_get_contents($_FILES['settings_file']['tmp_name']), true);
                $importError = '';

                if (importSiteSettingsBackup($backup, $importError)) {
                    logAdminActivity('SITE_SETTINGS_IMPORT', $_SESSION['admin_id'], 'settings', 'site_settings_backup', [
                        'schema_version' => $backup['schema_version'] ?? null,
                        'exported_at' => $backup['exported_at'] ?? null
                    ]);
                    $message = dcs_t('admin.settings_backup.restore_success');
                    $messageType = 'success';
                } else {
                    $message = $importError ?: dcs_t('admin.settings_backup.invalid_file');
                    $messageType = 'error';
                }
            }
        }
    }
}

$pageTitle = dcs_t('admin.settings_backup.title');
$backupPreview = buildSiteSettingsBackup();
?>
<!DOCTYPE html>
<html lang="<?= e(dcs_default_language()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .backup-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .settings-note {
            background-color: rgba(76, 175, 80, 0.08);
            border: 1px solid rgba(76, 175, 80, 0.28);
            border-radius: 6px;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 20px;
            padding: 15px;
        }

        .included-list,
        .excluded-list {
            display: grid;
            gap: 8px;
            margin: 12px 0 0;
            padding-left: 18px;
        }

        .file-input-row {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 14px 0;
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'nav.php'; ?>

    <main class="admin-main">
        <header class="admin-header">
            <h1><?= e($pageTitle) ?></h1>
            <div class="admin-user-menu">
                <div class="admin-user-info">
                    <div class="admin-username"><?= e($currentAdmin['username']) ?></div>
                    <div class="admin-role"><?= getRoleBadge($currentAdmin['role']) ?></div>
                </div>
                <a href="logout.php" class="btn btn-secondary btn-small"><?= e(dcs_t('admin.common.logout')) ?></a>
            </div>
        </header>

        <div class="admin-content">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>

            <div class="settings-note">
                <?= e(dcs_t('admin.settings_backup.note')) ?>
            </div>

            <div class="backup-grid">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.settings_backup.export_settings')) ?></h2>
                    </div>
                    <p class="text-muted"><?= e(dcs_t('admin.settings_backup.export_text')) ?></p>
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="export_settings">
                        <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.settings_backup.download_button')) ?></button>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.settings_backup.restore_settings')) ?></h2>
                    </div>
                    <p class="text-muted"><?= e(dcs_t('admin.settings_backup.restore_text')) ?></p>
                    <form method="POST" enctype="multipart/form-data" onsubmit="return confirm(this.dataset.confirmMessage);" data-confirm-message="<?= e(dcs_t('admin.settings_backup.restore_confirm')) ?>">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="import_settings">
                        <div class="file-input-row">
                            <input type="file" name="settings_file" id="settings_file" accept=".json,application/json" required>
                        </div>
                        <button type="submit" class="btn btn-warning"><?= e(dcs_t('admin.settings_backup.restore_button')) ?></button>
                    </form>
                </div>
            </div>

            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h2 class="card-title"><?= e(dcs_t('admin.settings_backup.backup_contents')) ?></h2>
                </div>
                <div class="backup-grid">
                    <div>
                        <h3><?= e(dcs_t('admin.settings_backup.included')) ?></h3>
                        <ul class="included-list">
                            <?php foreach ($backupPreview['includes'] as $item): ?>
                                <li><?= e(settingsBackupSectionLabel($item)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <h3><?= e(dcs_t('admin.settings_backup.excluded')) ?></h3>
                        <ul class="excluded-list">
                            <?php foreach ($backupPreview['excluded'] as $item): ?>
                                <li><?= e(settingsBackupSectionLabel($item)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
