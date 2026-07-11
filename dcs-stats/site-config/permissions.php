<?php

require_once dirname(__DIR__) . '/app/bootstrap.php';

(new \DcsStats\Controllers\Admin\PermissionsController())->show();
