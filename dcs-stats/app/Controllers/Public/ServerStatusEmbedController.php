<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Core\Installation;
use DcsStats\Core\View;

final class ServerStatusEmbedController
{
    public function index(): void
    {
        \DcsStats\Core\SupportBootstrap::load('language', 'siteFeatures');
        if (!Installation::isConfigured()) {
            Installation::redirectToInstaller();
        }

        $server = trim((string)($_GET['server'] ?? ''));
        $server = (string)preg_replace('/[\x00-\x1F\x7F]/u', '', $server);
        $server = substr($server, 0, 120);

        header_remove('X-Frame-Options');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; connect-src 'self' http://localhost:* https://localhost:*; img-src 'self' data: https:; frame-ancestors *; base-uri 'none'; form-action 'none'");
        View::render('Public/server_status_embed.php', ['serverFilter' => $server]);
    }
}
