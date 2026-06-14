<?php

namespace DcsStats\Core;

final class AdminRememberMeSessions
{
    public static function createForUser(array $user): void
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $sessionsFile = AdminEnvironment::dataFilePath('sessions');
        $sessions = self::readSessions($sessionsFile);
        $sessions[] = [
            'id' => count($sessions) + 1,
            'admin_id' => $user['id'],
            'token_hash' => $tokenHash,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'expires_at' => date(DATE_FORMAT, time() + ADMIN_COOKIE_LIFETIME),
            'created_at' => date(DATE_FORMAT),
        ];

        self::writeSessions($sessionsFile, $sessions);

        setcookie(
            ADMIN_COOKIE_NAME,
            $user['id'] . ':' . $token,
            time() + ADMIN_COOKIE_LIFETIME,
            '/',
            '',
            ENFORCE_HTTPS,
            true
        );
    }

    public static function restoreFromCookie(?string $cookieValue): bool
    {
        if ($cookieValue === null) {
            return false;
        }

        $parts = explode(':', $cookieValue, 2);
        if (count($parts) !== 2) {
            self::clearCookie();
            return false;
        }

        [$userId, $token] = $parts;
        $userId = (int)$userId;
        $tokenHash = hash('sha256', $token);

        foreach (self::readSessions(AdminEnvironment::dataFilePath('sessions')) as $session) {
            if (
                (int)($session['admin_id'] ?? 0) === $userId
                && ($session['token_hash'] ?? '') === $tokenHash
                && strtotime((string)($session['expires_at'] ?? '')) > time()
            ) {
                $user = self::findUserById($userId);

                if ($user && !empty($user['is_active'])) {
                    AdminSessionState::applyUser($user);
                    return true;
                }
            }
        }

        self::clearCookie();
        return false;
    }

    public static function clearCookie(): void
    {
        setcookie(ADMIN_COOKIE_NAME, '', time() - 3600, '/');
    }

    public static function removeCookieSession(string $cookieValue): void
    {
        $parts = explode(':', $cookieValue, 2);
        if (count($parts) !== 2) {
            return;
        }

        [$userId, $token] = $parts;
        $tokenHash = hash('sha256', $token);

        $sessionsFile = AdminEnvironment::dataFilePath('sessions');
        $sessions = array_filter(self::readSessions($sessionsFile), function ($session) use ($userId, $tokenHash) {
            return !((int)($session['admin_id'] ?? 0) === (int)$userId && ($session['token_hash'] ?? '') === $tokenHash);
        });

        self::writeSessions($sessionsFile, array_values($sessions));
    }

    private static function readSessions(string $sessionsFile): array
    {
        return json_decode((string)@file_get_contents($sessionsFile), true) ?: [];
    }

    private static function writeSessions(string $sessionsFile, array $sessions): void
    {
        @file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod($sessionsFile, 0600);
    }

    private static function findUserById(int $userId): ?array
    {
        foreach (AdminUsers::all() as $user) {
            if ((int)($user['id'] ?? 0) === $userId) {
                return $user;
            }
        }

        return null;
    }
}
