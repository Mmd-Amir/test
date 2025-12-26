<?php
rf_set_module('admin/routes/22_step_getprotocoldisable__step_getinbounddisable__resetbot_cancel.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "getprotocoldisable")) {
    $rf_admin_handled = true;

    global $json_list_marzban_panel_inbounds;
    $protocol = ["vless", "vmess", "trojan", "shadowsocks"];
    if (!in_array($text, $protocol)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Inbound']['invalidprotocol'], null, 'HTML');
        return;
    }
    $getinbounds = getinbounds($user['Processing_value'])[$text];
    $list_marzban_panel_inbounds = [
        'keyboard' => [],
        'resize_keyboard' => true,
    ];
    foreach ($getinbounds as $button) {
        $list_marzban_panel_inbounds['keyboard'][] = [
            ['text' => $button['tag']]
        ];
    }
    $list_marzban_panel_inbounds['keyboard'][] = [
        ['text' => "🏠 بازگشت به منوی مدیریت"],
    ];
    $json_list_marzban_panel_inbounds = json_encode($list_marzban_panel_inbounds);
    update("user", "Processing_value_one", $text, "id", $from_id);
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['Inbound']['getInbound'], $json_list_marzban_panel_inbounds, 'HTML');
    step('getInbounddisable', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getInbounddisable")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "نام اینباند با موفقیت ذخیره گردید", $optionMarzban, 'HTML');
    $textpro = "{$user['Processing_value_one']}*$text";
    update("marzban_panel", "inbound_deactive", $textpro, "name_panel", $user['Processing_value']);
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗑 بهینه سازی ربات" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $textoptimize = "❌❌❌❌❌❌❌ متن زیر را با دقت بخوانید

📌 با تایید گزینه زیر عملیات زیر انجام خواهد شد. و قابل بازگشت نیستند

1 - سفارش های غیرفعال حذف خواهند شد
2 - سفارش  های پرداخت نشده حذف خواهند شد.
3 - سفارش های حذف شده توسط ادمین 
4- حذف سرویس های تست غیرفعال
5 - سفارش های حذف شده توسط کاربر 
6 - سفارشاتی که زمان یا حجم شان تمام شده باشد
";
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "✅ تایید و  بهینه سازی", 'callback_data' => 'optimizebot'],
            ],
        ]
    ]);
    sendmessage($from_id, $textoptimize, $Response, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "💀 بازنشانی ربات" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    global $adminnumber;
    $mainAdminId = trim((string) ($adminnumber ?? ''));
    $currentUserId = trim((string) $from_id);
    if ($mainAdminId !== '' && $currentUserId !== $mainAdminId) {
        sendmessage($from_id, "⚠️ فقط ادمین اصلی می‌تواند این بخش را مشاهده کند.", null, 'HTML');
        return;
    }
    $resetWarning = "⚠️ هشدار مهم\n\nبا تایید بازنشانی، تمامی جداول پایگاه داده حذف و مجدداً ساخته خواهند شد. این عملیات غیرقابل بازگشت است.\n\nآیا از انجام این کار مطمئن هستید؟";
    $resetKeyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "✅ بله، مطمئن هستم", 'callback_data' => 'resetbot_confirm'],
                ['text' => "❌ خیر", 'callback_data' => 'resetbot_cancel'],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE);
    sendmessage($from_id, $resetWarning, $resetKeyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "resetbot_cancel")) {
    $rf_admin_handled = true;

    telegram('answerCallbackQuery', array(
        'callback_query_id' => $callback_query_id,
        'text' => "عملیات لغو شد.",
        'show_alert' => false,
        'cache_time' => 5,
    ));
    Editmessagetext($from_id, $message_id, "❌ عملیات بازنشانی لغو شد.", null);
    return;
}

if (!$rf_admin_handled && ($datain == "resetbot_confirm" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    global $pdo, $domainhosts, $adminnumber;
    $mainAdminId = trim((string) ($adminnumber ?? ''));
    $currentUserId = trim((string) $from_id);
    if ($mainAdminId !== '' && $currentUserId !== $mainAdminId) {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "❌ شما اجازه انجام این عملیات را ندارید.",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    telegram('answerCallbackQuery', array(
        'callback_query_id' => $callback_query_id,
        'text' => "⏳ در حال بازنشانی...",
        'show_alert' => false,
        'cache_time' => 5,
    ));
    Editmessagetext($from_id, $message_id, "⏳ عملیات بازنشانی ربات آغاز شد. لطفاً منتظر بمانید...", null);

    $dropError = null;
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($tables)) {
            foreach ($tables as $tableName) {
                $tableName = trim($tableName);
                if ($tableName !== '') {
                    $pdo->exec("DROP TABLE IF EXISTS `{$tableName}`;");
                }
            }
        }
    } catch (Throwable $exception) {
        $dropError = $exception;
    } finally {
        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        } catch (Throwable $ignored) {
        }
    }

    if ($dropError !== null) {
        file_put_contents(RF_APP_ROOT . '/resetbot_error.log', '[' . date('Y-m-d H:i:s') . "] DROP ERROR: " . $dropError->getMessage() . PHP_EOL, FILE_APPEND);
        Editmessagetext($from_id, $message_id, "❌ خطا در حذف جداول. لطفاً فایل resetbot_error.log را بررسی کنید.", null);
        sendmessage($from_id, "❌ عملیات بازنشانی به دلیل خطا در حذف جداول متوقف شد.", null, 'HTML');
        return;
    }

    $resetUrlUsed = '';
    $reinstallSuccess = false;
    $installerErrors = [];
    $candidateUrls = [];
    $normalizedHost = '';

    if (!empty($domainhosts)) {
        $normalizedHost = rtrim($domainhosts, '/');
        $candidateUrls[] = "https://{$normalizedHost}/table.php";
        $candidateUrls[] = "http://{$normalizedHost}/table.php";
    }

    $attemptInstallerRequest = function (string $url) use (&$resetUrlUsed, &$reinstallSuccess, &$installerErrors) {
        if ($reinstallSuccess || $url === '') {
            return;
        }

        $response = false;
        $httpCode = null;

        if (function_exists('curl_init')) {
            $curlHandle = @curl_init($url);
            if ($curlHandle !== false) {
                curl_setopt_array($curlHandle, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 20,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ]);
                $response = curl_exec($curlHandle);
                if ($response === false) {
                    $installerErrors[] = 'cURL error: ' . curl_error($curlHandle) . " ({$url})";
                } else {
                    $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
                }
                curl_close($curlHandle);
            }
        }

        if ($response === false) {
            $streamContext = stream_context_create([
                'http' => [
                    'timeout' => 20,
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $response = @file_get_contents($url, false, $streamContext);
            if ($response === false) {
                $installerErrors[] = 'stream error: unable to fetch ' . $url;
            } else {
                $httpCode = 200;
            }
        }

        if ($response !== false && ($httpCode === null || ($httpCode >= 200 && $httpCode < 400))) {
            $resetUrlUsed = $url;
            $reinstallSuccess = true;
        }
    };

    foreach ($candidateUrls as $candidateUrl) {
        $attemptInstallerRequest($candidateUrl);
        if ($reinstallSuccess) {
            break;
        }
    }

    if (!$reinstallSuccess) {
        $localTablePath = RF_APP_ROOT . '/table.php';
        if (is_file($localTablePath)) {
            try {
                include $localTablePath;
                $reinstallSuccess = true;
                $resetUrlUsed = 'local include';
            } catch (Throwable $tableError) {
                $installerErrors[] = 'local table include: ' . $tableError->getMessage();
                file_put_contents(RF_APP_ROOT . '/resetbot_error.log', '[' . date('Y-m-d H:i:s') . "] TABLE ERROR: " . $tableError->getMessage() . PHP_EOL, FILE_APPEND);
                Editmessagetext($from_id, $message_id, "⚠️ جداول حذف شدند اما اجرای table.php با خطا مواجه شد.", null);
                sendmessage($from_id, "⚠️ اجرای table.php با خطا مواجه شد. لطفاً فایل resetbot_error.log را بررسی کنید.", null, 'HTML');
                return;
            }
        }
    }

    if ($reinstallSuccess) {
        $successMessage = "✅ بازنشانی ربات با موفقیت انجام شد." . (!empty($resetUrlUsed) ? "\nمنبع اجرا: {$resetUrlUsed}" : '');
        Editmessagetext($from_id, $message_id, $successMessage, null);
        sendmessage($from_id, "✅ عملیات بازنشانی ربات با موفقیت انجام شد.", null, 'HTML');
    } else {
        if (!empty($installerErrors)) {
            file_put_contents(RF_APP_ROOT . '/resetbot_error.log', '[' . date('Y-m-d H:i:s') . "] INSTALL ERROR: " . implode(' | ', $installerErrors) . PHP_EOL, FILE_APPEND);
        }
        $manualUrlHint = !empty($normalizedHost) ? "لطفاً لینک https://{$normalizedHost}/table.php را به صورت دستی باز کنید." : "لطفاً فایل table.php را به صورت دستی اجرا کنید.";
        $warningText = "⚠️ جداول حذف شدند اما اجرای table.php انجام نشد. {$manualUrlHint}";
        Editmessagetext($from_id, $message_id, $warningText, null);
        sendmessage($from_id, $warningText, null, 'HTML');
    }
    return;
}

if (!$rf_admin_handled && ($datain == "optimizebot")) {
    $rf_admin_handled = true;

    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE Status = 'unpaid' AND name_product != 'سرویس تست'");
    $stmt->execute();
    $countunpiadorder = $stmt->rowCount();
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE Status = 'disabled' AND name_product != 'سرویس تست'");
    $stmt->execute();
    $countdisableorder = $stmt->rowCount();
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE (Status = 'removebyadmin' or Status = 'removedbyadmin')");
    $stmt->execute();
    $countremoveadminorder = $stmt->rowCount();
    $stmt = $pdo->prepare("SELECT * FROM invoice WHERE Status = 'disabled' AND name_product = 'سرویس تست'");
    $stmt->execute();
    $countdisableordtester = $stmt->rowCount();
    #remove data
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE Status = 'unpaid' AND name_product != 'سرویس تست'");
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE Status = 'disabled' AND name_product != 'سرویس تست'");
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE Status = 'removebyadmin'");
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE Status = 'removedbyadmin'");
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE Status = 'disabled' AND name_product = 'سرویس تست'");
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE Status = 'removeTime'");
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE Status = 'removevolume'");
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE Status = 'removebyuser' ");
    $stmt->execute();
    $optimizebot = "
✅ $countunpiadorder سفارش پرداخت نشده حذف گردید
✅ $countdisableorder عدد سفارش غیرفعال حذف گردید.
✅ $countremoveadminorder عدد سفارش حذف شده ادمین حذف گردید
✅ $countdisableordtester عدد سفارش تست حذف گردید.";
    Editmessagetext($from_id, $message_id, $optimizebot, null);
    $time = time();
    $logss = "optimize_{$countunpiadorder}_{$countdisableorder}_{$countremoveadminorder}_{$countdisableordtester}_$time";
    file_put_contents('log.txt', "\n" . $logss, FILE_APPEND);
    return;
}

if (!$rf_admin_handled && ($datain == "settimecornvolume")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تنظیم کنید که اگر حجم کاربر به x رسید پیام اخطار ارسال شود. حجم را براساس گیگ ارسال نمایید.", $backadmin, 'HTML');
    step("getvolumewarn", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getvolumewarn")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, "❌ مقدار نامعتبر", null, 'html');
        return;
    }
    update("setting", "volumewarn", $text);
    sendmessage($from_id, "✅ تغییرات با موفقیت ذخیره شد", $setting_panel, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🔧 ساخت کانفیگ دستی")) {
    $rf_admin_handled = true;

    savedata("clear", "idpanel", $user['Processing_value']);
    sendmessage($from_id, "📌در این بخش میتوانید یک سفارش را بطور دستی ایجاد و دریافت کنید 
⚠️ در صورتی که می خواهید  کانفیگ به حساب کاربر اضافه شود و کاربر مدیریت کند باید از گزینه افزودن سفارش  استفاده نمایید.
- برای اضافه کردن کانفیگ ابتدا نام کاربری را ارسال نمایید.", $backadmin, 'HTML');
    step('getusernameconfigcr', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getusernameconfigcr")) {
    $rf_admin_handled = true;

    if (!preg_match('~(?!_)^[a-z][a-z\d_]{2,32}(?<!_)$~i', $text)) {
        sendmessage($from_id, $textbotlang['users']['invalidusername'], $backadmin, 'HTML');
        return;
    }
    update("user", "Processing_value_one", $text, "id", $from_id);
    step('getcountcreate', $from_id);
    sendmessage($from_id, "📌 تعداد کانفیگی که میخواهید ساخته شود را ارسال کنید حداکثر ۱۰ تا می توانید ارسال کنید", $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcountcreate")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    if (intval($text) > 10 or intval($text) < 0) {
        sendmessage($from_id, "❌ حداقل ۱ عدد و حداکثر می توانید ۱۰ عدد ارسال کنید.", $backadmin, 'HTML');
        return;
    }
    savedata("save", "count", $text);
    step('getvolumesconfig', $from_id);
    sendmessage($from_id, "📌 حجم مصرفی اکانت را ارسال نمایید . حجم براساس گیگابایت است.", $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getvolumesconfig")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, "❌ مقدار نامعتبر", null, 'html');
        return;
    }
    update("user", "Processing_value_tow", $text, "id", $from_id);
    sendmessage($from_id, "📌 زمان سرویس را ارسال نمایید زمان براساس روز است.", $backadmin, 'HTML');
    step("gettimeaccount", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettimeaccount")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    if (!ctype_digit($text)) {
        sendmessage($from_id, "❌ مقدار نامعتبر", null, 'html');
        return;
    }
    if (intval($text) == 0) {
        $expire = 0;
    } else {
        $datetimestep = strtotime("+" . $text . "days");
        $expire = strtotime(date("Y-m-d H:i:s", $datetimestep));
    }
    $datac = array(
        'expire' => $expire,
        'data_limit' => $user['Processing_value_tow'] * pow(1024, 3),
        'from_id' => $from_id,
        'username' => "$username",
        'type' => "new by admin $from_id"
    );
    $panel = select("marzban_panel", "*", "name_panel", $userdata['idpanel'], "select");
    for ($i = 0; $i < $userdata['count']; $i++) {
        $usernameconfig = $user['Processing_value_one'] . "_" . $i;
        $dataoutput = $ManagePanel->createUser($userdata['idpanel'], "usertest", $usernameconfig, $datac);
        if ($dataoutput['username'] == null) {
            $dataoutput['msg'] = json_encode($dataoutput['msg']);
            sendmessage($from_id, $textbotlang['users']['sell']['ErrorConfig'], null, 'HTML');
            $texterros = "
⭕️ یک کاربر قصد دریافت اکانت داشت که ساخت کانفیگ با خطا مواجه شده و به کاربر کانفیگ داده نشد
✍️ دلیل خطا : 
{$dataoutput['msg']}
آیدی کابر : $from_id
نام کاربری کاربر : @$username
نام پنل : {$panel['name_panel']}";
            if (strlen($setting['Channel_Report']) > 0) {
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $errorreport,
                    'text' => $texterros,
                    'parse_mode' => "HTML"
                ]);
                step("home", $from_id);
            }
            return;
        }
        $randomString = bin2hex(random_bytes(5));
        $output_config_link = $panel['sublink'] == "onsublink" ? $dataoutput['subscription_url'] : "";
        $config = "";
        if ($panel['config'] == "onconfig" && is_array($dataoutput['configs'])) {
            foreach ($dataoutput['configs'] as $link) {
                $config .= "\n" . $link;
            }
        }
        $datatextbot['textafterpay'] = $panel['type'] == "Manualsale" ? $datatextbot['textmanual'] : $datatextbot['textafterpay'];
        $datatextbot['textafterpay'] = $panel['type'] == "WGDashboard" ? $datatextbot['text_wgdashboard'] : $datatextbot['textafterpay'];
        $datatextbot['textafterpay'] = $panel['type'] == "ibsng" || $panel['type'] == "mikrotik" ? $datatextbot['textafterpayibsng'] : $datatextbot['textafterpay'];
        if (intval($text) == 0)
            $text = $textbotlang['users']['stateus']['Unlimited'];
        $textcreatuser = str_replace('{username}', "<code>{$dataoutput['username']}</code>", $datatextbot['textafterpay']);
        $textcreatuser = str_replace('{name_service}', "پلن دلخواه", $textcreatuser);
        $textcreatuser = str_replace('{location}', $panel['name_panel'], $textcreatuser);
        $textcreatuser = str_replace('{day}', $text, $textcreatuser);
        $textcreatuser = str_replace('{volume}', $user['Processing_value_tow'], $textcreatuser);
        $textcreatuser = applyConnectionPlaceholders($textcreatuser, $output_config_link, $config);
        if ($panel['type'] == "Manualsale" || $panel['type'] == "ibsng" || $panel['type'] == "mikrotik") {
            $textcreatuser = str_replace('{password}', $dataoutput['subscription_url'], $textcreatuser);
            update("invoice", "user_info", $dataoutput['subscription_url'], "id_invoice", $randomString);
        }
        sendMessageService($panel, $dataoutput['configs'], $output_config_link, $dataoutput['username'], null, $textcreatuser, $randomString);
    }
    sendmessage($from_id, $textbotlang['users']['selectoption'], $optionathmarzban, 'HTML');
    $text_report = "";
    if (strlen($setting['Channel_Report']) > 0) {
        $text_report = " 🛍 ساخت کانفیگ توسط ادمین 

نام کاربری کانفیگ : {$user['Processing_value_one']}
حجم کانفیگ  : {$user['Processing_value_tow']} گیگ
زمان کانفیگ : $text روز
آیدی عددی ادمین : $from_id
نام کاربری ادمین : $username
تعداد ساخت : {$userdata['count']}";
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $buyreport,
            'text' => $text_report,
            'parse_mode' => "HTML"
        ]);
    }
    update("user", "Processing_value", $userdata['idpanel'], "id", $from_id);
    step("home", $from_id);
    return;
}

