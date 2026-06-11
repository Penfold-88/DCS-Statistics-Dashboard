<?php

require_once __DIR__ . '/app/bootstrap.php';

(new \DcsStats\Controllers\Public\ServersController())->index();
