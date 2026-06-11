<?php

if (!defined('DCS_ROOT_PATH')) {
    define('DCS_ROOT_PATH', __DIR__);
}

if (!defined('DCS_APP_PATH')) {
    define('DCS_APP_PATH', DCS_ROOT_PATH . '/app');
}

require_once DCS_APP_PATH . '/Core/Url.php';

use DcsStats\Core\Url;

function getBasePath() {
    return Url::basePath();
}

function getBaseUrl() {
    return Url::baseUrl();
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', getBasePath());
}

if (!defined('BASE_URL')) {
    define('BASE_URL', getBaseUrl());
}

function url($path = '') {
    return Url::to((string)$path);
}

function assetUrl($path = '') {
    return Url::asset((string)$path);
}

if (!function_exists('isDemoMode')) {
    function isDemoMode() {
        return Url::isDemoMode();
    }
}

function absoluteUrl($path = '') {
    return Url::absolute((string)$path);
}

function getJsConfig() {
    return Url::jsConfig();
}
