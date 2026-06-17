<?php

namespace DcsStats\Services\Admin;

final class LanguageSettingsActionService
{
    private LanguageSettingsStore $settingsStore;
    private LanguageTranslationUploadService $uploadService;

    public function __construct(?LanguageSettingsStore $settingsStore = null, ?LanguageTranslationUploadService $uploadService = null)
    {
        $this->settingsStore = $settingsStore ?? new LanguageSettingsStore();
        $this->uploadService = $uploadService ?? new LanguageTranslationUploadService();
    }

    public function handle(array $post, array $files, array $siteConfig, string $siteConfigFile, bool $demoRestricted): array
    {
        if (!isset($post['csrf_token']) || !\verifyCSRFToken($post['csrf_token'])) {
            return [$siteConfig, ERROR_MESSAGES['csrf_invalid'], 'error'];
        }

        if ($demoRestricted) {
            return [$siteConfig, \demoWriteLockMessage(), 'error'];
        }

        $action = $post['action'] ?? 'save_language';
        if ($action === 'upload_translation') {
            [$message, $messageType] = $this->uploadService->handle($files);
            return [$siteConfig, $message, $messageType];
        }

        return $this->settingsStore->save($post, $siteConfig, $siteConfigFile);
    }
}
