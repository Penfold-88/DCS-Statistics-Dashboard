<?php

namespace DcsStats\Core;

final class LocalizationTranslator
{
    private LanguageRegistry $languageRegistry;
    private TranslationFileLoader $fileLoader;
    private array $translationCache = [];

    public function __construct(
        ?LanguageRegistry $languageRegistry = null,
        ?TranslationFileLoader $fileLoader = null
    ) {
        $this->languageRegistry = $languageRegistry ?? new LanguageRegistry();
        $this->fileLoader = $fileLoader ?? new TranslationFileLoader($this->languageRegistry);
    }

    public function load($language): array
    {
        $language = $this->languageRegistry->languageCode($language);
        if (!isset($this->translationCache[$language])) {
            $this->translationCache[$language] = $this->fileLoader->load($language);
        }

        return $this->translationCache[$language];
    }

    public function translate(string $key, array $replace, string $language): string
    {
        $translations = $this->load($language);
        $fallback = $language === 'en' ? [] : $this->load('en');
        $text = $translations[$key] ?? $fallback[$key] ?? $key;

        foreach ($replace as $name => $value) {
            $text = str_replace('{' . $name . '}', (string)$value, $text);
        }

        return $text;
    }
}
