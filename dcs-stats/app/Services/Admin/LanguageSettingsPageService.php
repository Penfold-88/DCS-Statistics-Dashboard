<?php

namespace DcsStats\Services\Admin;

final class LanguageSettingsPageService
{
    private LanguageSettingsActionService $actionService;

    public function __construct(?LanguageSettingsActionService $actionService = null)
    {
        $this->actionService = $actionService ?? new LanguageSettingsActionService();
    }

    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::language();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $siteConfigFile = DCS_ROOT_PATH . '/site_config.json';
        $siteConfig = file_exists($siteConfigFile) ? (json_decode((string)file_get_contents($siteConfigFile), true) ?: []) : [];
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$siteConfig, $message, $messageType] = $this->actionService->handle($_POST, $_FILES, $siteConfig, $siteConfigFile, $demoRestricted);
        }

        $dateFormatOptions = \dcs_date_format_options();
        $currentDateFormat = isset($dateFormatOptions[$siteConfig['date_format'] ?? ''])
            ? $siteConfig['date_format']
            : \dcs_public_date_format();

        return [
            'builtInLanguages' => \dcs_builtin_languages(),
            'currentDateFormat' => $currentDateFormat,
            'currentLanguage' => \dcs_language_code($siteConfig['default_language'] ?? 'en'),
            'customLanguages' => \dcs_custom_languages(),
            'dateFormatOptions' => $dateFormatOptions,
            'demoRestricted' => $demoRestricted,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.language.title'),
            'siteConfig' => $siteConfig,
            'supportedLanguages' => \dcs_supported_languages(),
        ];
    }

}
