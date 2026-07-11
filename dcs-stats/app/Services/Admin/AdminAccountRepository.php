<?php

namespace DcsStats\Services\Admin;

final class AdminAccountRepository
{
    private \Closure $load;
    private \Closure $save;
    private \Closure $log;

    public function __construct(?callable $load = null, ?callable $save = null, ?callable $log = null)
    {
        $this->load = \Closure::fromCallable($load ?? static function (): array {
            return \getAdminUsers();
        });
        $this->save = \Closure::fromCallable($save ?? static function (array $users): bool {
            return (bool)\saveAdminUsers($users);
        });
        $this->log = \Closure::fromCallable($log ?? static function (
            string $action,
            ?int $adminId,
            string $targetType,
            $targetId,
            ?array $details = null
        ): void {
            \logAdminActivity($action, $adminId, $targetType, $targetId, $details);
        });
    }

    public function all(): array
    {
        return ($this->load)();
    }

    public function save(array $users): bool
    {
        return (bool)($this->save)($users);
    }

    public function log(string $action, int $adminId, $targetId, ?array $details = null): void
    {
        ($this->log)($action, $adminId, 'admin', $targetId, $details);
    }
}
