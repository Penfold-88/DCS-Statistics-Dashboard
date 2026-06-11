<?php

require_once __DIR__ . '/app/bootstrap.php';

use DcsStats\Core\DevMode;

function isDevMode() {
    return DevMode::enabled();
}

function getDevModeIndicator() {
    return DevMode::indicatorHtml();
}
