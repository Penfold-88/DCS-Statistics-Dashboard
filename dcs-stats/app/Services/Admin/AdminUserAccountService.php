<?php

namespace DcsStats\Services\Admin;

final class AdminUserAccountService
{
    private DemoAdminAccountPolicy $demoPolicy;
    private AdminAccountCreationService $creationService;
    private AdminAccountRemovalService $removalService;
    private AdminAccountStatusService $statusService;

    public function __construct(
        ?AdminAccountRepository $accounts = null,
        ?AdminAccountInputValidator $validator = null,
        ?AdminUserIdGenerator $idGenerator = null,
        ?DemoAdminAccountPolicy $demoPolicy = null,
        ?AdminAccountCreationService $creationService = null,
        ?AdminAccountRemovalService $removalService = null,
        ?AdminAccountStatusService $statusService = null
    ) {
        $accounts = $accounts ?? new AdminAccountRepository();
        $validator = $validator ?? new AdminAccountInputValidator();
        $idGenerator = $idGenerator ?? new AdminUserIdGenerator();
        $this->demoPolicy = $demoPolicy ?? new DemoAdminAccountPolicy();
        $this->creationService = $creationService ?? new AdminAccountCreationService($accounts, $validator, $idGenerator);
        $this->removalService = $removalService ?? new AdminAccountRemovalService($accounts, $this->demoPolicy);
        $this->statusService = $statusService ?? new AdminAccountStatusService($accounts, $validator, $this->demoPolicy);
    }

    public function isProtectedAccount(array $admin): bool
    {
        return $this->demoPolicy->isProtected($admin);
    }

    public function add(array $post, int $currentAdminId): array
    {
        return $this->creationService->add($post, $currentAdminId);
    }

    public function remove(int $adminId, int $currentAdminId): array
    {
        return $this->removalService->remove($adminId, $currentAdminId);
    }

    public function toggleActive(int $adminId, int $currentAdminId): array
    {
        return $this->statusService->toggleActive($adminId, $currentAdminId);
    }

    public function resetPassword(int $adminId, string $newPassword, int $currentAdminId): array
    {
        return $this->statusService->resetPassword($adminId, $newPassword, $currentAdminId);
    }
}
