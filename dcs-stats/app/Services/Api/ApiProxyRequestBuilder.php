<?php

namespace DcsStats\Services\Api;

final class ApiProxyRequestBuilder
{
    private ApiProxyEndpointPolicy $endpointPolicy;

    public function __construct(?ApiProxyEndpointPolicy $endpointPolicy = null)
    {
        $this->endpointPolicy = $endpointPolicy ?? new ApiProxyEndpointPolicy();
    }

    public function build(string $endpoint, string $method, array $postData): array
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
}
