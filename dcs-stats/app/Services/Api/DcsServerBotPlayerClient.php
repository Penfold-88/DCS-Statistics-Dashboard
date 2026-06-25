<?php

namespace DcsStats\Services\Api;

final class DcsServerBotPlayerClient
{
    private \Closure $request;

    public function __construct(callable $request)
    {
        $this->request = \Closure::fromCallable($request);
    }

    public function getUser($nickname)
    {
        if (!$nickname) {
            return null;
        }

        $users = ($this->request)('POST', '/getuser', ['nick' => $nickname]);
        return !empty($users) && is_array($users) ? $users[0] : null;
    }

    public function getPlayerStats($nickname, $date = null)
    {
        if (!$nickname) {
            return null;
        }

        return ($this->request)('POST', '/stats', [
            'nick' => $nickname,
            'date' => $this->resolvePlayerDate($nickname, $date),
        ]);
    }

    public function getPlayerInfo($nickname, $date = null)
    {
        if (!$nickname) {
            return null;
        }

        $data = ['nick' => $nickname];
        if ($date) {
            $data['date'] = $date;
        }

        return ($this->request)('POST', '/player_info', $data);
    }

    public function getWeaponPk($nickname, $date = null)
    {
        if (!$nickname) {
            return null;
        }

        return ($this->request)('POST', '/weaponpk', [
            'nick' => $nickname,
            'date' => $this->resolvePlayerDate($nickname, $date),
        ]);
    }

    private function resolvePlayerDate($nickname, $date)
    {
        if ($date) {
            return $date;
        }

        try {
            $userData = ($this->request)('POST', '/getuser', ['nick' => $nickname]);
            if ($userData && is_array($userData) && isset($userData[0]['date'])) {
                return $userData[0]['date'];
            }

            throw new \Exception('Unable to determine user last seen date');
        } catch (\Exception $e) {
            throw new \Exception('Failed to get user data: ' . $e->getMessage());
        }
    }
}
