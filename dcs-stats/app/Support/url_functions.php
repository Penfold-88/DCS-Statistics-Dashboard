<?php

use DcsStats\Core\Url;

if (!function_exists('getBasePath')) {
    function getBasePath() {
        return Url::basePath();
    }
}

if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
        return Url::baseUrl();
    }
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', getBasePath());
}

if (!defined('BASE_URL')) {
    define('BASE_URL', getBaseUrl());
}

if (!function_exists('url')) {
    function url($path = '') {
        return Url::to((string)$path);
    }
}

if (!function_exists('assetUrl')) {
    function assetUrl($path = '') {
        return Url::asset((string)$path);
    }
}

if (!function_exists('isDemoMode')) {
    function isDemoMode() {
        return Url::isDemoMode();
    }
}

if (!function_exists('absoluteUrl')) {
    function absoluteUrl($path = '') {
        return Url::absolute((string)$path);
    }
}

if (!function_exists('getJsConfig')) {
    function getJsConfig() {
        return Url::jsConfig();
    }
}
