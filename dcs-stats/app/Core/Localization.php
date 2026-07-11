<?php

namespace DcsStats\Core;

final class Localization
{
    private static ?LanguageRegistry $languageRegistry = null;
    private static ?LocalizationConfiguration $configuration = null;
    private static ?LocalizationTranslator $translator = null;

    public static function builtInLanguages(): array
    {
        return self::languageRegistry()->builtInLanguages();
    }

    public static function customLanguageRegistryPath(): string
    {
        return self::languageRegistry()->registryPath();
    }

    public static function customLanguageDir(): string
    {
        return self::languageRegistry()->languageDir();
    }

    public static function normalizeCode($language): string
    {
        return self::languageRegistry()->normalizeCode($language);
    }

    public static function customLanguages(): array
    {
        return self::languageRegistry()->customLanguages();
    }

    public static function supportedLanguages(): array
    {
        return self::languageRegistry()->supportedLanguages();
    }

    public static function languageCode($language = null): string
    {
        return self::languageRegistry()->languageCode($language);
    }

    public static function defaultLanguage(): string
    {
        return self::configuration()->defaultLanguage();
    }

    public static function siteConfig(): array
    {
        return self::configuration()->siteConfig();
    }

    public static function dateFormatOptions(): array
    {
        return self::configuration()->dateFormatOptions();
    }

    public static function publicDateFormat(): string
    {
        return self::configuration()->publicDateFormat();
    }

    public static function setLanguageOverride($language): void
    {
        self::configuration()->setLanguageOverride($language);
    }

    public static function loadTranslations($language): array
    {
        return self::translator()->load($language);
    }

    public static function translate(string $key, array $replace = []): string
    {
        return self::translator()->translate($key, $replace, self::defaultLanguage());
    }

    public static function languageName($language = null): string
    {
        $language = self::languageCode($language ?? self::defaultLanguage());
        $supported = self::supportedLanguages();

        return $supported[$language] ?? $supported['en'];
    }

    private static function languageRegistry(): LanguageRegistry
    {
        if (self::$languageRegistry === null) {
            self::$languageRegistry = new LanguageRegistry();
        }

        return self::$languageRegistry;
    }

    private static function configuration(): LocalizationConfiguration
    {
        if (self::$configuration === null) {
            self::$configuration = new LocalizationConfiguration(self::languageRegistry());
        }

        return self::$configuration;
    }

    private static function translator(): LocalizationTranslator
    {
        if (self::$translator === null) {
            self::$translator = new LocalizationTranslator(self::languageRegistry());
        }

        return self::$translator;
    }
}
