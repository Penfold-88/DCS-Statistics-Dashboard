<?php

namespace DcsStats\Core;

final class Localization
{
    private static ?string $defaultLanguage = null;
    private static ?array $siteConfig = null;
    private static array $translationCache = [];

    public static function builtInLanguages(): array
    {
        return [
            'en' => 'English',
            'de' => 'Deutsch',
            'it' => 'Italiano',
        ];
    }

    public static function customLanguageRegistryPath(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/languages.json';
    }

    public static function customLanguageDir(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/languages';
    }

    public static function normalizeCode($language): string
    {
        $language = strtolower(trim((string)$language));
        $language = str_replace('_', '-', $language);

        return preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $language) ? $language : '';
    }

    public static function customLanguages(): array
    {
        $path = self::customLanguageRegistryPath();
        if (!file_exists($path)) {
            return [];
        }

        $data = json_decode((string)@file_get_contents($path), true);
        if (!is_array($data)) {
            return [];
        }

        $languages = [];
        $builtIn = self::builtInLanguages();
        foreach ($data as $code => $info) {
            $code = self::normalizeCode($code);
            if ($code === '' || isset($builtIn[$code]) || !is_array($info) || empty($info['name'])) {
                continue;
            }

            $file = $info['file'] ?? ($code . '.json');
            if (!preg_match('/^[a-z0-9-]+\.json$/i', $file)) {
                continue;
            }

            $languages[$code] = [
                'name' => (string)$info['name'],
                'file' => $file,
                'uploaded_at' => $info['uploaded_at'] ?? null,
            ];
        }

        return $languages;
    }

    public static function supportedLanguages(): array
    {
        $languages = self::builtInLanguages();
        foreach (self::customLanguages() as $code => $info) {
            $languages[$code] = $info['name'];
        }

        return $languages;
    }

    public static function languageCode($language = null): string
    {
        $supported = self::supportedLanguages();
        $language = self::normalizeCode($language);

        return isset($supported[$language]) ? $language : 'en';
    }

    public static function defaultLanguage(): string
    {
        $override = $GLOBALS['dcs_language_override'] ?? null;
        if ($override !== null) {
            return self::languageCode($override);
        }

        if (self::$defaultLanguage !== null) {
            return self::$defaultLanguage;
        }

        $config = self::siteConfig();
        self::$defaultLanguage = self::languageCode($config['default_language'] ?? 'en');

        return self::$defaultLanguage;
    }

    public static function siteConfig(): array
    {
        if (self::$siteConfig !== null) {
            return self::$siteConfig;
        }

        $configFile = DCS_ROOT_PATH . '/site_config.json';
        if (!file_exists($configFile)) {
            self::$siteConfig = [];
            return self::$siteConfig;
        }

        $data = json_decode((string)@file_get_contents($configFile), true);
        self::$siteConfig = is_array($data) ? $data : [];

        return self::$siteConfig;
    }

    public static function dateFormatOptions(): array
    {
        return [
            'd/m/Y' => '2/5/2025',
            'm/d/Y' => '5/2/2025',
            'Y-m-d' => '2025-5-2',
            'd-m-Y' => '2-5-2025',
            'm-d-Y' => '5-2-2025',
            'Y/m/d' => '2025/5/2',
        ];
    }

    public static function publicDateFormat(): string
    {
        $config = self::siteConfig();
        $format = (string)($config['date_format'] ?? 'd/m/Y');
        $options = self::dateFormatOptions();

        return isset($options[$format]) ? $format : 'd/m/Y';
    }

    public static function setLanguageOverride($language): void
    {
        $GLOBALS['dcs_language_override'] = self::languageCode($language);
    }

    public static function loadTranslations($language): array
    {
        $language = self::languageCode($language);
        if (isset(self::$translationCache[$language])) {
            return self::$translationCache[$language];
        }

        $translations = [];
        $file = DCS_ROOT_PATH . '/lang/' . $language . '.php';
        if (file_exists($file)) {
            $translations = require $file;
        } else {
            $customLanguages = self::customLanguages();
            if (isset($customLanguages[$language])) {
                $customFile = self::customLanguageDir() . '/' . $customLanguages[$language]['file'];
                $customData = file_exists($customFile) ? json_decode((string)@file_get_contents($customFile), true) : [];
                $translations = $customData['translations'] ?? $customData;
            }
        }

        self::$translationCache[$language] = is_array($translations) ? $translations : [];

        return self::$translationCache[$language];
    }

    public static function translate(string $key, array $replace = []): string
    {
        $language = self::defaultLanguage();
        $translations = self::loadTranslations($language);
        $fallback = $language === 'en' ? [] : self::loadTranslations('en');
        $text = $translations[$key] ?? $fallback[$key] ?? $key;

        foreach ($replace as $name => $value) {
            $text = str_replace('{' . $name . '}', (string)$value, $text);
        }

        return $text;
    }

    public static function languageName($language = null): string
    {
        $language = self::languageCode($language ?? self::defaultLanguage());
        $supported = self::supportedLanguages();

        return $supported[$language] ?? $supported['en'];
    }
}
