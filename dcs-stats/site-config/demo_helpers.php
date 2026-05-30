<?php
/**
 * Demo mode helpers.
 * Demo mode is enabled by placing a hidden .demo file in the dashboard root.
 */

if (!function_exists('isDemoMode')) {
    function isDemoMode() {
        return getDemoConfigPath() !== '';
    }
}

if (!function_exists('getDemoConfigPath')) {
    function getDemoConfigPath() {
        $paths = [
            dirname(__DIR__) . '/.demo',
            dirname(__DIR__, 2) . '/.demo'
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return '';
    }
}

if (!function_exists('getDemoProtectedUsername')) {
    function getDemoProtectedUsername() {
        $path = getDemoConfigPath();
        if ($path === '' || !is_readable($path)) {
            return '';
        }

        $content = trim((string)@file_get_contents($path));
        if ($content === '') {
            return '';
        }

        $json = json_decode($content, true);
        if (is_array($json)) {
            return trim((string)($json['protected_user'] ?? $json['owner'] ?? $json['username'] ?? ''));
        }

        foreach (preg_split('/\R/', $content) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$key, $value] = array_map('trim', explode('=', $line, 2));
                if (in_array(strtolower($key), ['protected_user', 'owner', 'username'], true)) {
                    return $value;
                }
                continue;
            }

            return $line;
        }

        return '';
    }
}

if (!function_exists('isDemoOwner')) {
    function isDemoOwner($admin = null) {
        if (!$admin && function_exists('getCurrentAdmin')) {
            $admin = getCurrentAdmin();
        }

        $protectedUsername = getDemoProtectedUsername();
        return $protectedUsername !== ''
            && is_array($admin)
            && hash_equals($protectedUsername, (string)($admin['username'] ?? ''));
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
