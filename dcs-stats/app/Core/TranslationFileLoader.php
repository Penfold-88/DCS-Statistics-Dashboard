<?php

namespace DcsStats\Core;

final class TranslationFileLoader
{
    private LanguageRegistry $languageRegistry;

    public function __construct(?LanguageRegistry $languageRegistry = null)
    {
        $this->languageRegistry = $languageRegistry ?? new LanguageRegistry();
    }

    public function load(string $language): array
    {
        $translations = [];
        $file = DCS_ROOT_PATH . '/lang/' . $language . '.php';
        if (file_exists($file)) {
            $translations = require $file;
        } else {
            $customLanguages = $this->languageRegistry->customLanguages();
            if (isset($customLanguages[$language])) {
                $customFile = $this->languageRegistry->languageDir() . '/' . $customLanguages[$language]['file'];
                $customData = file_exists($customFile) ? json_decode((string)@file_get_contents($customFile), true) : [];
                $translations = $customData['translations'] ?? $customData;
            }
        }

        return is_array($translations) ? $translations : [];
    }
}
