<?php

namespace DcsStats\Controllers\Admin;

final class LoginController
{
    public function show(): void
    {
        ob_start();

        require_once DCS_ROOT_PATH . '/site-config/auth.php';
        require_once DCS_ROOT_PATH . '/language.php';

        if (!file_exists(\getDataFilePath('users'))) {
            header('Location: install.php');
            exit;
        }

        $error = '';
        $success = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
                $error = ERROR_MESSAGES['csrf_invalid'];
            } else {
                $result = \attemptLogin($_POST['username'] ?? '', $_POST['password'] ?? '', isset($_POST['remember']));

                if ($result['success']) {
                    ob_end_clean();
                    header('Location: ' . \adminPanelUrl('index.php'));
                    exit;
                }

                $error = $result['error'];
            }
        }

        if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
            $success = SUCCESS_MESSAGES['logout_success'];
        }

        require DCS_ROOT_PATH . '/app/Views/Admin/login.php';
    }
}
