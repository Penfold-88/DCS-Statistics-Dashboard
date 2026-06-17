<?php

namespace DcsStats\Services\Admin;

final class LanguageSettingsActionService
{
    public function handle(array $post, array $files, array $siteConfig, string $siteConfigFile, bool $demoRestricted): array
    {
        if (!isset($post['csrf_token']) || !\verifyCSRFToken($post['csrf_token'])) {
            return [$siteConfig, ERROR_MESSAGES['csrf_invalid'], 'error'];
        }

        if ($demoRestricted) {
            return [$siteConfig, \demoWriteLockMessage(), 'error'];
        }

        $action = $post['action'] ?? 'save_language';
        if ($action === 'upload_translation') {
            [$message, $messageType] = $this->handleUpload($files);
            return [$siteConfig, $message, $messageType];
        }

        return $this->saveSettings($post, $siteConfig, $siteConfigFile);
    }

    private function handleUpload(array $files): array
    {
        $file = $files['translation_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return ['Please choose a valid translation JSON file.', 'error'];
        }

        $upload = json_decode((string)file_get_contents($file['tmp_name']), true);
        $languageInfo = $upload['language'] ?? [];
        $code = \dcs_normalize_language_code($languageInfo['code'] ?? ($upload['code'] ?? ''));
        $name = trim((string)($languageInfo['name'] ?? ($upload['name'] ?? '')));
        $translations = $upload['translations'] ?? [];
        $builtIn = \dcs_builtin_languages();
        $english = \dcs_load_translations('en');
        $cleanTranslations = [];

        if ($code === '' || $name === '' || !is_array($translations)) {
            return ['Translation files need a language code, language name, and translations list.', 'error'];
        }

        if (isset($builtIn[$code])) {
            return ['English and German are built in. Use a new language code for uploads.', 'error'];
        }

        foreach ($english as $key => $fallbackText) {
            if (isset($translations[$key]) && is_scalar($translations[$key])) {
                $value = trim((string)$translations[$key]);
                if ($value !== '') {
                    $cleanTranslations[$key] = $value;
                }
            }
        }

        if (empty($cleanTranslations)) {
            return ['No translated text was found for the current template keys.', 'error'];
        }

        $languageDir = \dcs_custom_language_dir();
        if (!is_dir($languageDir)) {
            mkdir($languageDir, 0755, true);
        }

        $fileName = $code . '.json';
        $storedLanguage = [
            'language' => [
                'code' => $code,
                'name' => $name,
            ],
            'translations' => $cleanTranslations,
        ];
        $saved = file_put_contents(
            $languageDir . '/' . $fileName,
            json_encode($storedLanguage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ) !== false;

        $registry = $this->loadRawLanguageRegistry();
        $registry[$code] = [
            'name' => $name,
            'file' => $fileName,
            'uploaded_at' => date('c'),
        ];

        if ($saved && $this->saveLanguageRegistry($registry)) {
            \logAdminActivity('LANGUAGE_UPLOAD', $_SESSION['admin_id'], 'settings', 'language', ['language' => $code]);
            return ['Translation uploaded successfully.', 'success'];
        }

        return ['Failed to save the uploaded translation.', 'error'];
    }

    private function saveSettings(array $post, array $siteConfig, string $siteConfigFile): array
    {
        $language = \dcs_language_code($post['default_language'] ?? 'en');
        $dateFormatOptions = \dcs_date_format_options();
        $dateFormat = (string)($post['date_format'] ?? 'd/m/Y');
        $siteConfig['default_language'] = $language;
        $siteConfig['date_format'] = isset($dateFormatOptions[$dateFormat]) ? $dateFormat : 'd/m/Y';
        \dcs_set_language_override($language);

        if (file_put_contents($siteConfigFile, json_encode($siteConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
            \logAdminActivity('LANGUAGE_UPDATE', $_SESSION['admin_id'], 'settings', 'language', [
                'default_language' => $language,
                'date_format' => $siteConfig['date_format'],
            ]);
            return [$siteConfig, 'Language settings saved successfully.', 'success'];
        }

        return [$siteConfig, 'Failed to save language settings.', 'error'];
    }

    private function saveLanguageRegistry(array $registry): bool
    {
        $path = \dcs_custom_language_registry_path();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($path, json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    private function loadRawLanguageRegistry(): array
    {
        $path = \dcs_custom_language_registry_path();
        if (!file_exists($path)) {
            return [];
        }

        $data = json_decode((string)@file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }
}
