<?php

namespace DcsStats\Services\Admin;

final class InstallerSupportService
{
    private InstallerInputValidator $inputValidator;
    private InstallerPermissionService $permissionService;
    private InstallerApiConnectionService $apiConnectionService;

    public function __construct(
        ?InstallerInputValidator $inputValidator = null,
        ?InstallerPermissionService $permissionService = null,
        ?InstallerApiConnectionService $apiConnectionService = null
    ) {
        $this->inputValidator = $inputValidator ?? new InstallerInputValidator();
        $this->permissionService = $permissionService ?? new InstallerPermissionService();
        $this->apiConnectionService = $apiConnectionService ?? new InstallerApiConnectionService();
    }

    public function validateInputs(array $input): array
    {
        return $this->inputValidator->validate($input);
    }

    public function isValidApiKey($apiKey): bool
    {
        return $this->inputValidator->isValidApiKey($apiKey);
    }

    public function canCreateInPath(string $path): bool
    {
        return $this->permissionService->canCreateInPath($path);
    }

    public function pathIsWritable(string $path, string $type): bool
    {
        return $this->permissionService->pathIsWritable($path, $type);
    }

    public function permissionStatuses(array $paths, string $type): array
    {
        return $this->permissionService->statuses($paths, $type);
    }

    public function resolveApiUrl(string $apiUrl, string $apiKey, bool $isDev): array
    {
        return $this->apiConnectionService->resolve($apiUrl, $apiKey, $isDev);
    }
}
