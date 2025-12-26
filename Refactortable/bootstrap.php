<?php
require_once __DIR__ . '/helpers/logger.php';
require_once __DIR__ . '/helpers/error_handler.php';
require_once __DIR__ . '/helpers/flow.php';

rf_set_module('bootstrap.php');
rf_install_error_handlers();

if (!defined('RF_APP_ROOT')) {
    // Refactortable lives in project root: /path/to/project/Refactortable
    define('RF_APP_ROOT', dirname(__DIR__));
}

@chdir(RF_APP_ROOT);

// Prefer package-local logs only if not already defined by another package.
if (!defined('RF_LOG_DIR')) {
    define('RF_LOG_DIR', __DIR__ . '/logs');
}
if (!is_dir(RF_LOG_DIR)) {
    @mkdir(RF_LOG_DIR, 0775, true);
}

// Route PHP's internal error_log into our log dir too.
@ini_set('error_log', RF_LOG_DIR . '/php_error.log');

// Original dependencies
require_once 'function.php';
require_once 'config.php';
require_once 'botapi.php';

// Keep original global reference (for compatibility with code style)
global $connect;
