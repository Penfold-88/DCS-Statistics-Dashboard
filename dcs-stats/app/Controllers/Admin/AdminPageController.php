<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Core\AdminBootstrap;

abstract class AdminPageController
{
    protected function boot(array $extraIncludes = []): void
    {
        AdminBootstrap::panel();

        foreach ($extraIncludes as $include) {
            AdminBootstrap::includeCompat($include);
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
        require DCS_APP_PATH . '/Views/Admin/' . ltrim($view, '/');
    }
}
