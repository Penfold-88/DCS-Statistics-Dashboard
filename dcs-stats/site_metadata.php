<?php

require_once __DIR__ . '/app/bootstrap.php';

use DcsStats\Core\SiteMetadata;

function getSiteMetadataPath() {
    return SiteMetadata::path();
}

function getDefaultSiteMetadata() {
    return SiteMetadata::defaults();
}

function loadSiteMetadata() {
    return SiteMetadata::load();
}

function saveSiteMetadata($metadata) {
    return SiteMetadata::save(is_array($metadata) ? $metadata : []);
}
