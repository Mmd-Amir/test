<?php
// -------------------------------------------------------------
// Refactored entry bootstrap for `index.php`
// - keeps behavior as close as possible to the original
// - sets up logging + error handling
// - loads project dependencies (config/botapi/functions/...)
// -------------------------------------------------------------

require_once __DIR__ . '/helpers/logger.php';
require_once __DIR__ . '/helpers/error_handler.php';
require_once __DIR__ . '/helpers/flow.php';

rf_set_module('bootstrap.php');

// Make relative paths behave like the original root index.php
if (!defined('RF_APP_ROOT')) {
    define('RF_APP_ROOT', dirname(__DIR__));
}
@chdir(RF_APP_ROOT);

// Logs
if (!defined('RF_LOG_DIR')) {
    define('RF_LOG_DIR', __DIR__ . '/logs');
}
if (!is_dir(RF_LOG_DIR)) {
    @mkdir(RF_LOG_DIR, 0775, true);
}
@ini_set('error_log', RF_LOG_DIR . '/php_error.log');

$version = @file_get_contents('version');
if ($version === false) {
    $version = 'unknown';
}

date_default_timezone_set('Asia/Tehran');
$new_marzban = isset($new_marzban) ? $new_marzban : false;

ini_set('default_charset', 'UTF-8');
ini_set('memory_limit', '-1');

// Project dependencies (same as original index.php)
require_once 'config.php';
require_once 'botapi.php';
require_once 'jdf.php';
require_once 'function.php';
require_once 'keyboard.php';
require_once 'vendor/autoload.php';
require_once 'panels.php';

// Legacy helpers extracted from the original index.php
require_once __DIR__ . '/helpers/compat_functions.php';

$textbotlang = languagechange('text.json');

if ($is_bot) {
    rf_stop();
}
