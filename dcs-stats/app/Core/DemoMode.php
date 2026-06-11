<?php

namespace DcsStats\Core;

final class DemoMode
{
    public static function isEnabled(): bool
    {
        return self::configPath() !== '';
    }

    public static function configPath(): string
    {
        $paths = [
            DCS_ROOT_PATH . '/.demo',
            dirname(DCS_ROOT_PATH) . '/.demo',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return '';
    }

    public static function protectedUsername(): string
    {
        $path = self::configPath();
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

    public static function isOwner($admin = null): bool
    {
        if (!$admin && function_exists('getCurrentAdmin')) {
            $admin = \getCurrentAdmin();
        }

        $protectedUsername = self::protectedUsername();
        return $protectedUsername !== ''
            && is_array($admin)
            && hash_equals($protectedUsername, (string)($admin['username'] ?? ''));
    }

    public static function isRestricted($admin = null): bool
    {
        return self::isEnabled() && !self::isOwner($admin);
    }

    public static function restrictionMessage(): string
    {
        return 'Demo mode is enabled. This action is locked on the public demo.';
    }

    public static function writeLockMessage(): string
    {
        return 'Demo mode is enabled. The admin panel is read-only on the public demo.';
    }

    public static function maskValue($value): string
    {
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

    public static function blockWriteRequest($admin = null, bool $json = false): bool
    {
        if (!self::isRestricted($admin)) {
            return false;
        }

        http_response_code(403);
        if ($json) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => self::writeLockMessage()]);
        } else {
            echo self::writeLockMessage();
        }
        exit;
    }
}
