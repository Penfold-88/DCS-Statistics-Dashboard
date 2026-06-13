<?php

use DcsStats\Core\DemoMode;

if (!function_exists('isDemoMode')) {
    function isDemoMode() {
        return DemoMode::isEnabled();
    }
}

if (!function_exists('getDemoConfigPath')) {
    function getDemoConfigPath() {
        return DemoMode::configPath();
    }
}

if (!function_exists('getDemoProtectedUsername')) {
    function getDemoProtectedUsername() {
        return DemoMode::protectedUsername();
    }
}

if (!function_exists('isDemoOwner')) {
    function isDemoOwner($admin = null) {
        return DemoMode::isOwner($admin);
    }
}

if (!function_exists('isDemoRestricted')) {
    function isDemoRestricted($admin = null) {
        return DemoMode::isRestricted($admin);
    }
}

if (!function_exists('demoRestrictionMessage')) {
    function demoRestrictionMessage() {
        return DemoMode::restrictionMessage();
    }
}

if (!function_exists('maskDemoValue')) {
    function maskDemoValue($value) {
        return DemoMode::maskValue($value);
    }
}

if (!function_exists('demoWriteLockMessage')) {
    function demoWriteLockMessage() {
        return DemoMode::writeLockMessage();
    }
}

if (!function_exists('blockDemoWriteRequest')) {
    function blockDemoWriteRequest($admin = null, $json = false) {
        return DemoMode::blockWriteRequest($admin, (bool)$json);
    }
}
