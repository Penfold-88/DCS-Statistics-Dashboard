<?php

namespace DcsStats\Services\Admin;

final class MaintenanceConfigStore
{
    public function load(): array
    {
        $defaults = ['enabled' => false, 'ip_whitelist' => []];
        $file = $this->path();

        if (file_exists($file)) {
            $data = json_decode((string)file_get_contents($file), true);
            if (is_array($data)) {
                return array_merge($defaults, $data);
            }
        }

        return $defaults;
    }

    public function save($config): bool
    {
        return file_put_contents($this->path(), json_encode($config, JSON_PRETTY_PRINT)) !== false;
    }

    private function path(): string
    {
        return DCS_ROOT_PATH . '/site-config/data/maintenance.json';
    }
}
