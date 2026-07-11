<?php

namespace DcsStats\Services;

final class InstallCheckinStateStore
{
    public function path(): string
    {
        $dataDir = DCS_ROOT_PATH . '/site-config/data';
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0700, true);
        }

        return $dataDir . '/install_checkin_state.json';
    }

    public function load(): array
    {
        $statePath = $this->path();
        if (!file_exists($statePath)) {
            return [];
        }

        $state = json_decode((string)@file_get_contents($statePath), true);

        return is_array($state) ? $state : [];
    }

    public function save(array $state): void
    {
        @file_put_contents($this->path(), json_encode($state, JSON_PRETTY_PRINT));
    }
}
