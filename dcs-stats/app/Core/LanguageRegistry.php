<?php

namespace DcsStats\Core;

final class LanguageRegistry
{
    public function builtInLanguages(): array
    {
        return [
            'en' => 'English',
            'de' => 'Deutsch',
            'it' => 'Italiano',
        ];
    }

    public function registryPath(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/languages.json';
    }

    public function languageDir(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/languages';
    }

    public function normalizeCode($language): string
    {
        $language = strtolower(trim((string)$language));
        $language = str_replace('_', '-', $language);

        return preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $language) ? $language : '';
    }

    public function customLanguages(): array
    {
        if (!file_exists($this->registryPath())) {
            return [];
        }

        $data = json_decode((string)@file_get_contents($this->registryPath()), true);
        if (!is_array($data)) {
            return [];
        }

        $languages = [];
        $builtIn = $this->builtInLanguages();
        foreach ($data as $code => $info) {
            $code = $this->normalizeCode($code);
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

    public function supportedLanguages(): array
    {
        $languages = $this->builtInLanguages();
        foreach ($this->customLanguages() as $code => $info) {
            $languages[$code] = $info['name'];
        }

        return $languages;
    }

    public function languageCode($language = null): string
    {
        $supported = $this->supportedLanguages();
        $language = $this->normalizeCode($language);

        return isset($supported[$language]) ? $language : 'en';
    }
}
