<?php

function createEnhancedAPIClient() {
    \DcsStats\Core\SupportBootstrap::apiConfig();
    $result = loadApiConfigWithFix();
    $config = $result['config'];

    return new \DcsStats\Services\Api\EnhancedDcsServerBotApiClient($config);
}
