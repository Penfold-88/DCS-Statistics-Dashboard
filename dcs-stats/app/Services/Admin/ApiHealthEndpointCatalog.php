<?php

namespace DcsStats\Services\Admin;

final class ApiHealthEndpointCatalog
{
    public function endpoints(): array
    {
        return [
            ['label' => \dcs_t('servers.title'), 'method' => 'GET', 'endpoint' => '/servers', 'note' => \dcs_t('admin.api_health.note_servers')],
            ['label' => \dcs_t('admin.api_health.server_stats'), 'method' => 'GET', 'endpoint' => '/serverstats', 'note' => \dcs_t('admin.api_health.note_serverstats')],
            ['label' => \dcs_t('admin.api_health.attendance'), 'method' => 'GET', 'endpoint' => '/server_attendance', 'note' => \dcs_t('admin.api_health.note_attendance')],
            ['label' => \dcs_t('leaderboard.title'), 'method' => 'GET', 'endpoint' => '/leaderboard?what=kills&limit=1', 'note' => \dcs_t('admin.api_health.note_leaderboard')],
            ['label' => \dcs_t('squadrons.title'), 'method' => 'GET', 'endpoint' => '/squadrons', 'note' => \dcs_t('admin.api_health.note_squadrons')],
            ['label' => \dcs_t('admin.api_health.current_server'), 'method' => 'GET', 'endpoint' => '/current_server', 'note' => \dcs_t('admin.api_health.note_current_server')],
        ];
    }
}
