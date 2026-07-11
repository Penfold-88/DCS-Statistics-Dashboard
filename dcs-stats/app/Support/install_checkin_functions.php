<?php

use DcsStats\Services\InstallCheckinService;

function installCheckinService() {
    static $service = null;
    if ($service === null) {
        $service = new InstallCheckinService();
    }

    return $service;
}

function getInstallCheckinEndpoint() {
    return installCheckinService()->endpoint();
}

function getInstallCheckinToken() {
    return installCheckinService()->token();
}

function allowInstallCheckinSslFallback() {
    return installCheckinService()->allowSslFallback();
}

function getInstallCheckinStatePath() {
    return installCheckinService()->statePath();
}

function loadInstallCheckinState() {
    return installCheckinService()->loadState();
}

function saveInstallCheckinState($state) {
    installCheckinService()->saveState(is_array($state) ? $state : []);
}

function buildInstallCheckinPayload($payload) {
    return installCheckinService()->buildPayload(is_array($payload) ? $payload : []);
}

function sendInstallCheckinWithCurl($endpoint, $json, $headers, $verifySsl = true) {
    return installCheckinService()->sendWithCurl((string)$endpoint, (string)$json, is_array($headers) ? $headers : [], (bool)$verifySsl);
}

function sendInstallCheckinPayload($endpoint, $payload) {
    return installCheckinService()->sendPayload((string)$endpoint, is_array($payload) ? $payload : []);
}

function getInstallCheckinPayloadKey($payload) {
    return installCheckinService()->payloadKey(is_array($payload) ? $payload : []);
}

function runInstallCheckinIfDue($versionInfo = [], $updateChannel = [], $options = []) {
    return installCheckinService()->runIfDue(
        is_array($versionInfo) ? $versionInfo : [],
        is_array($updateChannel) ? $updateChannel : [],
        is_array($options) ? $options : []
    );
}
