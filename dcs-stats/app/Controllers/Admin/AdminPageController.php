<?php

namespace DcsStats\Controllers\Admin;

abstract class AdminPageController
{
    protected function boot(array $extraIncludes = []): void
    {
        require_once DCS_ROOT_PATH . '/site-config/auth.php';
        require_once DCS_ROOT_PATH . '/site-config/admin_functions.php';

        foreach ($extraIncludes as $include) {
            require_once DCS_ROOT_PATH . '/' . ltrim($include, '/');
        }
    }

    protected function requirePermission(string $permission): array
    {
        \requireAdmin();
        \requirePermission($permission);

        return \getCurrentAdmin();
    }

    protected function render(string $view, array $state = []): void
    {
        extract($state, EXTR_SKIP);
        require DCS_ROOT_PATH . '/app/Views/Admin/' . ltrim($view, '/');
    }
}
