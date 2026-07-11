<?php

define('DCS_SKIP_SESSION', true);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

(new \DcsStats\Controllers\Admin\Api\BackupsController())->create();

