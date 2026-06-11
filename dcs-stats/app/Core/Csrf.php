<?php

namespace DcsStats\Core;

final class Csrf
{
    public static function token(): string
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }

        return $_SESSION[CSRF_TOKEN_NAME];
    }

    public static function verify(string $token): bool
    {
        if ($token === '' || empty($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }

        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    public static function requestToken(?array $jsonInput = null): string
    {
        if (isset($_POST['csrf_token'])) {
            return (string)$_POST['csrf_token'];
        }

        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_ADMIN_CSRF_TOKEN'] ?? '';
        if ($headerToken !== '') {
            return (string)$headerToken;
        }

        if (is_array($jsonInput) && isset($jsonInput['csrf_token'])) {
            return (string)$jsonInput['csrf_token'];
        }

        return '';
    }

    public static function requireValid(?array $jsonInput = null): void
    {
        if (!self::verify(self::requestToken($jsonInput))) {
            http_response_code(403);
            die(ERROR_MESSAGES['csrf_invalid']);
        }
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
