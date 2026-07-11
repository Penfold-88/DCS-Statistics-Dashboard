<?php

namespace DcsStats\Services;

final class DashboardPageService
{
    public function getHomePageState(): array
    {
        $showAttendanceCards = isFeatureEnabled('home_attendance_cards') && (
            isFeatureEnabled('home_api_players_24h') ||
            isFeatureEnabled('home_api_players_7d') ||
            isFeatureEnabled('home_api_players_30d') ||
            isFeatureEnabled('home_api_current_players')
        );
        $showApiInsights = isFeatureEnabled('home_api_insights');
        $showTopApiLists = $showApiInsights && (
            isFeatureEnabled('home_top_theatres') ||
            isFeatureEnabled('home_top_missions') ||
            isFeatureEnabled('home_top_modules')
        );
        $showTopPilotsChart = isFeatureEnabled('home_top_pilots');
        $showTopSquadronsChart = isFeatureEnabled('squadrons_enabled') && isFeatureEnabled('home_top_pilots');
        $showCoreServerStats = isFeatureEnabled('home_server_stats') ||
            isFeatureEnabled('home_mission_stats') ||
            isFeatureEnabled('home_player_activity');

        return [
            'showAttendanceCards' => $showAttendanceCards,
            'showApiInsights' => $showApiInsights,
            'showTopApiLists' => $showTopApiLists,
            'showTopPilotsChart' => $showTopPilotsChart,
            'showTopSquadronsChart' => $showTopSquadronsChart,
            'showCoreServerStats' => $showCoreServerStats,
            'homepageChartTheme' => loadChartTheme(),
        ];
    }
}

