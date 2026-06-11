<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Core\AdminAuth;

final class LogoutController
{
    public function handle(): void
    {
        AdminAuth::logout();

        header('Location: ' . url('index.php'));
        exit;
    }
}

