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
        ($this->writer)(
            ADMIN_COOKIE_NAME,
            $userId . ':' . $token,
            time() + ADMIN_COOKIE_LIFETIME,
            '/',
            '',
            ENFORCE_HTTPS,
            true
        );
    }

    public function clear(): void
    {
        ($this->writer)(ADMIN_COOKIE_NAME, '', time() - 3600, '/');
    }
}
