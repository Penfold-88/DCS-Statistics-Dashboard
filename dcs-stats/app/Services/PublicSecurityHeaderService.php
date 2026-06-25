<?php

namespace DcsStats\Services;

final class PublicSecurityHeaderService
{
    public function send(): void
    {
        header('X-Content-Type-Options: nosniff');
        if (isset($_GET['preview']) && $_GET['preview'] === '1') {
            header('X-Frame-Options: SAMEORIGIN');
        } else {
            header('X-Frame-Options: DENY');
        }
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        $cspConnectSrc = "'self' http://localhost:* https://localhost:*";
        $frameAncestors = (isset($_GET['preview']) && $_GET['preview'] === '1') ? " frame-ancestors 'self';" : " frame-ancestors 'none';";
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src {$cspConnectSrc};" . $frameAncestors);
    }
}
