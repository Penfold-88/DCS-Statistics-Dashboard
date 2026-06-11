<?php

require_once __DIR__ . '/app/bootstrap.php';

use DcsStats\Core\ChartTheme;

function getDefaultChartTheme() {
    return ChartTheme::defaults();
}

function getChartThemePath() {
    return ChartTheme::path();
}

function loadChartTheme() {
    return ChartTheme::load();
}

function saveChartTheme($theme) {
    return ChartTheme::save(is_array($theme) ? $theme : []);
}
