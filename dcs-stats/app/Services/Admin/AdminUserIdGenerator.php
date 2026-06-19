<?php

namespace DcsStats\Services\Admin;

final class AdminUserIdGenerator
{
    public function generate(array $users): int
    {
        $existingIds = [];
        foreach ($users as $user) {
            $existingIds[(int)($user['id'] ?? 0)] = true;
        }

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $id = random_int(100000, 2147483647);
            if (!isset($existingIds[$id])) {
                return $id;
            }
        }

        $maxId = empty($existingIds) ? 0 : max(array_keys($existingIds));
        return $maxId + random_int(1, 1000);
    }
}
