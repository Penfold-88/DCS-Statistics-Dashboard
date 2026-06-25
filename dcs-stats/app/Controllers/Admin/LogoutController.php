<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Core\AdminAuth;
use DcsStats\Core\Csrf;

final class LogoutController
{
    public function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            die('Method not allowed');
        }

        AdminAuth::requireLogin();
        Csrf::requireValid();
        AdminAuth::logout();

        header('Location: ' . url('index.php'));
        exit;
    }
}
