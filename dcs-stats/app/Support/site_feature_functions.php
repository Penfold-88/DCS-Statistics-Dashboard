<?php

use DcsStats\Core\SiteFeatures;

function getSettingsPath() {
    return SiteFeatures::settingsPath();
}

function loadSiteFeatures() {
    return SiteFeatures::load();
}

function saveSiteFeatures($features) {
    return SiteFeatures::save(is_array($features) ? $features : []);
}

function isFeatureEnabled($feature) {
    return SiteFeatures::isEnabled((string)$feature);
}

function getFeatureValue($feature, $default = '') {
    return SiteFeatures::value((string)$feature, $default);
}

function serverCardFeatureKey($serverName) {
    return SiteFeatures::serverCardFeatureKey((string)$serverName);
}

function getFeatureGroups() {
    return SiteFeatures::groups();
}

function getFeatureDependencies() {
    return SiteFeatures::dependencies();
}
