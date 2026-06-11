<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\ThemePageService;

final class ThemesController extends AdminPageController
{
    public function show(): void
    {
        $this->boot([
            'config_path.php',
            'chart_theme.php',
            'language.php',
        ]);

        $currentAdmin = $this->requirePermission('manage_themes');
        $this->render('themes.php', (new ThemePageService())->state($currentAdmin));
    }
}
