<?php

use DcsStats\Core\DevMode;

function isDevMode() {
    return DevMode::enabled();
}

function getDevModeIndicator() {
    return DevMode::indicatorHtml();
}
