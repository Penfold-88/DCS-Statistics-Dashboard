<?php
/**
 * Admin panel configuration compatibility include.
 *
 * V1.3 owns these settings in the application framework; this file remains so
 * existing admin and installer entry points can keep requiring site-config/config.php.
 */

if (!defined('ADMIN_PANEL')) {
    die('Direct access not permitted');
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

\DcsStats\Core\AdminConfig::load();
