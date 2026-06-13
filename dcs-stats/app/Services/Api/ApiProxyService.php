<?php

namespace DcsStats\Services\Api;

final class ApiProxyService
{
    private array $allowedEndpoints = [
        '/airbase' => ['GET'],
        '/airbase/atis' => ['GET'],
        '/airbase/warehouse' => ['GET'],
        '/airbases' => ['GET'],
        '/convertCoordinates' => ['GET'],
        '/credits' => ['POST'],
        '/current_server' => ['GET'],
        '/events' => ['GET'],
        '/getuser' => ['POST'],
        '/highscore' => ['GET'],
        '/leaderboard' => ['GET'],
        '/linkme' => ['POST'],
        '/mission/group/waypoints' => ['GET'],
        '/modulestats' => ['POST'],
        '/player_info' => ['POST'],
        '/player_squadrons' => ['POST'],
        '/server_attendance' => ['GET'],
        '/servers' => ['GET'],
        '/serverstats' => ['GET'],
        '/squadron_credits' => ['POST'],
        '/squadron_members' => ['POST'],
        '/squadrons' => ['GET'],
        '/stats' => ['POST'],
        '/topkdr' => ['GET'],
        '/topkills' => ['GET'],
        '/traps' => ['POST'],
        '/traps/img' => ['GET'],
        '/trueskill' => ['GET'],
        '/weaponpk' => ['POST'],
    ];

    private array $allowedQueryParams = [
        '/airbase' => ['server_name', 'airbase_name', 'name'],
        '/airbase/atis' => ['server_name', 'airbase_name', 'name'],
        '/airbase/warehouse' => ['server_name', 'airbase_name', 'name'],
        '/airbases' => ['server_name'],
        '/convertCoordinates' => ['server_name', 'coordinates', 'lat', 'lon', 'mgrs', 'format'],
        '/current_server' => ['nick', 'date'],
        '/events' => ['ucid', 'start_time', 'end_time', 'offset', 'limit'],
        '/highscore' => ['server_name', 'period', 'limit', 'what', 'offset'],
        '/leaderboard' => ['server', 'server_name', 'what', 'order', 'query', 'limit', 'offset'],
        '/mission/group/waypoints' => ['server_name', 'group_name', 'group_type'],
        '/server_attendance' => ['server', 'server_name'],
        '/servers' => ['server', 'server_name'],
        '/serverstats' => ['server', 'server_name'],
        '/squadrons' => ['limit', 'offset'],
        '/topkdr' => ['server', 'server_name', 'limit', 'offset'],
        '/topkills' => ['server', 'server_name', 'limit', 'offset'],
        '/traps/img' => ['trap_id'],
        '/trueskill' => ['server', 'server_name', 'limit', 'offset'],
    ];

    private array $allowedPostFields = [
        '/credits' => ['nick', 'date', 'campaign', 'ucid', 'name'],
        '/getuser' => ['nick', 'discord_id', 'ucid', 'name', 'query', 'search'],
        '/linkme' => ['discord_id', 'force'],
        '/modulestats' => ['nick', 'date', 'server_name', 'ucid', 'name', 'limit', 'offset'],
        '/player_info' => ['nick', 'date', 'server_name', 'ucid', 'name'],
        '/player_squadrons' => ['nick', 'date', 'ucid', 'name'],
        '/squadron_credits' => ['name', 'campaign'],
        '/squadron_members' => ['name'],
        '/stats' => ['nick', 'date', 'server_name', 'last_session', 'ucid', 'name'],
        '/traps' => ['nick', 'date', 'limit', 'offset', 'server_name'],
        '/weaponpk' => ['nick', 'date', 'server_name', 'ucid', 'name', 'limit', 'offset'],
    ];

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

        $response = $this->sendUpstreamRequest($apiConfig, $request);
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

        if (!isset($this->allowedEndpoints[$endpointPath]) || !in_array($method, $this->allowedEndpoints[$endpointPath], true)) {
            return ['error' => 'Endpoint or method not allowed'];
        }

        $queryParams = [];
        if (!empty($endpointParts['query'])) {
            parse_str($endpointParts['query'], $queryParams);
        }
        $queryParams = $this->filterAllowedParams($queryParams, $this->allowedQueryParams[$endpointPath] ?? []);
        $queryParams = $this->normalizeQueryAliases($endpointPath, $queryParams);

        $data = $method === 'POST'
            ? $this->filterAllowedParams($postData, $this->allowedPostFields[$endpointPath] ?? [])
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

    private function sendUpstreamRequest(array $apiConfig, array $request): array
    {
        $url = rtrim($apiConfig['api_base_url'], '/') . '/' . ltrim($request['path'], '/');
        if (!empty($request['query_params'])) {
            $url .= '?' . http_build_query($request['query_params']);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $apiConfig['timeout'] ?? 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (stripos($url, 'https://') === 0) {
            $verifySsl = filter_var($apiConfig['verify_ssl'] ?? true, FILTER_VALIDATE_BOOLEAN);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySsl);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySsl ? 2 : 0);
        }

        $headers = [];
        if (!empty($apiConfig['api_key'])) {
            $headers[] = 'X-API-Key: ' . $apiConfig['api_key'];
        }

        if ($request['method'] === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, !empty($request['data']) ? http_build_query($request['data']) : '');
            if (!empty($request['data'])) {
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            }
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'body' => $body,
            'http_code' => $httpCode,
            'error' => $error,
        ];
    }

    private function jsonError(string $message, int $statusCode): void
    {
        http_response_code($statusCode);
        echo json_encode(['error' => $message]);
    }
}
