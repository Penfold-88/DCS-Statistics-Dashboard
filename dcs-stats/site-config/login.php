<?php

define('DCS_SKIP_SESSION', true);

require_once dirname(__DIR__) . '/app/bootstrap.php';

(new \DcsStats\Controllers\Admin\LoginController())->show();
