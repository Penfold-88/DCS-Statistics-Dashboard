<?php

namespace DcsStats\Controllers\Admin;

use DcsStats\Services\Admin\LanguageSettingsPageService;

final class LanguageSettingsController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['language.php']);

        $currentAdmin = $this->requirePermission('manage_features');
        $languageState = (new LanguageSettingsPageService())->state($currentAdmin);

        $this->render('language_settings.php', $languageState);
    }
}
