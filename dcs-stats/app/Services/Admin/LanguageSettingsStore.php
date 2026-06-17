<?php

namespace DcsStats\Services\Admin;

final class LanguageSettingsStore
{
    public function save(array $post, array $siteConfig, string $siteConfigFile): array
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
}
