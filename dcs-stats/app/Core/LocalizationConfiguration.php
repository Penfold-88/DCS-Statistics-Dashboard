<?php

namespace DcsStats\Core;

final class LocalizationConfiguration
{
    private LanguageRegistry $languageRegistry;
    private ?string $defaultLanguage = null;
    private ?array $siteConfig = null;

    public function __construct(?LanguageRegistry $languageRegistry = null)
    {
        $this->languageRegistry = $languageRegistry ?? new LanguageRegistry();
    }

    public function defaultLanguage(): string
    {
        $override = $GLOBALS['dcs_language_override'] ?? null;
        if ($override !== null) {
            return $this->languageRegistry->languageCode($override);
        }

        if ($this->defaultLanguage === null) {
            $config = $this->siteConfig();
            $this->defaultLanguage = $this->languageRegistry->languageCode($config['default_language'] ?? 'en');
        }

        return $this->defaultLanguage;
    }

    public function siteConfig(): array
    {
        if ($this->siteConfig !== null) {
            return $this->siteConfig;
        }

        $configFile = DCS_ROOT_PATH . '/site_config.json';
        if (!file_exists($configFile)) {
            $this->siteConfig = [];
            return $this->siteConfig;
        }

        $data = json_decode((string)@file_get_contents($configFile), true);
        $this->siteConfig = is_array($data) ? $data : [];

        return $this->siteConfig;
    }

    public function dateFormatOptions(): array
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

    public function publicDateFormat(): string
    {
        $format = (string)($this->siteConfig()['date_format'] ?? 'd/m/Y');
        return isset($this->dateFormatOptions()[$format]) ? $format : 'd/m/Y';
    }

    public function setLanguageOverride($language): void
    {
        $GLOBALS['dcs_language_override'] = $this->languageRegistry->languageCode($language);
    }
}
