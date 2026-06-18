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
        return (new DemoConfigReader())->configPath();
    }

    public static function protectedUsername(): string
    {
        return (new DemoConfigReader())->protectedUsername();
    }

    public static function isOwner($admin = null): bool
    {
        return (new DemoAccessPolicy())->isOwner($admin);
    }

    public static function isRestricted($admin = null): bool
    {
        return (new DemoAccessPolicy())->isRestricted($admin);
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
        return (new DemoAccessPolicy())->maskValue($value);
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
