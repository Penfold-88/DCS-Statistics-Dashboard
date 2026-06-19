<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Core\AdminBootstrap;
use DcsStats\Core\SupportBootstrap;

abstract class AdminPageController
{
    protected function boot(array $supports = []): void
    {
        AdminBootstrap::panel();
        SupportBootstrap::load(...$supports);
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
