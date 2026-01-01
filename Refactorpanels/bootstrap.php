<?php
// Refactorpanels bootstrap: load project dependencies exactly like the legacy panels.php

if (function_exists('rf_set_module')) {
    rf_set_module('panels/bootstrap.php');
}

if (!defined('RF_APP_ROOT')) {
    // Refactorpanels folder is assumed to live in project root.
    define('RF_APP_ROOT', dirname(__DIR__));
}

@chdir(RF_APP_ROOT);

// Send PHP internal errors to a file under RF_LOG_DIR (if present)
// Or use the hard-coded PANELS_DEBUG.log if we want to be sure.
$panelsDebugFile = RF_APP_ROOT . '/PANELS_DEBUG.log';
ini_set('log_errors', 1);
ini_set('error_log', $panelsDebugFile);

require_once RF_APP_ROOT . '/config.php';
require_once RF_APP_ROOT . '/Marzban.php';
require_once RF_APP_ROOT . '/function.php';

require_once RF_APP_ROOT . '/x-ui_single.php';
require_once RF_APP_ROOT . '/hiddify.php';
require_once RF_APP_ROOT . '/alireza.php';
require_once RF_APP_ROOT . '/marzneshin.php';
require_once RF_APP_ROOT . '/alireza_single.php';
require_once RF_APP_ROOT . '/WGDashboard.php';
require_once RF_APP_ROOT . '/s_ui.php';
require_once RF_APP_ROOT . '/ibsng.php';
require_once RF_APP_ROOT . '/mikrotik.php';
