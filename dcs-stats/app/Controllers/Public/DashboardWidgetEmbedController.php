<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;

final class DashboardWidgetEmbedController
{
    private const TYPES = ['summary', 'attendance', 'top-pilots', 'combat-stats', 'top-squadrons', 'player-activity', 'top-theatres', 'top-missions', 'top-modules'];

    public function index(): void
    {
        \DcsStats\Core\SupportBootstrap::load('language', 'siteFeatures');
        if (!Installation::isConfigured()) Installation::redirectToInstaller();
        $type = strtolower(trim((string)($_GET['widget'] ?? 'summary')));
        if (!in_array($type, self::TYPES, true)) $type = 'summary';
        $server = substr((string)preg_replace('/[\x00-\x1F\x7F]/u', '', trim((string)($_GET['server'] ?? ''))), 0, 120);
        $metric = in_array((string)($_GET['metric'] ?? ''), ['kills', 'kdr', 'kdr_pvp'], true) ? (string)$_GET['metric'] : 'kills';
        $limit = (int)($_GET['limit'] ?? 5);
        if (!in_array($limit, [3, 5, 10], true)) $limit = 5;

        header_remove('X-Frame-Options');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; connect-src 'self' http://localhost:* https://localhost:*; img-src 'self' data: https:; frame-ancestors *; base-uri 'none'; form-action 'none'");
        View::render('Public/dashboard_widget_embed.php', ['widgetType' => $type, 'serverFilter' => $server, 'metric' => $metric, 'limit' => $limit]);
    }
}
