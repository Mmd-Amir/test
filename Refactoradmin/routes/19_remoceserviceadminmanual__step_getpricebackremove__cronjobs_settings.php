<?php
rf_set_module('admin/routes/19_remoceserviceadminmanual__step_getpricebackremove__cronjobs_settings.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && (preg_match('/remoceserviceadminmanual-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_invoice = $dataget[1];
    update("user", "Processing_value", $id_invoice, "id", $from_id);
    $invoice = select("invoice", "*", "id_invoice", $id_invoice, "select");
    $requestcheck = select("cancel_service", "*", "username", $invoice['username'], "select");
    if ($requestcheck['status'] == "accept" || $requestcheck['status'] == "reject") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "این درخواست توسط ادمین دیگری بررسی شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $invoice['Service_location'], "select");
    $ManagePanel->RemoveUser($invoice['Service_location'], $requestcheck['username']);
    update("cancel_service", "status", "accept", "username", $requestcheck['username']);
    update("invoice", "status", "removedbyadmin", "username", $requestcheck['username']);
    sendmessage($invoice['id_user'], "✅ کاربری گرامی درخواست حذف شما با نام کاربری  {$invoice['username']} موافقت گردید.", null, 'HTML');
    sendmessage($from_id, "📌 مبلغ  برای بازگشت وجه را ارسال نمایید", $backadmin, 'HTML');
    step("getpricebackremove", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpricebackremove")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    $invoice = select("invoice", "*", "id_invoice", $user['Processing_value'], "select");
    $Balance_id_cancel = select("user", "*", "id", $invoice['id_user'], "select");
    $Balance_id_cancel_fee = intval($Balance_id_cancel['Balance']) + intval($text);
    update("user", "Balance", $Balance_id_cancel_fee, "id", $invoice['id_user']);
    sendmessage($invoice['id_user'], "💰کاربر گرامی مبلغ $text تومان به موجودی شما اضافه گردید.", null, 'HTML');
    sendmessage($from_id, "✅ مبلغ با موفقیت به حساب کاربر اضافه گردید.", $keyboardadmin, 'HTML');
    $text_report = "⭕️ یک ادمین سرویس کاربر که درخواست حذف داشت را تایید کرد
        
اطلاعات کاربر تایید کننده  : 

🪪 آیدی عددی : <code>$from_id</code>
💰 مبلغ بازگشتی : $text تومان
👤 نام کاربری : {$invoice['username']}
آیدی عددی درخواست کننده کنسل کردن : {$invoice['id_user']}";
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherreport,
            'text' => $text_report,
            'parse_mode' => "HTML"
        ]);
    }
    return;
}

if (!$rf_admin_handled && ($datain == "cronjobs_settings" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    if (!function_exists('buildCronJobsKeyboard')) {
        sendmessage($from_id, "امکان مدیریت کرون‌ها در این نسخه فعال نشده است.", $backadmin, 'HTML');
        return;
    }
    $cronIntro = "برای تغییر زمان‌بندی هر کرون، دکمه «⚙️ تنظیمات» همان ردیف را انتخاب کنید.";
    sendmessage($from_id, $cronIntro, buildCronJobsKeyboard(), 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/^cronjob_config-([A-Za-z0-9_]+)/', $datain, $cronMatches) && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    if (!function_exists('getCronJobDefinitions') || !function_exists('loadCronSchedules') || !function_exists('describeCronSchedule')) {
        sendmessage($from_id, "امکان مدیریت کرون‌ها در این نسخه فعال نشده است.", $backadmin, 'HTML');
        return;
    }
    $jobKey = $cronMatches[1];
    $definitions = getCronJobDefinitions();
    if (!isset($definitions[$jobKey])) {
        sendmessage($from_id, "کرون انتخابی یافت نشد.", $backadmin, 'HTML');
        return;
    }
    $schedules = loadCronSchedules();
    $currentSchedule = $schedules[$jobKey] ?? $definitions[$jobKey]['default'];
    $readableSchedule = describeCronSchedule($currentSchedule);
    $definitionLabel = $definitions[$jobKey]['admin_label'];
    $isDisabled = isset($currentSchedule['unit']) && $currentSchedule['unit'] === 'disabled';
    $toggleText = $isDisabled ? '✅ فعال‌سازی کرون' : '❌ غیرفعال‌سازی کرون';
    $toggleAction = $isDisabled ? 'enable' : 'disable';

    $unitKeyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "دقیقه‌ای", 'callback_data' => "cronjob_unit-{$jobKey}-minute"],
                ['text' => "ساعتی", 'callback_data' => "cronjob_unit-{$jobKey}-hour"],
                ['text' => "روزانه", 'callback_data' => "cronjob_unit-{$jobKey}-day"],
            ],
            [
                ['text' => $toggleText, 'callback_data' => "cronjob_toggle-{$jobKey}-{$toggleAction}"],
            ],
            [
                ['text' => "🔙 بازگشت", 'callback_data' => "cronjobs_settings"],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE);
    $message = "⏱ زمان‌بندی فعلی «{$definitionLabel}»: {$readableSchedule}\n\nواحد مورد نظر را انتخاب کنید.";
    sendmessage($from_id, $message, $unitKeyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/^cronjob_toggle-([A-Za-z0-9_]+)-(enable|disable)$/', $datain, $cronMatches) && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    if (!function_exists('getCronJobDefinitions') || !function_exists('updateCronSchedule') || !function_exists('describeCronSchedule')) {
        sendmessage($from_id, "امکان مدیریت کرون‌ها در این نسخه فعال نشده است.", $backadmin, 'HTML');
        return;
    }
    $jobKey = $cronMatches[1];
    $action = $cronMatches[2];
    $definitions = getCronJobDefinitions();
    if (!isset($definitions[$jobKey])) {
        sendmessage($from_id, "کرون انتخابی یافت نشد.", $backadmin, 'HTML');
        return;
    }

    if ($action === 'disable') {
        $newSchedule = ['unit' => 'disabled', 'value' => 1];
        $statusText = "کرون «{$definitions[$jobKey]['admin_label']}» غیرفعال شد.";
    } else {
        $newSchedule = $definitions[$jobKey]['default'] ?? ['unit' => 'minute', 'value' => 1];
        $description = describeCronSchedule($newSchedule);
        $statusText = "کرون «{$definitions[$jobKey]['admin_label']}» فعال شد. زمان‌بندی فعلی: {$description}";
    }

    if (!updateCronSchedule($jobKey, $newSchedule)) {
        sendmessage($from_id, "خطا در ذخیره‌سازی تنظیمات کرون.", $backadmin, 'HTML');
        return;
    }

    sendmessage($from_id, $statusText, buildCronJobsKeyboard(), 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/^cronjob_unit-([A-Za-z0-9_]+)-(minute|hour|day)$/', $datain, $cronMatches) && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    if (!function_exists('getCronJobDefinitions')) {
        sendmessage($from_id, "امکان مدیریت کرون‌ها در این نسخه فعال نشده است.", $backadmin, 'HTML');
        return;
    }
    $jobKey = $cronMatches[1];
    $unit = $cronMatches[2];
    $definitions = getCronJobDefinitions();
    if (!isset($definitions[$jobKey])) {
        sendmessage($from_id, "کرون انتخابی یافت نشد.", $backadmin, 'HTML');
        return;
    }
    $payload = json_encode(['cron_key' => $jobKey, 'unit' => $unit], JSON_UNESCAPED_UNICODE);
    update("user", "Processing_value", $payload, "id", $from_id);
    step("cronjob_set_value", $from_id);
    $unitTitle = getCronUnitTitle($unit);
    sendmessage($from_id, "🔢 مقدار جدید (به صورت عدد) برای بازه زمانی {$unitTitle} ارسال کنید.", $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "cronjob_set_value")) {
    $rf_admin_handled = true;

    $pending = json_decode($user['Processing_value'], true);
    if (!is_array($pending) || empty($pending['cron_key']) || empty($pending['unit'])) {
        sendmessage($from_id, "درخواست نامعتبر است.", $backadmin, 'HTML');
        step('home', $from_id);
        return;
    }
    if (!ctype_digit($text) || intval($text) < 1) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    if (!function_exists('updateCronSchedule') || !function_exists('getCronJobDefinitions') || !function_exists('describeCronSchedule')) {
        sendmessage($from_id, "امکان ذخیره‌سازی تنظیمات کرون وجود ندارد.", $backadmin, 'HTML');
        step('home', $from_id);
        return;
    }
    $definitions = getCronJobDefinitions();
    $jobKey = $pending['cron_key'];
    if (!isset($definitions[$jobKey])) {
        sendmessage($from_id, "کرون انتخابی یافت نشد.", $backadmin, 'HTML');
        step('home', $from_id);
        return;
    }
    $value = intval($text);
    if (!updateCronSchedule($jobKey, ['unit' => $pending['unit'], 'value' => $value])) {
        sendmessage($from_id, "خطا در ذخیره‌سازی تنظیمات کرون.", $backadmin, 'HTML');
        step('home', $from_id);
        return;
    }
    $schedules = loadCronSchedules();
    $currentSchedule = $schedules[$jobKey] ?? ['unit' => $pending['unit'], 'value' => $value];
    $description = describeCronSchedule($currentSchedule);
    $label = $definitions[$jobKey]['admin_label'];
    sendmessage($from_id, "✅ زمان‌بندی «{$label}» به {$description} تغییر کرد.", buildCronJobsKeyboard(), 'HTML');
    update("user", "Processing_value", "", "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "settimecornremovevolume" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['cronjob']['setvolumeremove'] . $setting['cronvolumere'] . "روز", $backadmin, 'HTML');
    step("getcronvolumere", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcronvolumere")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['cronjob']['changeddata'], $setting_panel, 'HTML');
    step("home", $from_id);
    update("setting", "cronvolumere", $text);
    return;
}

if (!$rf_admin_handled && ($datain == "setting_on_holdcron" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "در این بخش باید تغیین کنید که اگر کاربر بعد از چند روز به کانفیگ خود وصل نشد و در وضعیت on_hold بود به کاربر پیام دهد" . $setting['on_hold_day'] . "روز", $backadmin, 'HTML');
    step("on_hold_day", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "on_hold_day")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['cronjob']['changeddata'], $setting_panel, 'HTML');
    step("home", $from_id);
    update("setting", "on_hold_day", $text);
    return;
}

if (!$rf_admin_handled && ($datain == "settimecornremove" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['cronjob']['setdayremove'] . $setting['removedayc'] . "روز", $backadmin, 'HTML');
    step("getdaycron", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getdaycron")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['cronjob']['changeddata'], $setting_panel, 'HTML');
    step("home", $from_id);
    update("setting", "removedayc", $text);
    return;
}

if (!$rf_admin_handled && ($text == "🌐 ثبت آدرس API ترنادو" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "urlpaymenttron", "select");
    $currentUrl = is_array($PaySetting) && isset($PaySetting['ValuePay']) ? $PaySetting['ValuePay'] : 'تنظیم نشده';
    $recommendedUrl = (defined('TRONADO_ORDER_TOKEN_ENDPOINTS') && isset(TRONADO_ORDER_TOKEN_ENDPOINTS[0]))
        ? TRONADO_ORDER_TOKEN_ENDPOINTS[0]
        : 'https://bot.tronado.cloud/api/v1/Order/GetOrderToken';
    $texttronseller = "🌐 آدرس API مورد استفاده برای اتصال به ترنادو را ارسال کنید.\n\nآدرس فعلی: {$currentUrl}\n\nℹ️ پیشنهاد ویژه برای ترنادو:\n{$recommendedUrl}";
    sendmessage($from_id, $texttronseller, $backadmin, 'HTML');
    step('urlpaymenttron', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "urlpaymenttron")) {
    $rf_admin_handled = true;

    $submittedUrl = trim($text);
    $oldDomain = 'tronseller.storeddownloader.fun';
    if (stripos($submittedUrl, $oldDomain) !== false) {
        $warningMessage = "⚠️ دامنه قدیمی ترنادو هنوز استفاده می‌شود. لطفاً آدرس جدید را وارد کنید.";
        sendmessage($from_id, $warningMessage, $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $trnado, 'HTML');
    update("PaySetting", "ValuePay", $submittedUrl, "NamePay", "urlpaymenttron");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "✏️ ویرایش آموزش" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Help']['SelectName'], $json_list_helpkey, 'HTML');
    step("getnameforedite", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnameforedite")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $helpedit, 'HTML');
    update("user", "Processing_value", $text, "id", $from_id);
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "ویرایش نام" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "نام جدید را ارسال کنید", $backadmin, 'HTML');
    step('changenamehelp', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "changenamehelp")) {
    $rf_admin_handled = true;

    if (strlen($text) >= 150) {
        sendmessage($from_id, "❌ نام آموزش باید کمتر از 150 کاراکتر باشد", null, 'HTML');
        return;
    }
    update("help", "name_os", $text, "name_os", $user['Processing_value']);
    sendmessage($from_id, "✅ نام آموزش بروزرسانی شد", $helpedit, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "ویرایش دسته بندی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "دسته بندی جدید خود را ارسال کنید", $backadmin, 'HTML');
    step('changecategoryhelp', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "changecategoryhelp")) {
    $rf_admin_handled = true;

    if (strlen($text) >= 150) {
        sendmessage($from_id, "❌ نام آموزش باید کمتر از 150 کاراکتر باشد", null, 'HTML');
        return;
    }
    update("help", "category", $text, "name_os", $user['Processing_value']);
    sendmessage($from_id, "✅ نام دسته آموزش بروزرسانی شد", $helpedit, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "ویرایش توضیحات" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "توضیحات جدید را ارسال کنید", $backadmin, 'HTML');
    step('changedeshelp', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "changedeshelp")) {
    $rf_admin_handled = true;

    update("help", "Description_os", $text, "name_os", $user['Processing_value']);
    sendmessage($from_id, "✅ توضیحات  آموزش بروزرسانی شد", $helpedit, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "ویرایش رسانه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "تصویر یا فیلم جدید را ارسال کنید", $backadmin, 'HTML');
    step('changemedia', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "changemedia")) {
    $rf_admin_handled = true;

    if ($photo) {
        if (isset($photoid))
            update("help", "Media_os", $photoid, "name_os", $user['Processing_value']);
        update("help", "type_Media_os", "photo", "name_os", $user['Processing_value']);
    } elseif ($video) {
        if (isset($videoid))
            update("help", "Media_os", $videoid, "name_os", $user['Processing_value']);
        update("help", "type_Media_os", "video", "name_os", $user['Processing_value']);
    }
    sendmessage($from_id, "✅ توضیحات  آموزش بروزرسانی شد", $helpedit, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "💰  غیرفعالسازی  نمایش شماره کارت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "برای تمامی کاربران غیرفعال گردید یا کاربران جدید؟
    کاربران جدید 0 
    همه کاربران 1
    2 کاربران بجز نمایندگان", null, 'HTML');
    step('showcardallusers', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "showcardallusers")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['disableshowcardstatus'], null, 'HTML');
    if (intval($text) == "1") {
        update("user", "cardpayment", "0");
        update("setting", "showcard", "0");
    } elseif (intval($text) == 2) {
        update("user", "cardpayment", "0", "agent", "f");
        update("setting", "showcard", "0");
    } else {
        update("setting", "showcard", "0");
    }
    return;
}

if (!$rf_admin_handled && ($text == "💰 فعالسازی نمایش شماره کارت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['activeshowcardstatus'], null, 'HTML');
    update("user", "cardpayment", "1");
    update("setting", "showcard", "1");
    return;
}

if (!$rf_admin_handled && ($text == "🔋 روش تمدید سرویس" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $Methodextend, 'HTML');
    step('updateextendmethod', $from_id);
    return;
}

