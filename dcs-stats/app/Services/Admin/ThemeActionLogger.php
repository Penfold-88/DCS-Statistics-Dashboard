<?php

namespace DcsStats\Services\Admin;

final class ThemeActionLogger
{
    public function log(string $action, string $message): void
    {
        if (function_exists('logActivity')) {
            \logActivity($action, $message);
        }
    }
}
