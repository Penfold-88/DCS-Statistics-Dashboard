<?php

namespace DcsStats\Services\Admin;

final class LanguageTranslationUploadService
{
    public function handle(array $files): array
    {
        $file = $files['translation_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return ['Please choose a valid translation JSON file.', 'error'];
        }

        if ((int)($file['size'] ?? 0) > 1048576 || !is_uploaded_file($file['tmp_name'])) {
            return ['Translation files must be valid uploads no larger than 1MB.', 'error'];
        }

        $upload = json_decode((string)file_get_contents($file['tmp_name']), true);
        if (!is_array($upload) || json_last_error() !== JSON_ERROR_NONE) {
            return ['The uploaded translation is not valid JSON.', 'error'];
        }

        $languageInfo = $upload['language'] ?? [];
        $rawCode = trim((string)($languageInfo['code'] ?? ($upload['code'] ?? '')));
        if (!preg_match('/^[a-z]{2}(?:-[A-Z]{2})?$/', $rawCode)) {
            return ['Use a language code such as fr or pt-BR.', 'error'];
        }
        $code = \dcs_normalize_language_code($rawCode);
        $name = trim((string)($languageInfo['name'] ?? ($upload['name'] ?? '')));
        $translations = $upload['translations'] ?? [];

        if ($code === '' || $name === '' || strlen($name) > 80 || preg_match('/[\x00-\x1F\x7F]/', $name) || !is_array($translations)) {
            return ['Translation files need a language code, language name, and translations list.', 'error'];
        }

        if (isset(\dcs_builtin_languages()[$code])) {
            return ['English and German are built in. Use a new language code for uploads.', 'error'];
        }

        $cleanTranslations = $this->filterTranslations($translations);
        if (empty($cleanTranslations)) {
            return ['No translated text was found for the current template keys.', 'error'];
        }

        $fileName = $code . '.json';
        $saved = $this->saveLanguageFile($fileName, $code, $name, $cleanTranslations);
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

    private function filterTranslations(array $translations): array
    {
        $cleanTranslations = [];
        foreach (\dcs_load_translations('en') as $key => $fallbackText) {
            if (isset($translations[$key]) && is_scalar($translations[$key])) {
                $value = trim((string)$translations[$key]);
                if ($value !== '') {
                    $cleanTranslations[$key] = $value;
                }
            }
        }

        return $cleanTranslations;
    }

    private function saveLanguageFile(string $fileName, string $code, string $name, array $translations): bool
    {
        $languageDir = \dcs_custom_language_dir();
        if (!is_dir($languageDir)) {
            mkdir($languageDir, 0700, true);
        }
        @chmod($languageDir, 0700);

        $storedLanguage = [
            'language' => [
                'code' => $code,
                'name' => $name,
            ],
            'translations' => $translations,
        ];

        $saved = file_put_contents(
            $languageDir . '/' . $fileName,
            json_encode($storedLanguage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        ) !== false;
        if ($saved) {
            @chmod($languageDir . '/' . $fileName, 0600);
        }
        return $saved;
    }

    private function saveLanguageRegistry(array $registry): bool
    {
        $path = \dcs_custom_language_registry_path();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $saved = file_put_contents($path, json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
        if ($saved) {
            @chmod($path, 0600);
        }
        return $saved;
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
