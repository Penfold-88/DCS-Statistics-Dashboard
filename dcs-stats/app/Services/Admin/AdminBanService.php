<?php

namespace DcsStats\Services\Admin;

final class AdminBanService
{
    public function playerBans(bool $activeOnly = true): array
    {
        $bans = json_decode((string)file_get_contents(ADMIN_BANS_FILE), true) ?: [];

        if ($activeOnly) {
            $bans = array_filter($bans, function ($ban) {
                if (!$ban['is_active']) {
                    return false;
                }

                if (!$ban['expires_at']) {
                    return true;
                }

                return strtotime($ban['expires_at']) > time();
            });
        }

        return array_values($bans);
    }

    public function playerIsBanned($ucid): bool
    {
        foreach ($this->playerBans(true) as $ban) {
            if (($ban['player_ucid'] ?? '') === $ucid) {
                return true;
            }
        }

        return false;
    }
}
