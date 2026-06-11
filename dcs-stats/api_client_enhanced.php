<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/api_client.php';
require_once __DIR__ . '/api_config_helper.php';

function createEnhancedAPIClient() {
    $result = loadApiConfigWithFix();
    $config = $result['config'];

    return new \DcsStats\Services\Api\EnhancedDcsServerBotApiClient($config);
}
