<?php

namespace DcsStats\Services\Admin;

final class MetadataPageService
{
    private MetadataActionService $actions;

    public function __construct(?MetadataActionService $actions = null)
    {
        $this->actions = $actions ?? new MetadataActionService();
    }

    public function state(array $currentAdmin): array
    {
        \DcsStats\Core\SupportBootstrap::language();
        \DcsStats\Core\SupportBootstrap::siteMetadata();

        $demoRestricted = \isDemoRestricted($currentAdmin);
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$message, $messageType] = $this->actions->handle($demoRestricted);
        }

        return [
            'demoRestricted' => $demoRestricted,
            'message' => $message,
            'messageType' => $messageType,
            'metadata' => \loadSiteMetadata(),
            'pageTitle' => \dcs_t('admin.metadata.title'),
        ];
    }
}
