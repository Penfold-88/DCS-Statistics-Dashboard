<?php

namespace DcsStats\Core;

final class AdminRememberMeCookie
{
    private \Closure $writer;

    public function __construct(?callable $writer = null)
    {
        $this->writer = \Closure::fromCallable($writer ?? static function (...$arguments): bool {
            return setcookie(...$arguments);
        });
    }

    public function issue(int $userId, string $token): void
    {
        ($this->writer)(ADMIN_COOKIE_NAME, $userId . ':' . $token, [
            'expires' => time() + ADMIN_COOKIE_LIFETIME,
            'path' => '/',
            'secure' => ENFORCE_HTTPS || AdminEnvironment::requestIsHttps(),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    public function clear(): void
    {
        ($this->writer)(ADMIN_COOKIE_NAME, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => ENFORCE_HTTPS || AdminEnvironment::requestIsHttps(),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }
}
