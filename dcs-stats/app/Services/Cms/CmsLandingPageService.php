<?php

namespace DcsStats\Services\Cms;

final class CmsLandingPageService
{
    private CmsPageStore $store;

    public function __construct(?CmsPageStore $store = null)
    {
        $this->store = $store ?? new CmsPageStore();
    }

    public function selectedPage(): ?array
    {
        if (!\isFeatureEnabled('cms_enabled')) {
            return null;
        }
        $id = (string)\getFeatureValue('cms_homepage_page_id', '');
        if (!preg_match('/^[a-f0-9]{16}$/', $id)) {
            return null;
        }
        return $this->resolve(true, $id);
    }

    public function resolve(bool $cmsEnabled, string $id): ?array
    {
        if (!$cmsEnabled || !preg_match('/^[a-f0-9]{16}$/', $id)) {
            return null;
        }
        $page = $this->store->find($id);
        return $page && !empty($page['published']) ? $page : null;
    }

    public function hasCmsLandingPage(): bool
    {
        return $this->selectedPage() !== null;
    }
}
