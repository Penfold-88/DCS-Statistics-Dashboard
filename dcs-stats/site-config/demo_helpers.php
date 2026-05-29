<?php
/**
 * Demo mode helpers.
 * Demo mode is enabled by placing a hidden .demo file in the dashboard root.
 */

if (!function_exists('isDemoMode')) {
    function isDemoMode() {
        return file_exists(dirname(__DIR__) . '/.demo') || file_exists(dirname(__DIR__, 2) . '/.demo');
    }
}

if (!function_exists('isDemoOwner')) {
    function isDemoOwner($admin = null) {
        if (!$admin && function_exists('getCurrentAdmin')) {
            $admin = getCurrentAdmin();
        }

        return is_array($admin) && (($admin['username'] ?? '') === 'Penfold88');
    }
}

if (!function_exists('isDemoRestricted')) {
    function isDemoRestricted($admin = null) {
        return isDemoMode() && !isDemoOwner($admin);
    }
}

if (!function_exists('demoRestrictionMessage')) {
    function demoRestrictionMessage() {
        return 'Demo mode is enabled. This action is locked on the public demo.';
    }
}

if (!function_exists('maskDemoValue')) {
    function maskDemoValue($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        $port = '';
        if (preg_match('/:(\d+)$/', $value, $matches)) {
            $port = ':' . $matches[1];
        }

        return '••••••••' . $port;
    }
}

if (!function_exists('demoWriteLockMessage')) {
    function demoWriteLockMessage() {
        return 'Demo mode is enabled. The admin panel is read-only on the public demo.';
    }
}

if (!function_exists('blockDemoWriteRequest')) {
    function blockDemoWriteRequest($admin = null, $json = false) {
        if (!isDemoRestricted($admin)) {
            return false;
        }

        http_response_code(403);
        if ($json) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => demoWriteLockMessage()]);
        } else {
            echo demoWriteLockMessage();
        }
        exit;
    }
}
