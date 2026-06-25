<?php

namespace DcsStats\Services\Api;

final class ApiProxyEndpointPolicy
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

    public function allows(string $endpointPath, string $method): bool
    {
        return isset($this->allowedEndpoints[$endpointPath])
            && in_array($method, $this->allowedEndpoints[$endpointPath], true);
    }

    public function queryParamsFor(string $endpointPath): array
    {
        return $this->allowedQueryParams[$endpointPath] ?? [];
    }

    public function postFieldsFor(string $endpointPath): array
    {
        return $this->allowedPostFields[$endpointPath] ?? [];
    }
}
