<?php

namespace DcsStats\Services\Admin;

final class ApiSettingsPageService
{
    private ApiSettingsActionService $actionService;

    public function __construct(?ApiSettingsActionService $actionService = null)
    {
        $this->actionService = $actionService ?? new ApiSettingsActionService();
    }

    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::apiConfig();
        \DcsStats\Core\SupportBootstrap::apiCache();
        \DcsStats\Core\SupportBootstrap::language();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $configResult = \loadApiConfigWithFix();
        $apiConfig = $configResult['config'];
        $configFile = $configResult['config_path'];
        $envApiKeyActive = \isEnvironmentApiKeyActive();
        $autoFixMessage = $this->autoFixMessage($configResult, $configFile, $demoRestricted);
        $message = '';
        $messageType = '';
        $testResult = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$apiConfig, $message, $messageType, $testResult] = $this->actionService->handle(
                $_POST,
                $apiConfig,
                $configFile,
                $envApiKeyActive,
                $demoRestricted
            );
        }

        $apiHostValue = $apiConfig['api_host'] ?? preg_replace('#^https?://#', '', $apiConfig['api_base_url']);

        return [
            'apiConfig' => $apiConfig,
            'apiHostValue' => $apiHostValue,
            'autoFixMessage' => $autoFixMessage,
            'configFile' => $configFile,
            'demoRestricted' => $demoRestricted,
            'displayApiHost' => $demoRestricted ? \maskDemoValue($apiHostValue) : $apiHostValue,
            'envApiKeyActive' => $envApiKeyActive,
            'message' => $message,
            'messageType' => $messageType,
            'pageTitle' => \dcs_t('admin.api.title'),
            'testResult' => $testResult,
        ];
    }

    private function autoFixMessage(array $configResult, string $configFile, bool $demoRestricted): string
    {
        $message = '';

        if (isset($configResult['fixed']) && $configResult['fixed'] && !empty($configResult['changes'])) {
            $message = \dcs_t('admin.api.auto_fixed') . ': ' . implode(', ', $configResult['changes']);
        }

        if (!$demoRestricted && $configFile !== DCS_ROOT_PATH . '/api_config.json') {
            $message .= ($message ? ' | ' : '') . \dcs_t('admin.api.config_location') . ': ' . $configFile;
        }

        return $message;
    }

}
