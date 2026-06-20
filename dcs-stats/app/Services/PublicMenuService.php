<?php

namespace DcsStats\Services;

final class PublicMenuService
{
    private MenuConfigPathService $pathService;

    public function __construct(?MenuConfigPathService $pathService = null)
    {
        $this->pathService = $pathService ?? new MenuConfigPathService();
    }

    public function configPath(): string
    {
        return $this->pathService->configPath();
    }

    public function items(): array
    {
        return (new MenuManagerService($this->pathService))->items();
    }
}
