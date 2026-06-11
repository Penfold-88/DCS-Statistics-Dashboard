<?php

namespace DcsStats\Core;

final class AdminUsers
{
    public static function all(): array
    {
        if (array_key_exists('dcs_admin_users_cache', $GLOBALS)) {
            return $GLOBALS['dcs_admin_users_cache'];
        }

        $usersFile = AdminEnvironment::dataFilePath('users');
        if (!file_exists($usersFile)) {
            $GLOBALS['dcs_admin_users_cache'] = [];
            return [];
        }

        $users = json_decode((string)file_get_contents($usersFile), true);
        $GLOBALS['dcs_admin_users_cache'] = is_array($users) ? $users : [];
        return $GLOBALS['dcs_admin_users_cache'];
    }

    public static function save(array $users): bool
    {
        $usersFile = AdminEnvironment::dataFilePath('users');
        $result = @file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        @chmod($usersFile, 0600);

        if ($result !== false) {
            $GLOBALS['dcs_admin_users_cache'] = $users;
        }

        return $result !== false;
    }

    public static function findByUsernameOrEmail(string $username): ?array
    {
        foreach (self::all() as $user) {
            if (($user['username'] ?? '') === $username || ($user['email'] ?? '') === $username) {
                return $user;
            }
        }

        return null;
    }

    public static function update(int $userId, array $updates): bool
    {
        $users = self::all();

        foreach ($users as &$user) {
            if ((int)($user['id'] ?? 0) === $userId) {
                $user = array_merge($user, $updates);
                return self::save($users);
            }
        }

        return false;
    }

    public static function unlockIfExpired(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }

        $lockedUntil = strtotime((string)$user['locked_until']);
        if ($lockedUntil > time()) {
            return true;
        }

        self::update((int)($user['id'] ?? 0), [
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);

        return false;
    }
}
