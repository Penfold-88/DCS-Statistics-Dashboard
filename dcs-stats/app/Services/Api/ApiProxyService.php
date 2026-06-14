<?php

namespace DcsStats\Services\Api;

final class ApiProxyService
{
    private ApiProxyEndpointPolicy $endpointPolicy;
    private ApiProxyHttpClient $httpClient;

    public function __construct(?ApiProxyEndpointPolicy $endpointPolicy = null, ?ApiProxyHttpClient $httpClient = null)
    {
        $this->endpointPolicy = $endpointPolicy ?? new ApiProxyEndpointPolicy();
        $this->httpClient = $httpClient ?? new ApiProxyHttpClient();
    }

    public function handle(string $endpoint, string $method, array $postData): void
    {
        \DcsStats\Core\SupportBootstrap::apiConfig();
        \DcsStats\Core\SupportBootstrap::apiCache();

        $apiConfig = loadApiConfigWithFix()['config'];
        if (!$apiConfig['use_api'] || empty($apiConfig['api_base_url'])) {
            $this->jsonError('API not enabled', 503);
            return;
        }

        $request = $this->prepareRequest($endpoint, $method, $postData);
        if (!empty($request['error'])) {
            $this->jsonError($request['error'], 400);
            return;
        }

        $cacheData = $request['method'] === 'POST' ? $request['data'] : null;
        $cached = apiCacheRead($request['method'], $apiConfig['api_base_url'], $request['cache_endpoint'], $cacheData, $apiConfig);
        if ($cached !== null) {
            http_response_code((int)($cached['http_code'] ?? 200));
            header('X-DCS-API-Cache: HIT');
            echo $cached['body'];
            return;
        }

        $response = $this->httpClient->send($apiConfig, $request);
        if (!empty($response['error'])) {
            $this->jsonError('API request failed: ' . $response['error'], 502);
            return;
        }

        http_response_code($response['http_code']);
        header('X-DCS-API-Cache: MISS');
        apiCacheWrite($request['method'], $apiConfig['api_base_url'], $request['cache_endpoint'], $cacheData, $apiConfig, $response['body'], $response['http_code']);

        echo $response['body'];
    }

    private function prepareRequest(string $endpoint, string $method, array $postData): array
    {
        if ($endpoint === '') {
            return ['error' => 'Endpoint parameter required'];
        }

        $endpointParts = parse_url($endpoint);
        $endpointPath = $endpointParts['path'] ?? '';
        if ($endpointPath === '' || $endpointPath[0] !== '/') {
            return ['error' => 'Endpoint path not allowed'];
        }

        if (!$this->endpointPolicy->allows($endpointPath, $method)) {
            return ['error' => 'Endpoint or method not allowed'];
        }

        $queryParams = [];
        if (!empty($endpointParts['query'])) {
            parse_str($endpointParts['query'], $queryParams);
        }
        $queryParams = $this->filterAllowedParams($queryParams, $this->endpointPolicy->queryParamsFor($endpointPath));
        $queryParams = $this->normalizeQueryAliases($endpointPath, $queryParams);

        $data = $method === 'POST'
            ? $this->filterAllowedParams($postData, $this->endpointPolicy->postFieldsFor($endpointPath))
            : [];

        $cacheEndpoint = $endpointPath;
        if (!empty($queryParams)) {
            $cacheEndpoint .= '?' . http_build_query($queryParams);
        }

        return [
            'method' => $method,
            'path' => $endpointPath,
            'query_params' => $queryParams,
            'data' => $data,
            'cache_endpoint' => $cacheEndpoint,
        ];
    }

    private function filterAllowedParams(array $params, array $allowedKeys): array
    {
        $filtered = [];
        foreach ($params as $key => $value) {
            if (!in_array($key, $allowedKeys, true) || is_array($value)) {
                continue;
            }
            $filtered[$key] = is_bool($value) ? ($value ? '1' : '0') : (string)$value;
        }

        return $filtered;
    }

    private function normalizeQueryAliases(string $endpointPath, array $queryParams): array
    {
        if (
            in_array($endpointPath, ['/airbase', '/airbase/atis', '/airbase/warehouse'], true) &&
            empty($queryParams['airbase_name']) &&
            !empty($queryParams['name'])
        ) {
            $queryParams['airbase_name'] = $queryParams['name'];
            unset($queryParams['name']);
        }

        return $queryParams;
    }

    private function jsonError(string $message, int $statusCode): void
    {
        http_response_code($statusCode);
        echo json_encode(['error' => $message]);
    }
}
