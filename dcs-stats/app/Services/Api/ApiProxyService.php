<?php

namespace DcsStats\Services\Api;

final class ApiProxyService
{
    private ApiProxyEndpointPolicy $endpointPolicy;
    private ApiProxyHttpClient $httpClient;
    private ApiProxyRequestBuilder $requestBuilder;

    public function __construct(
        ?ApiProxyEndpointPolicy $endpointPolicy = null,
        ?ApiProxyHttpClient $httpClient = null,
        ?ApiProxyRequestBuilder $requestBuilder = null
    ) {
        $this->endpointPolicy = $endpointPolicy ?? new ApiProxyEndpointPolicy();
        $this->httpClient = $httpClient ?? new ApiProxyHttpClient();
        $this->requestBuilder = $requestBuilder ?? new ApiProxyRequestBuilder($this->endpointPolicy);
    }

    public function handle(string $endpoint, string $method, array $postData): void
    {
        \DcsStats\Core\SupportBootstrap::apiConfig();
        \DcsStats\Core\SupportBootstrap::apiCache();

        $apiConfig = loadApiConfigWithFix()['config'];
        if (!$apiConfig['use_api'] || empty($apiConfig['api_base_url'])) {
            $this->jsonError($this->unavailableMessage($apiConfig), 503);
            return;
        }

        $request = $this->requestBuilder->build($endpoint, $method, $postData);
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
            $this->jsonError($this->unavailableMessage($apiConfig), 502);
            return;
        }

        if ((int)$response['http_code'] >= 300 && (int)$response['http_code'] < 400) {
            $this->jsonError('Upstream API redirects are not allowed', 502);
            return;
        }

        http_response_code($response['http_code']);
        header('X-DCS-API-Cache: MISS');
        apiCacheWrite($request['method'], $apiConfig['api_base_url'], $request['cache_endpoint'], $cacheData, $apiConfig, $response['body'], $response['http_code']);

        echo $response['body'];
    }

    private function jsonError(string $message, int $statusCode): void
    {
        http_response_code($statusCode);
        echo json_encode(['error' => $message]);
    }

    private function unavailableMessage(array $apiConfig): string
    {
        $message = trim((string)($apiConfig['unavailable_message'] ?? ''));
        return $message !== '' ? $message : 'API Currently Unavailable';
    }
}
