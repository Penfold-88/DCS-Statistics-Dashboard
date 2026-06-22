<?php

namespace DcsStats\Controllers\Admin;

final class CmsEmbedsController extends AdminPageController
{
    public function show(): void
    {
        $this->boot(['language', 'siteFeatures']);
        $currentAdmin = $this->requirePermission('manage_pages');
        if (!\isFeatureEnabled('cms_enabled')) {
            header('Location: cms_settings.php');
            exit;
        }

        $adminBaseUrl = rtrim(\absoluteUrl(), '/');
        $siteBaseUrl = preg_replace('#/site-config$#', '', $adminBaseUrl);
        $embedUrl = rtrim((string)$siteBaseUrl, '/') . '/server_status_embed.php';
        $embedCode = '<iframe src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars(\dcs_t('widget.server_status.title'), ENT_QUOTES, 'UTF-8') . '" width="100%" height="360" loading="lazy" style="border:0" referrerpolicy="strict-origin-when-cross-origin"></iframe>';
        $this->render('cms_embeds.php', [
            'currentAdmin' => $currentAdmin,
            'pageTitle' => \dcs_t('admin.cms.embeds'),
            'embedUrl' => $embedUrl,
            'embedCode' => $embedCode,
        ]);
    }
}
