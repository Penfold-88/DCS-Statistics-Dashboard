<?php

namespace DcsStats\Controllers\Public;

final class HealthController
{
    public function show(): void
    {
        http_response_code(200);
        header('Content-Type: text/plain; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');

        echo 'OK';
    }
}
