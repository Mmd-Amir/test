<?php
// Refactored keyboard.php (split into readable modules under Refactorkeyboard/)

if (!defined('RF_APP_ROOT')) {
    define('RF_APP_ROOT', dirname(__DIR__)); // project root
}
@chdir(RF_APP_ROOT);

// Ensure logs folder exists (if this package is the first to define RF_LOG_DIR)
if (!defined('RF_LOG_DIR')) {
    define('RF_LOG_DIR', __DIR__ . '/logs');
    if (!is_dir(RF_LOG_DIR)) {
        @mkdir(RF_LOG_DIR, 0775, true);
    }
    @ini_set('error_log', RF_LOG_DIR . '/php_error.log');
}

require_once __DIR__ . '/helpers/logger.php';
require_once __DIR__ . '/helpers/error_handler.php';
require_once __DIR__ . '/helpers/flow.php';
require_once __DIR__ . '/helpers/compat_functions.php';

if (function_exists('rf_install_error_handlers')) {
    rf_install_error_handlers();
}
if (function_exists('rf_set_module')) {
    rf_set_module('Refactorkeyboard/keyboard.php');
}

require_once __DIR__ . '/modules/00_init_settings_texts.php';
require_once __DIR__ . '/modules/01_admin_menus_and_gateways.php';
require_once __DIR__ . '/modules/02_shop_and_navigation_keyboards.php';
require_once __DIR__ . '/modules/03_lists_panels_channels_help.php';
require_once __DIR__ . '/modules/04_textbot_products_payments_lists.php';
require_once __DIR__ . '/modules/05_panel_options_support_affiliates_payment_settings.php';
require_once __DIR__ . '/modules/06_departments_lottery_wheel_links.php';
require_once __DIR__ . '/modules/07_dynamic_keyboard_builders_products_categories.php';
require_once __DIR__ . '/modules/08_stars_limits_nowpayments_stats.php';

// End.
