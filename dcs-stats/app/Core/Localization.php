<?php

namespace DcsStats\Core;

final class Localization
{
    private static ?string $defaultLanguage = null;
    private static ?array $siteConfig = null;
    private static array $translationCache = [];

    public static function builtInLanguages(): array
    {
        return (new LanguageRegistry())->builtInLanguages();
    }

    public static function customLanguageRegistryPath(): string
    {
        return (new LanguageRegistry())->registryPath();
    }

    public static function customLanguageDir(): string
    {
        return (new LanguageRegistry())->languageDir();
    }

    public static function normalizeCode($language): string
    {
        return (new LanguageRegistry())->normalizeCode($language);
    }

    public static function customLanguages(): array
    {
        return (new LanguageRegistry())->customLanguages();
    }

    public static function supportedLanguages(): array
    {
        return (new LanguageRegistry())->supportedLanguages();
    }

    public static function languageCode($language = null): string
    {
        return (new LanguageRegistry())->languageCode($language);
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

        self::$translationCache[$language] = (new TranslationFileLoader())->load($language);

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
