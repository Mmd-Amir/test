<?php
// -------------------------------------------------------------
// Refactored version of the original root `index.php`
// Generated modules are under `Refactorindex/`
// -------------------------------------------------------------

require_once __DIR__ . '/bootstrap.php';

// === Initialization & guards ===
require_once __DIR__ . '/parts/init.php';
require_once __DIR__ . '/parts/precheck.php';

// === Main router chains (extracted from huge elseif chains) ===

// Chain 1: User core routes (start/back, services list, product info, extend, transfer, ...)
$rf_chain1_handled = false;
require_once __DIR__ . '/routes/user/01_start.php';
require_once __DIR__ . '/routes/user/02_details.php';
require_once __DIR__ . '/routes/user/03_qrcode.php';
require_once __DIR__ . '/routes/user/04_extend.php';
require_once __DIR__ . '/routes/user/05_extend_conf.php';
require_once __DIR__ . '/routes/user/06_volume.php';
require_once __DIR__ . '/routes/user/07_report.php';
require_once __DIR__ . '/routes/user/08_transfer.php';
unset($rf_chain1_handled);

// Chain 2: UserTest + Wallet + Buy flow + Payment step selector
$rf_chain2_handled = false;
require_once __DIR__ . '/routes/usertest/01_create_test_service.php';
require_once __DIR__ . '/routes/account/01_wallet_account_info.php';
require_once __DIR__ . '/routes/shop/02_buy_service_checkout.php';
require_once __DIR__ . '/routes/shop/03_buy_discount_code.php';
require_once __DIR__ . '/routes/shop/04_confirm_and_provision_service.php';
unset($rf_chain2_handled);

// Chain 3: Payment gateway confirmations (callback based)
$rf_chain3_handled = false;
require_once __DIR__ . '/routes/payment/01_confirm_gateway_user.php';
unset($rf_chain3_handled);

// Chain 4: Receipt submission + affiliates + wheel
$rf_chain4_handled = false;
require_once __DIR__ . '/routes/payment/02_receipt_submission.php';
require_once __DIR__ . '/routes/affiliates/01_affiliates_menu.php';
require_once __DIR__ . '/routes/wheel/01_wheel_luck.php';
unset($rf_chain4_handled);

// Chain 5: Telegram payments + extend flows
$rf_chain5_handled = false;
require_once __DIR__ . '/routes/telegram/01_precheckout_stars_and_extend.php';
unset($rf_chain5_handled);

// === Admin + shutdown (admin include + closing DB connections) ===
require_once __DIR__ . '/parts/shutdown.php';
