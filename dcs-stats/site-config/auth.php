<?php

define('ADMIN_PANEL', true);

if (!defined('DCS_SKIP_SESSION')) {
    define('DCS_SKIP_SESSION', true);
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

\DcsStats\Core\AdminBootstrap::auth();
