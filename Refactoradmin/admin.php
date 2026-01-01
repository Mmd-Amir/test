<?php
// Refactored admin.php entrypoint (split into smaller modules)
//
// Usage:
// - Include this file from your main entry (e.g. end of index.php) when user is admin
// - Or replace legacy admin.php with a tiny loader that includes Refactoradmin/admin.php

if (!function_exists('rf_set_module')) {
    require_once __DIR__ . '/helpers/logger.php';
}
if (!defined('RF_APP_ROOT')) {
    // /path/to/project/Refactoradmin -> project root is one level up.
    define('RF_APP_ROOT', dirname(__DIR__));
}
@chdir(RF_APP_ROOT);

// Install error handlers (safe if already installed)
require_once __DIR__ . '/helpers/error_handler.php';
require_once __DIR__ . '/helpers/compat_functions.php';
require_once __DIR__ . '/helpers/flow.php';

rf_set_module('Refactoradmin/admin.php');

// Guard: admin only
if (!isset($from_id) || !isset($admin_ids) || !in_array($from_id, $admin_ids)) {
    return;
}

// Compatibility: some installs use $data instead of $datain
if (!isset($datain) && isset($data)) {
    $datain = $data;
}

$rf_admin_handled = false;

require_once __DIR__ . '/bootstrap.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/00_admin__admin_backadmin__hide_mini_app_instruction.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/01_stats.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/02_month_current_stat__view_stat_time__step_get_time_start.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/03_types.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/04_startaction__cancel_sendmessage__step_changetextstart.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/05_step_text_discount__step_text_add_balance__step_text_sell.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/06_messaging.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/07_step_text_roll.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/settings/02_toggle_status_and_cron_callbacks.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/08_limits.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/09_reject_pay__step_reject-dec__step_selectloc.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/10_step_add_balance_all__typebalanceall__typecustomer.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/11_step_removeprotocol__step_updatemethodusername__step_getnamecustom.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/12_step_getlocationedit__step_getnamenew__step_geturlnew.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/13_step_getpaawordnew__step_confirmremovepanel__backlistuser.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/14_balance.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/15_step_adddecriptionblock__acceptblock__verify.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/16_step_setpercentage__step_setbanner__step_cartdirect.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/17_step_getmaxbuyagent__searchorder__step_getusernameconfigandordedrs.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/18_editshops__rejectremoceserviceadmin__step_descriptionsrequsts.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/19_remoceserviceadminmanual__step_getpricebackremove__cronjobs_settings.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/20_step_updateextendmethod__onautoconfirm__offautoconfirm.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/21_addagentrequest__setagenttype__iranpay2setting.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/22_step_getprotocoldisable__step_getinbounddisable__resetbot_cancel.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/23_set_panel_pasargad__step_getlocoption__bakcnode.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/24_step_getpricecashback__step_getagent__editpayment.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/25_step_getcashiranpay4__step_getcashiranpay1__step_getcashplisio.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/26_offpayverify__step_getnameedit__step_getcontentedit.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/27_step_getmainaqzarinpal__step_getmaaxzarinpal__step_walletaddresssiranpay.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/28_step_gethelpcart__step_gethelpnowpayment__step_gethelpperfect.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/29_step_getpricereqagent__onauto__offauto.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/30_step_getonelotary3__gradonhshans__step_getpricewheel.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/31_orders.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/32_step_getpricevolumesrc__settimepricesrc__step_getpricetimesrc.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/33_editpanel__startelegram__step_getmainaqstar.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/34_step_getpricnn2__step_getpriceftime__step_getpricnntime.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/35_on_buy_porsant__off_buy_porsant__step_text_request_agent_dec.php';
if ($rf_admin_handled) return;

require_once __DIR__ . '/routes/36_previous_pageuserzero__step_edit_app__step_get_new_lin_app.php';
if ($rf_admin_handled) return;

// No admin route matched.
return;
