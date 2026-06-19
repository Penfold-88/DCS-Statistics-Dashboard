<?php

namespace DcsStats\Services\Api;

final class PublicApiRequestGuard
{
    public function prepareJson(): void
    {
        ini_set('display_errors', '0');
        error_reporting(0);
        header('X-Content-Type-Options: nosniff');
    }

    public function allow(int $limit, int $window): bool
    {
        \DcsStats\Core\SupportBootstrap::security();
        return \checkRateLimit($limit, $window);
    }

    public function input(): array
    {
        $input = json_decode((string)file_get_contents('php://input'), true);
        return is_array($input) ? $input : [];
    }
}
