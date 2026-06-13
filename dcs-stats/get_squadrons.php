<?php

define('DCS_SKIP_SESSION', true);

require_once __DIR__ . '/app/bootstrap.php';

(new \DcsStats\Controllers\Api\PublicStatsController())->squadrons();
