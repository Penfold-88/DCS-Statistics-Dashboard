<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\ThemePageServiceFactory;

final class ThemesController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'chartTheme',
            'language',
        ]);

        $currentAdmin = $this->requirePermission('manage_themes');
        $this->render('themes.php', (new ThemePageServiceFactory())->create()->state($currentAdmin));
    }
}
