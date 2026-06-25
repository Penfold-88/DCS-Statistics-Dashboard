<?php

define('ADMIN_PANEL', true);
require_once dirname(__DIR__) . '/app/bootstrap.php';

(new \DcsStats\Controllers\Admin\CmsSettingsController())->show();
