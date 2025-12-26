<?php
rf_set_module('admin/routes/settings/02_toggle_status_and_cron_callbacks.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;

if (!$rf_admin_handled && (preg_match('/^editstsuts-(.*)-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    require_once __DIR__ . '/toggles/01_apply_toggle_changes.php';
    require_once __DIR__ . '/toggles/02_render_status_keyboard.php';

    return;
}
