<?php

function sendAdminSecurityHeaders() {
    \DcsStats\Core\AdminEnvironment::sendSecurityHeaders();
}

sendAdminSecurityHeaders();

function isAdminRequestHttps() {
    return \DcsStats\Core\AdminEnvironment::requestIsHttps();
}

function prepareAdminSessionStorage() {
    \DcsStats\Core\AdminEnvironment::prepareSessionStorage();
}

\DcsStats\Core\AdminEnvironment::startSession();

function initializeAdminData() {
    \DcsStats\Core\AdminDataInitializer::initialize();
}

function adminDataNeedsInitialization() {
    return \DcsStats\Core\AdminDataInitializer::needsInitialization();
}

function getDataFilePath($type) {
    return \DcsStats\Core\AdminEnvironment::dataFilePath((string)$type);
}

function getAdminUsers() {
    return \DcsStats\Core\AdminUsers::all();
}

function saveAdminUsers($users) {
    return \DcsStats\Core\AdminUsers::save(is_array($users) ? $users : []);
}

function findAdminUser($username) {
    return \DcsStats\Core\AdminUsers::findByUsernameOrEmail((string)$username);
}

function updateAdminUser($userId, $updates) {
    return \DcsStats\Core\AdminUsers::update((int)$userId, is_array($updates) ? $updates : []);
}

function isUserLocked($user) {
    return \DcsStats\Core\AdminUsers::unlockIfExpired(is_array($user) ? $user : []);
}

function pruneAdminLogs($logs, $maxLogs = null) {
    return \DcsStats\Core\AdminAuditLog::prune($logs, $maxLogs === null ? null : (int)$maxLogs);
}

function logAdminActivity($action, $adminId = null, $targetType = null, $targetId = null, $details = null) {
    \DcsStats\Core\AdminAuditLog::write($action, $adminId, $targetType, $targetId, $details);
}

function attemptLogin($username, $password, $remember = false) {
    return \DcsStats\Core\AdminSessionAuth::attemptLogin($username, $password, (bool)$remember);
}

function isAdminLoggedIn() {
    return \DcsStats\Core\AdminSessionAuth::isLoggedIn();
}

function logout() {
    \DcsStats\Core\AdminSessionAuth::logout();
}

function hasPermission($permission) {
    return \DcsStats\Core\AdminSessionAuth::hasPermission($permission);
}

function adminPanelUrl($path = '') {
    return \DcsStats\Core\AdminSessionAuth::panelUrl((string)$path);
}

function requireAdmin() {
    \DcsStats\Core\AdminSessionAuth::requireLogin();
}

function requirePermission($permission) {
    \DcsStats\Core\AdminSessionAuth::requirePermission($permission);
}

function getCSRFToken() {
    return \DcsStats\Core\Csrf::token();
}

function verifyCSRFToken($token) {
    return \DcsStats\Core\Csrf::verify((string)$token);
}

function getRequestCSRFToken($jsonInput = null) {
    return \DcsStats\Core\Csrf::requestToken(is_array($jsonInput) ? $jsonInput : null);
}

function requireCSRFToken($jsonInput = null) {
    \DcsStats\Core\Csrf::requireValid(is_array($jsonInput) ? $jsonInput : null);
}

function csrfField() {
    return \DcsStats\Core\Csrf::field();
}

if (adminDataNeedsInitialization()) {
    initializeAdminData();
}
