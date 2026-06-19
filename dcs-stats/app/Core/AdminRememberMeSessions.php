<?php

namespace DcsStats\Core;

final class AdminRememberMeSessions
{
    public static function createForUser(array $user): void
    {
        (new AdminRememberMeService())->createForUser($user);
    }

    public static function restoreFromCookie(?string $cookieValue): bool
    {
        return (new AdminRememberMeService())->restoreFromCookie($cookieValue);
    }

    public static function clearCookie(): void
    {
        (new AdminRememberMeService())->clearCookie();
    }

    public static function removeCookieSession(string $cookieValue): void
    {
        (new AdminRememberMeService())->removeCookieSession($cookieValue);
    }
}
