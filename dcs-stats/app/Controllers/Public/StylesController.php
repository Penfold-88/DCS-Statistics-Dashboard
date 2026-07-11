<?php

namespace DcsStats\Controllers\Public;

use DcsStats\Services\StylesheetService;

final class StylesController
{
    public function show(): void
    {
        http_response_code(200);
        header('Content-Type: text/css; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');

        echo (new StylesheetService())->render();
    }
}
