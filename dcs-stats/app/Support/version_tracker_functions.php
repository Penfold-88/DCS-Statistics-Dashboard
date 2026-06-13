<?php

function getCurrentVersionInfo() {
    return \DcsStats\Core\VersionTracker::currentInfo();
}

function getInstalledBuildLabel($versionInfo = null) {
    return \DcsStats\Core\VersionTracker::installedBuildLabel(is_array($versionInfo) ? $versionInfo : null);
}

function getGitHubBranchVersionInfo($repo, $branch, $timeoutSeconds = 5) {
    return \DcsStats\Core\VersionTracker::githubBranchVersionInfo($repo, $branch, $timeoutSeconds);
}

function updateVersionMetadata($version = null, $branch = null, $username = null, $commitSha = null, $commitDate = null) {
    return \DcsStats\Core\VersionTracker::updateMetadata($version, $branch, $username, $commitSha, $commitDate);
}

function initializeVersionTracking() {
    return \DcsStats\Core\VersionTracker::initialize();
}
