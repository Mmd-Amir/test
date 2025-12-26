<?php
/**
 * Refactored version of /function.php
 * Folder: Refactorfunction/
 *
 * Usage:
 *   require_once __DIR__ . '/Refactorfunction/function.php';
 *
 * Notes:
 * - All functions are wrapped with function_exists to avoid redeclare fatals.
 * - Error logs are written to Refactorfunction/logs/*.log (or to the first RF_LOG_DIR already defined).
 */

if (!defined('APP_ROOT_PATH')) {
    // Project root (one level up from Refactorfunction/)
    define('APP_ROOT_PATH', dirname(__DIR__));
}

// Composer autoload (optional)
$composerAutoload = APP_ROOT_PATH . '/vendor/autoload.php';
if (is_readable($composerAutoload)) {
    require_once $composerAutoload;
} else {
    error_log('Composer autoloader not found. Optional dependencies may be unavailable.');
}
unset($composerAutoload);

// Original config include
require_once APP_ROOT_PATH . '/config.php';

// Make sure PHP writes errors to the same place as before
ini_set('error_log', APP_ROOT_PATH . '/error_log');

// Refactor helpers (logger + error handler)
require_once __DIR__ . '/helpers/logger.php';
require_once __DIR__ . '/helpers/error_handler.php';
require_once __DIR__ . '/helpers/flow.php';

// Bootstrap constants used by payment helpers
require_once __DIR__ . '/bootstrap/tronado_api.php';

// DB
require_once __DIR__ . '/modules/db/connection.php';
require_once __DIR__ . '/modules/db/schema.php';
require_once __DIR__ . '/modules/db/crud.php';

// Utilities
require_once __DIR__ . '/modules/util/math.php';
require_once __DIR__ . '/modules/util/formatting.php';
require_once __DIR__ . '/modules/util/dates.php';
require_once __DIR__ . '/modules/util/ids.php';
require_once __DIR__ . '/modules/util/strings.php';
require_once __DIR__ . '/modules/util/referral.php';
require_once __DIR__ . '/modules/util/placeholders.php';

// Filesystem helpers
require_once __DIR__ . '/modules/fs/directories.php';

// Panel/output helpers
require_once __DIR__ . '/modules/panel/output.php';

// Telegram helpers
require_once __DIR__ . '/modules/telegram/ip_security.php';
require_once __DIR__ . '/modules/telegram/messaging.php';

// Cron helpers
require_once __DIR__ . '/modules/cron/shell.php';
require_once __DIR__ . '/modules/cron/http_urls.php';
require_once __DIR__ . '/modules/cron/definitions.php';
require_once __DIR__ . '/modules/cron/runtime.php';

// Media helpers
require_once __DIR__ . '/modules/media/images_qrcode.php';

// Language helpers
require_once __DIR__ . '/modules/i18n/language.php';

// Payment helpers
require_once __DIR__ . '/modules/payment/settings.php';
require_once __DIR__ . '/modules/payment/tronado.php';
require_once __DIR__ . '/modules/payment/nowpayments.php';
require_once __DIR__ . '/modules/payment/crypto.php';
require_once __DIR__ . '/modules/payment/gateways.php';
require_once __DIR__ . '/modules/payment/invoice.php';
require_once __DIR__ . '/modules/payment/direct_payment.php';
