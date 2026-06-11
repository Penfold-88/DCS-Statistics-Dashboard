<?php

namespace DcsStats\Core;

final class AdminEnvironment
{
    public static function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
    }

    public static function requestIsHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        $forwardedProto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        if ($forwardedProto === 'https') {
            return true;
        }

        $forwardedSsl = strtolower((string)($_SERVER['HTTP_X_FORWARDED_SSL'] ?? ''));
        return $forwardedSsl === 'on' || $forwardedSsl === '1';
    }

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Strict');

        if (ENFORCE_HTTPS || self::requestIsHttps()) {
            ini_set('session.cookie_secure', '1');
        }

        self::prepareSessionStorage();
        session_name(ADMIN_SESSION_NAME);
        session_start();
    }

    public static function prepareSessionStorage(): void
    {
        $sessionDir = rtrim(ADMIN_DATA_DIR, '/\\') . DIRECTORY_SEPARATOR . 'php-sessions';

        if (!is_dir(ADMIN_DATA_DIR)) {
            @mkdir(ADMIN_DATA_DIR, 0700, true);
        }

        if (is_dir(ADMIN_DATA_DIR) && is_writable(ADMIN_DATA_DIR) && !is_dir($sessionDir)) {
            @mkdir($sessionDir, 0700, true);
        }

        if (is_dir($sessionDir) && is_writable($sessionDir)) {
            @chmod($sessionDir, 0700);
            session_save_path($sessionDir);
        }
    }

    public static function dataFilePath(string $type): string
    {
        $overrideConstant = strtoupper('ADMIN_' . $type . '_FILE_OVERRIDE');
        if (defined($overrideConstant)) {
            return constant($overrideConstant);
        }

        return constant('ADMIN_' . strtoupper($type) . '_FILE');
    }
}
