<?php
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
