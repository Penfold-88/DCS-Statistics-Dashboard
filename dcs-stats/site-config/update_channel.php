<?php

require_once dirname(__DIR__) . '/app/bootstrap.php';

function getUpdateChannelDevFilePath() {
    return \DcsStats\Core\UpdateChannel::devFilePath();
}

function getUpdateChannelConfig() {
    return \DcsStats\Core\UpdateChannel::config();
}
