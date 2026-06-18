<?php

namespace DcsStats\Services\Api;

final class PublicPlayerSearchService
{
    private PublicStatsFormatter $formatter;

    public function __construct(?PublicStatsFormatter $formatter = null)
    {
        $this->formatter = $formatter ?? new PublicStatsFormatter();
    }

    public function search(string $rawQuery): array
    {
        \DcsStats\Core\SupportBootstrap::security();
        \DcsStats\Core\SupportBootstrap::apiClient();
        \DcsStats\Core\SupportBootstrap::apiConfig();

        $query = validateInput($rawQuery, [
            'type' => 'search_query',
            'max_length' => 50,
            'min_length' => 2,
        ]);

        if ($query === false) {
            return ['error' => 'Invalid search query'];
        }

        try {
            $config = loadApiConfigWithFix()['config'];
            $apiClient = new \DCSServerBotAPIClient($config);

            try {
                $apiResponse = $apiClient->makeRequest('POST', '/getuser', ['nick' => $query]);
                $players = $this->formatter->formatPlayerSearchResults(is_array($apiResponse) ? $apiResponse : []);

                return [
                    'results' => $players,
                    'count' => count($players),
                    'source' => 'api',
                    'error' => count($players) === 0 ? 'No players found' : null,
                ];
            } catch (\Exception $apiError) {
                return [
                    'error' => 'Player search is currently unavailable',
                    'message' => 'The DCSServerBot /getuser endpoint is returning errors. This is a known issue with some DCSServerBot installations.',
                    'results' => [],
                    'count' => 0,
                    'source' => 'api',
                ];
            }
        } catch (\Exception $e) {
            return [
                'error' => 'Search service error',
                'results' => [],
                'count' => 0,
                'source' => 'api',
            ];
        }
    }
}
