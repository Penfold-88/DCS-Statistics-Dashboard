<?php

namespace DcsStats\Services\Api;

final class ServerListService
{
    public function getServers(): array
    {
        \DcsStats\Core\SupportBootstrap::apiClient();

        $client = createEnhancedAPIClient();
        $servers = $client->request('/servers', null, 'GET');

        return is_array($servers) ? $servers : [];
    }
}

