<?php

namespace DcsStats\Services\Api;

final class DcsServerBotMockDataProvider
{
    public function get($endpoint, $data = null)
    {
        $endpoint = strtok($endpoint, '?');

        switch ($endpoint) {
            case '/getuser':
                return [[
                    'nick' => $data['nick'] ?? 'TestPilot',
                    'date' => date('Y-m-d H:i:s'),
                    'userid' => '123456789',
                    'ucid' => 'abc-def-ghi-jkl',
                    'ipaddr' => '192.168.1.100'
                ]];

            case '/stats':
                return [
                    'nick' => $data['nick'] ?? 'TestPilot',
                    'kills' => rand(10, 100),
                    'deaths' => rand(5, 50),
                    'kd' => round(rand(50, 300) / 100, 2),
                    'pvp_kills' => rand(5, 50),
                    'pvp_deaths' => rand(2, 25),
                    'ejections' => rand(0, 10),
                    'crashes' => rand(0, 5),
                    'teamkills' => rand(0, 3),
                    'flighttime' => rand(1000, 10000),
                    'last_seen' => date('Y-m-d H:i:s')
                ];

            case '/topkills':
                $players = [];
                for ($i = 1; $i <= 20; $i++) {
                    $players[] = [
                        'nick' => 'Pilot_' . $i,
                        'kills' => 100 - ($i * 4),
                        'deaths' => rand(10, 50),
                        'kd' => round((100 - ($i * 4)) / rand(10, 50), 2),
                        'pvp_kills' => 50 - ($i * 2),
                        'server' => 'Dev Server'
                    ];
                }
                return $players;

            case '/topkdr':
                $players = [];
                for ($i = 1; $i <= 20; $i++) {
                    $players[] = [
                        'nick' => 'Ace_' . $i,
                        'kills' => rand(50, 100),
                        'deaths' => rand(10, 30),
                        'kd' => round(5.0 - ($i * 0.2), 2),
                        'pvp_kills' => rand(25, 50),
                        'server' => 'Dev Server'
                    ];
                }
                return $players;

            case '/credits':
                $players = [];
                for ($i = 1; $i <= 20; $i++) {
                    $players[] = [
                        'nick' => 'Rich_' . $i,
                        'credits' => 10000 - ($i * 400),
                        'server' => 'Dev Server'
                    ];
                }
                return $players;

            case '/servers':
                return [
                    [
                        'server_name' => 'Development Test Server',
                        'mission_name' => 'Caucasus - Training',
                        'players' => rand(5, 30),
                        'max_players' => 32,
                        'status' => 'online',
                        'uptime' => rand(1000, 10000)
                    ]
                ];

            case '/squadrons':
                return [
                    [
                        'name' => 'Test Squadron Alpha',
                        'tag' => 'TSA',
                        'members' => rand(10, 30),
                        'created' => date('Y-m-d', strtotime('-1 year'))
                    ],
                    [
                        'name' => 'Dev Squadron Beta',
                        'tag' => 'DSB',
                        'members' => rand(5, 20),
                        'created' => date('Y-m-d', strtotime('-6 months'))
                    ]
                ];

            case '/weaponpk':
                return [
                    'AIM-120C' => ['shots' => rand(20, 50), 'hits' => rand(10, 25), 'pk' => rand(40, 80)],
                    'AIM-9X' => ['shots' => rand(10, 30), 'hits' => rand(8, 25), 'pk' => rand(60, 90)],
                    'R-77' => ['shots' => rand(15, 40), 'hits' => rand(8, 20), 'pk' => rand(35, 75)]
                ];

            default:
                return [
                    'status' => 'success',
                    'message' => 'Dev mode - mock data',
                    'endpoint' => $endpoint,
                    'data' => []
                ];
        }
    }
}
