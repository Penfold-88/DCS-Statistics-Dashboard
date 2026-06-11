<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\CustomLinksPageService;

final class CustomLinksController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'language.php',
            'site_features.php',
        ]);

        $currentAdmin = $this->requirePermission('manage_features');
        $customLinksState = (new CustomLinksPageService())->state($currentAdmin);

        $this->render('custom_links.php', $customLinksState);
    }
}
