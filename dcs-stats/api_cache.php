<?php

require_once __DIR__ . '/app/bootstrap.php';

use DcsStats\Core\ApiCache;

function apiCacheDirectory() {
    return ApiCache::directory();
}

function apiCacheMaxFiles($config = []) {
    return ApiCache::maxFiles(is_array($config) ? $config : []);
}

function apiCachePrune($config = []) {
    return ApiCache::prune(is_array($config) ? $config : []);
}

function apiCacheNormalisePath($endpoint) {
    return ApiCache::normalisePath((string)$endpoint);
}

function apiCacheTtl($config) {
    return ApiCache::ttl(is_array($config) ? $config : []);
}

function apiCacheTtlForEndpoint($endpoint, $config) {
    return ApiCache::ttlForEndpoint((string)$endpoint, is_array($config) ? $config : []);
}

function apiCacheIsCacheable($method, $endpoint, $config = []) {
    return ApiCache::isCacheable((string)$method, (string)$endpoint, is_array($config) ? $config : []);
}

function apiCacheKey($method, $baseUrl, $endpoint, $data = null) {
    return ApiCache::key((string)$method, (string)$baseUrl, (string)$endpoint, $data);
}

function apiCacheFile($key) {
    return ApiCache::file((string)$key);
}

function apiCacheRead($method, $baseUrl, $endpoint, $data, $config) {
    return ApiCache::read((string)$method, (string)$baseUrl, (string)$endpoint, $data, is_array($config) ? $config : []);
}

function apiCacheWrite($method, $baseUrl, $endpoint, $data, $config, $body, $httpCode = 200) {
    return ApiCache::write((string)$method, (string)$baseUrl, (string)$endpoint, $data, is_array($config) ? $config : [], (string)$body, (int)$httpCode);
}

function apiCacheClear() {
    return ApiCache::clear();
}

function apiCacheLastRefreshTimestamp() {
    return ApiCache::lastRefreshTimestamp();
}
