<?php

use DcsStats\Core\ApiConfig;

function getDefaultApiConfig($apiHost = '') {
    return ApiConfig::defaults((string)$apiHost);
}

function getEnvironmentApiKey() {
    return ApiConfig::environmentApiKey();
}

function isEnvironmentApiKeyActive() {
    return ApiConfig::isEnvironmentApiKeyActive();
}

function applyEnvironmentApiConfigOverrides($config) {
    return ApiConfig::applyEnvironmentOverrides($config);
}

function validateAndFixApiConfig($config) {
    return ApiConfig::validateAndFix($config);
}

function getWritableConfigPath($preferredFile = null) {
    return ApiConfig::writablePath($preferredFile);
}

function loadApiConfigWithFix($configFile = null) {
    return ApiConfig::loadWithFix($configFile);
}

function createApiConfigFromHost($apiHost, $configFile = null) {
    return ApiConfig::createFromHost((string)$apiHost, $configFile);
}
