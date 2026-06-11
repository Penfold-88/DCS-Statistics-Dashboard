<?php

namespace DcsStats\Core;

final class DevMode
{
    private static ?bool $enabled = null;

    public static function enabled(): bool
    {
        if (self::$enabled !== null) {
            return self::$enabled;
        }

        self::$enabled = false;
        $rootPath = DCS_ROOT_PATH;

        if (file_exists($rootPath . '/.mock-api')) {
            self::$enabled = true;
            return self::$enabled;
        }

        $parentPath = dirname($rootPath);
        if (basename($parentPath) !== 'dcs-stats' && file_exists($parentPath . '/.mock-api')) {
            self::$enabled = true;
            return self::$enabled;
        }

        $checkPath = $rootPath;
        for ($i = 0; $i < 5; $i++) {
            if (basename($checkPath) === 'dcs-stats' && file_exists($checkPath . '/.mock-api')) {
                self::$enabled = true;
                return self::$enabled;
            }

            $checkPath = dirname($checkPath);
            if ($checkPath === '/' || $checkPath === '.') {
                break;
            }
        }

        if (getenv('DCS_STATS_MOCK_API') === 'true' || getenv('MOCK_API') === 'true') {
            self::$enabled = true;
        }

        return self::$enabled;
    }

    public static function indicatorHtml(): string
    {
        if (!self::enabled()) {
            return '';
        }

        return '<div style="background: #ff9800; color: #000; padding: 5px 10px; text-align: center; font-size: 12px; position: fixed; bottom: 0; left: 0; right: 0; z-index: 9999;">
        MOCK API MODE - live API calls disabled
    </div>';
    }
}
