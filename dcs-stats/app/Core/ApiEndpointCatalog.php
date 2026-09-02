<?php

namespace DcsStats\Core;

final class ApiEndpointCatalog
{
    public static function dashboardEndpoints(): array
    {
        return [
            'get_servers.php',
            'get_leaderboard.php',
            'get_player_stats.php',
            'get_pilot_credits.php',
            'get_pilot_statistics.php',
            'get_server_statistics.php',
            'get_active_players.php',
            'search_players.php',
            'get_leaderboard_client.php',
            'get_credits.php',
            'get_missionstats.php',
            'get_server_stats.php',
            'get_squadrons.php',
            'get_squadron_members.php',
            'get_squadron_credits.php',
        ];
    }

    public static function restEndpointMappings(): array
    {
        return [
            'getuser' => '/getuser',
            'stats' => '/stats',
            'player_info' => '/player_info',
            'topkills' => '/topkills',
            'topkdr' => '/topkdr',
            'leaderboard' => '/leaderboard',
            'highscore' => '/highscore',
            'trueskill' => '/trueskill',
            'modulestats' => '/modulestats',
            'traps' => '/traps',
            'weaponpk' => '/weaponpk',
            'credits' => '/credits',
            'servers' => '/servers',
            'serverstats' => '/serverstats',
            'server_attendance' => '/server_attendance',
            'current_server' => '/current_server',
            'mission_bullseyes' => '/mission/bullseyes',
            'mission_drawings' => '/mission/drawings',
            'mission_unit' => '/mission/unit',
            'squadrons' => '/squadrons',
            'player_squadrons' => '/player_squadrons',
            'squadron_members' => '/squadron_members',
            'squadron_credits' => '/squadron_credits',
        ];
    }
}
