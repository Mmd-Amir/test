<?php
// Refactored version of the original `table.php` (DB tables/migrations)

require_once __DIR__ . '/bootstrap.php';

rf_set_module('table.php');

// Sequential migrations (kept in original order)
require_once __DIR__ . '/migrations/01_user_table.php';
require_once __DIR__ . '/migrations/02_help_table.php';
require_once __DIR__ . '/migrations/03_cron_runtime_state_table.php';
require_once __DIR__ . '/migrations/04_setting_table_and_defaults.php';
require_once __DIR__ . '/migrations/05_admin_table.php';
require_once __DIR__ . '/migrations/06_channels_and_marzban_panel_tables.php';
require_once __DIR__ . '/migrations/07_product_table.php';
require_once __DIR__ . '/migrations/08_invoice_table.php';
require_once __DIR__ . '/migrations/09_payment_report_table.php';
require_once __DIR__ . '/migrations/10_discount_table_and_columns.php';
require_once __DIR__ . '/migrations/11_giftcodeconsumed_table.php';
require_once __DIR__ . '/migrations/12_textbot_paysettings_discountsell.php';
require_once __DIR__ . '/migrations/13_affiliates_and_shop_tables.php';
require_once __DIR__ . '/migrations/14_departman_support_and_misc_tables.php';
