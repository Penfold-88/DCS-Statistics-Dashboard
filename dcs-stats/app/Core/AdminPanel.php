<?php

namespace DcsStats\Core;

use DcsStats\Services\Admin\AdminActivityService;
use DcsStats\Services\Admin\AdminPresentationService;
use DcsStats\Services\Admin\MaintenanceConfigStore;

final class AdminPanel
{
    public static function currentAdmin(): ?array
    {
        return (new AdminActivityService())->currentAdmin();
    }

    public static function formatDate($date, ?string $format = null): string
    {
        return (new AdminPresentationService())->formatDate($date, $format);
    }

    public static function normalizeLog($log): array
    {
        return (new AdminActivityService())->normalizeLog($log);
    }

    public static function logTimestamp(array $log): int
    {
        return (new AdminActivityService())->logTimestamp($log);
    }

    public static function logAction($action, array $details = []): void
    {
        (new AdminActivityService())->logAction($action, $details);
    }

    public static function recentActivity(int $limit = 10): array
    {
        return (new AdminActivityService())->recentActivity($limit);
    }

    public static function dashboardStats(): array
    {
        return (new AdminActivityService())->dashboardStats();
    }

    public static function roleBadge($role): string
    {
        return (new AdminPresentationService())->roleBadge($role);
    }

    public static function pagination($totalItems, $perPage, $currentPage, $baseUrl): string
    {
        return (new AdminPresentationService())->pagination($totalItems, $perPage, $currentPage, $baseUrl);
    }

    public static function loadMaintenanceConfig(): array
    {
        return (new MaintenanceConfigStore())->load();
    }

    public static function saveMaintenanceConfig($config): bool
    {
        return (new MaintenanceConfigStore())->save($config);
    }
}
