<?php

namespace DcsStats\Services\Api;

final class ServerListService
{
    public function getServers(): array
    {
        require_once DCS_ROOT_PATH . '/api_client_enhanced.php';

        $client = createEnhancedAPIClient();
        $servers = $client->request('/servers', null, 'GET');

        return is_array($servers) ? $servers : [];
    }
}

