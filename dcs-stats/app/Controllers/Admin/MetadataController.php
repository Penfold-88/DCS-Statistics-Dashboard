<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\MetadataPageService;

final class MetadataController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'language.php',
            'site_metadata.php',
        ]);

        $currentAdmin = $this->requirePermission('manage_features');
        $metadataState = (new MetadataPageService())->state($currentAdmin);

        $this->render('metadata.php', $metadataState);
    }
}
