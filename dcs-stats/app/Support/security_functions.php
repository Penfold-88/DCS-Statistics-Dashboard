<?php

use DcsStats\Core\Security;

function checkRateLimit($limit = 60, $window = 60) {
    return Security::checkRateLimit((int)$limit, (int)$window);
}

function validateJsonLine($line, $required_fields = []) {
    return Security::validateJsonLine((string)$line, is_array($required_fields) ? $required_fields : []);
}

function validatePath($path, $base_dir) {
    return Security::validatePath((string)$path, (string)$base_dir);
}

function validateInput($input, $rules = []) {
    return Security::validateInput($input, is_array($rules) ? $rules : []);
}

function logSecurityEvent($event, $details, $ip = null) {
    Security::logEvent((string)$event, (string)$details, $ip === null ? null : (string)$ip);
}

function getSecurityLogPath() {
    return Security::logPath();
}

function rotateSecurityLog($logPath) {
    Security::rotateLog((string)$logPath);
}
