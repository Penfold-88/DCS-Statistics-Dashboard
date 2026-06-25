<?php

namespace DcsStats\Services\Admin;

final class ThemeMenuConfigService
{
    public function configPath(): string
    {
        return (new \DcsStats\Services\MenuConfigPathService())->configPath();
    }
}
