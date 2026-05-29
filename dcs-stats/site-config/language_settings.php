<?php
/**
 * Language Settings
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin_functions.php';
require_once dirname(__DIR__) . '/language.php';

requireAdmin();
requirePermission('manage_features');

$currentAdmin = getCurrentAdmin();
$demoRestricted = isDemoRestricted($currentAdmin);
$siteConfigFile = dirname(__DIR__) . '/site_config.json';
$siteConfig = file_exists($siteConfigFile) ? (json_decode(file_get_contents($siteConfigFile), true) ?: []) : [];
$message = '';
$messageType = '';

function saveLanguageRegistry($registry) {
    $path = dcs_custom_language_registry_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return file_put_contents($path, json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function loadRawLanguageRegistry() {
    $path = dcs_custom_language_registry_path();
    if (!file_exists($path)) {
        return [];
    }
    $data = json_decode(@file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = ERROR_MESSAGES['csrf_invalid'];
        $messageType = 'error';
    } elseif ($demoRestricted) {
        $message = demoWriteLockMessage();
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? 'save_language';

        if ($action === 'upload_translation') {
            $file = $_FILES['translation_file'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                $message = 'Please choose a valid translation JSON file.';
                $messageType = 'error';
            } else {
                $upload = json_decode(file_get_contents($file['tmp_name']), true);
                $languageInfo = $upload['language'] ?? [];
                $code = dcs_normalize_language_code($languageInfo['code'] ?? ($upload['code'] ?? ''));
                $name = trim((string)($languageInfo['name'] ?? ($upload['name'] ?? '')));
                $translations = $upload['translations'] ?? [];
                $builtIn = dcs_builtin_languages();
                $english = dcs_load_translations('en');
                $cleanTranslations = [];

                if ($code === '' || $name === '' || !is_array($translations)) {
                    $message = 'Translation files need a language code, language name, and translations list.';
                    $messageType = 'error';
                } elseif (isset($builtIn[$code])) {
                    $message = 'English and German are built in. Use a new language code for uploads.';
                    $messageType = 'error';
                } else {
                    foreach ($english as $key => $fallbackText) {
                        if (isset($translations[$key]) && is_scalar($translations[$key])) {
                            $value = trim((string)$translations[$key]);
                            if ($value !== '') {
                                $cleanTranslations[$key] = $value;
                            }
                        }
                    }

                    if (empty($cleanTranslations)) {
                        $message = 'No translated text was found for the current template keys.';
                        $messageType = 'error';
                    } else {
                        $languageDir = dcs_custom_language_dir();
                        if (!is_dir($languageDir)) {
                            mkdir($languageDir, 0755, true);
                        }

                        $fileName = $code . '.json';
                        $storedLanguage = [
                            'language' => [
                                'code' => $code,
                                'name' => $name
                            ],
                            'translations' => $cleanTranslations
                        ];
                        $saved = file_put_contents(
                            $languageDir . '/' . $fileName,
                            json_encode($storedLanguage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ) !== false;

                        $registry = loadRawLanguageRegistry();
                        $registry[$code] = [
                            'name' => $name,
                            'file' => $fileName,
                            'uploaded_at' => date('c')
                        ];

                        if ($saved && saveLanguageRegistry($registry)) {
                            logAdminActivity('LANGUAGE_UPLOAD', $_SESSION['admin_id'], 'settings', 'language', ['language' => $code]);
                            $message = 'Translation uploaded successfully.';
                            $messageType = 'success';
                        } else {
                            $message = 'Failed to save the uploaded translation.';
                            $messageType = 'error';
                        }
                    }
                }
            }
        } else {
            $language = dcs_language_code($_POST['default_language'] ?? 'en');
            $siteConfig['default_language'] = $language;
            dcs_set_language_override($language);

            if (file_put_contents($siteConfigFile, json_encode($siteConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
                logAdminActivity('LANGUAGE_UPDATE', $_SESSION['admin_id'], 'settings', 'language', ['default_language' => $language]);
                $message = 'Language settings saved successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to save language settings.';
                $messageType = 'error';
            }
        }
    }
}

$supportedLanguages = dcs_supported_languages();
$builtInLanguages = dcs_builtin_languages();
$customLanguages = dcs_custom_languages();
$currentLanguage = dcs_language_code($siteConfig['default_language'] ?? 'en');
$pageTitle = dcs_t('admin.language.title');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Carrier Air Wing Command</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }
        .admin-sidebar { width: 250px; flex-shrink: 0; background: #2a2a2a; }
        .admin-main { flex: 1; min-width: 0; overflow-x: hidden; }
        .admin-content { padding: 30px; max-width: 100%; overflow-x: hidden; }
        .language-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            margin: 18px 0 24px;
        }
        .language-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: 8px;
            padding: 14px;
        }
        .language-card strong {
            color: var(--text-primary);
            display: block;
            font-size: 1rem;
            margin-bottom: 6px;
        }
        .language-meta {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .language-badge {
            background: var(--accent-primary);
            border-radius: 999px;
            color: var(--bg-primary);
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            margin-top: 10px;
            padding: 4px 9px;
            text-transform: uppercase;
        }
        .language-actions {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            color: var(--accent-primary);
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .form-control {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: 4px;
            color: var(--text-primary);
            max-width: 420px;
            padding: 10px;
            width: 100%;
        }
        .help-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-top: 8px;
        }
        .template-link {
            color: var(--accent-primary);
            display: inline-block;
            font-weight: 700;
            margin-bottom: 14px;
            text-decoration: none;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php include __DIR__ . '/nav.php'; ?>

        <main class="admin-main">
            <div class="admin-content">
                <?php if ($message): ?>
                    <div class="alert alert-<?= e($messageType) ?>"><?= e($message) ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= e(dcs_t('admin.language.translated_languages')) ?></h2>
                    </div>

                    <div class="language-grid">
                        <?php foreach ($supportedLanguages as $code => $label): ?>
                            <?php $isBuiltIn = isset($builtInLanguages[$code]); ?>
                            <div class="language-card">
                                <strong><?= e($label) ?></strong>
                                <div class="language-meta">
                                    <?= e(strtoupper($code)) ?> · <?= e($isBuiltIn ? dcs_t('admin.language.built_in') : dcs_t('admin.language.uploaded')) ?> · <?= e(dcs_t('admin.language.translated_keys', ['count' => count(dcs_load_translations($code))])) ?>
                                </div>
                                <?php if ($currentLanguage === $code): ?>
                                    <span class="language-badge"><?= e(dcs_t('admin.language.current')) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="language-actions" style="margin-top: 24px;">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title"><?= e(dcs_t('admin.language.default_language')) ?></h2>
                        </div>

                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="save_language">
                            <div class="form-group">
                                <label for="default_language"><?= e(dcs_t('admin.language.public_site_language')) ?></label>
                                <select id="default_language" name="default_language" class="form-control">
                                    <?php foreach ($supportedLanguages as $code => $label): ?>
                                    <option value="<?= e($code) ?>" <?= $currentLanguage === $code ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.language.save_language')) ?></button>
                        </form>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title"><?= e(dcs_t('admin.language.upload_translation')) ?></h2>
                        </div>

                        <a class="template-link" href="../lang/translation-template.json" download><?= e(dcs_t('admin.language.download_template')) ?></a>

                        <form method="POST" enctype="multipart/form-data">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="upload_translation">
                            <div class="form-group">
                                <label for="translation_file"><?= e(dcs_t('admin.language.translation_file')) ?></label>
                                <input id="translation_file" name="translation_file" type="file" class="form-control" accept=".json,application/json" required>
                                <div class="help-text"><?= e(dcs_t('admin.language.upload_help')) ?></div>
                            </div>

                            <button type="submit" class="btn btn-primary"><?= e(dcs_t('admin.language.upload_translation')) ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
