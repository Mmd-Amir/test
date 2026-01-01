<?php
/**
 * REFACOR PANELS DEBUGGING
 */
$panelsDebugFile = dirname(__DIR__) . '/PANELS_DEBUG.log';
ini_set('log_errors', 1);
ini_set('error_log', $panelsDebugFile);
// file_put_contents($panelsDebugFile, "[" . date('Y-m-d H:i:s') . "] Panels loaded\n", FILE_APPEND);

// Refactorpanels entrypoint (replacement for legacy panels.php)

if (!function_exists('rf_log')) {
    require_once __DIR__ . '/helpers/logger.php';
}
require_once __DIR__ . '/helpers/error_handler.php';
require_once __DIR__ . '/helpers/flow.php';

if (function_exists('rf_set_module')) {
    rf_set_module('panels/panels.php');
}

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/ManagePanel.php';
