<?php
rf_set_module('admin/routes/12_step_getlocationedit__step_getnamenew__step_geturlnew.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "GetLocationEdit")) {
    $rf_admin_handled = true;

    $marzban_list_get = select("marzban_panel", "*", "name_panel", $text, "select");
    if (!is_array($marzban_list_get) || empty($marzban_list_get)) {
        $notFoundMessage = $textbotlang['Admin']['managepanel']['nullpanel'] ?? "❌ پنل مورد نظر یافت نشد.";
        sendmessage($from_id, $notFoundMessage, $json_list_marzban_panel, 'HTML');
        return;
    }
    if ($marzban_list_get['type'] == "marzban") {
        $Check_token = token_panel($marzban_list_get['code_panel'], false);
        if (isset($Check_token['access_token'])) {
            $System_Stats = Get_System_Stats($text);
            if ($new_marzban) {
                $active_users = $System_Stats['active_users']
                    ?? $System_Stats['users_active']
                    ?? $System_Stats['online_users']
                    ?? 0;
            } else {
                $active_users = $System_Stats['users_active']
                    ?? $System_Stats['active_users']
                    ?? $System_Stats['online_users']
                    ?? 0;
            }
            $total_user = $System_Stats['total_user'];
            $mem_total = formatBytes($System_Stats['mem_total']);
            $mem_used = formatBytes($System_Stats['mem_used']);
            $bandwidth = formatBytes($System_Stats['outgoing_bandwidth'] + $System_Stats['incoming_bandwidth']);
            $ListSell = number_format(mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) FROM invoice WHERE (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND Service_location = '{$marzban_list_get['name_panel']}' AND name_product != 'سرویس تست'"))['COUNT(*)']);
            $ListSellSUM = number_format(mysqli_fetch_assoc(mysqli_query($connect, "SELECT SUM(price_product) FROM invoice WHERE (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND Service_location = '{$marzban_list_get['name_panel']}' AND name_product != 'سرویس تست'"))['SUM(price_product)']);

            $Condition_marzban = "";
            $text_marzban = "
آمار پنل شما👇:
                             
🖥 وضعیت اتصال پنل مرزبان: ✅ پنل متصل است
👥  تعداد کل کاربران: $total_user
👤 تعداد کاربران فعال: $active_users
📡 نسخه پنل مرزبان :  {$System_Stats['version']}
💻 رم  کل سرور  : $mem_total
💻 مصرف رم پنل مرزبان  : $mem_used
🌐 ترافیک کل مصرف شده  ( آپلود / دانلود) : $bandwidth
🛍 تعداد فروش کل در این پنل : $ListSell
🛍 جمع فروش کل در این پنل : $ListSellSUM تومان
گروه کاربری :{$marzban_list_get['agent']}
        
⭕️ برای مدیریت پنل یکی از گزینه های زیر را انتخاب کنید";
            sendmessage($from_id, $text_marzban, $optionMarzban, 'HTML');
        } elseif (isset($Check_token['detail']) && $Check_token['detail'] == "Incorrect username or password") {
            $text_marzban = "❌ نام کاربری یا رمز عبور پنل اشتباه است";
            sendmessage($from_id, $text_marzban, $optionMarzban, 'HTML');
        } else {
            $errorDetails = json_encode($Check_token, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $text_marzban = $textbotlang['Admin']['managepanel']['errorstateuspanel'];
            if (!empty($errorDetails) && $errorDetails !== 'null') {
                $text_marzban .= PHP_EOL . "علت خطا: {$errorDetails}";
            }
            sendmessage($from_id, $text_marzban, $optionMarzban, 'HTML');
        }
    } elseif ($marzban_list_get['type'] == "x-ui_single") {
        $x_ui_check_connect = login($marzban_list_get['code_panel'], false);
        if ($x_ui_check_connect['success']) {
            sendmessage($from_id, $textbotlang['Admin']['managepanel']['connectx-ui'], $optionX_ui_single, 'HTML');
        } elseif (!empty($x_ui_check_connect['msg']) && $x_ui_check_connect['msg'] == "Invalid username or password.") {
            $text_marzban = "❌ نام کاربری یا رمز عبور پنل اشتباه است";
            sendmessage($from_id, $text_marzban, $optionX_ui_single, 'HTML');
        } else {
            $text_marzban = $textbotlang['Admin']['managepanel']['errorstateuspanel'];
            if (!empty($x_ui_check_connect['errror'])) {
                $text_marzban .= PHP_EOL . "علت خطا: {$x_ui_check_connect['errror']}";
            }
            sendmessage($from_id, $text_marzban, $optionX_ui_single, 'HTML');
        }
    } elseif ($marzban_list_get['type'] == "alireza_single") {
        $x_ui_check_connect = login($marzban_list_get['code_panel'], false);
        if ($x_ui_check_connect['success']) {
            sendmessage($from_id, $textbotlang['Admin']['managepanel']['connectx-ui'], $optionalireza_single, 'HTML');
        } elseif (!empty($x_ui_check_connect['msg']) && $x_ui_check_connect['msg'] == "The username or password is incorrect") {
            $text_marzban = "❌ نام کاربری یا رمز عبور پنل اشتباه است";
            sendmessage($from_id, $text_marzban, $optionalireza_single, 'HTML');
        } else {
            $text_marzban = $textbotlang['Admin']['managepanel']['errorstateuspanel'];
            if (!empty($x_ui_check_connect['errror'])) {
                $text_marzban .= PHP_EOL . "علت خطا: {$x_ui_check_connect['errror']}";
            }
            sendmessage($from_id, $text_marzban, $optionalireza_single, 'HTML');
        }
    } elseif ($marzban_list_get['type'] == "hiddify") {
        $System_Stats = serverstatus($marzban_list_get['name_panel']);
        if (!empty($System_Stats['status']) && $System_Stats['status'] != 200) {
            $text_marzban = "❌ خطایی در دریافت اطلاعات رخ داده است کد خطا : " . $System_Stats['status'];
            sendmessage($from_id, $text_marzban, $optionhiddfy, 'HTML');
        } elseif (!empty($System_Stats['error'])) {
            $text_marzban = "❌ خطایی در دریافت اطلاعات رخ داده است  خطا : " . $System_Stats['error'];
            sendmessage($from_id, $text_marzban, $optionhiddfy, 'HTML');
        } else {
            $System_Stats = json_decode($System_Stats['body'], true);
            if (isset($System_Stats['stats'])) {
                $mem_total = round($System_Stats['stats']['system']['ram_total'], 2);
                $mem_used = round($System_Stats['stats']['system']['ram_used'], 2);
                $outgoingBandwidth = 0;
                $incomingBandwidth = 0;

                if (isset($System_Stats['outgoing_bandwidth']) || isset($System_Stats['incoming_bandwidth'])) {
                    $outgoingBandwidth = (float) ($System_Stats['outgoing_bandwidth'] ?? 0);
                    $incomingBandwidth = (float) ($System_Stats['incoming_bandwidth'] ?? 0);
                } elseif (isset($System_Stats['stats']['outgoing_bandwidth']) || isset($System_Stats['stats']['incoming_bandwidth'])) {
                    $outgoingBandwidth = (float) ($System_Stats['stats']['outgoing_bandwidth'] ?? 0);
                    $incomingBandwidth = (float) ($System_Stats['stats']['incoming_bandwidth'] ?? 0);
                }

                $bandwidth = formatBytes($outgoingBandwidth + $incomingBandwidth);
                $text_marzban = "
آمار پنل شما👇:
                             
🖥 وضعیت اتصال پنل : ✅ پنل متصل است
💻 رم  کل سرور  : $mem_total
💻 مصرف رم پنل   : $mem_used
گروه کاربری :{$marzban_list_get['agent']}
⭕️ برای مدیریت پنل یکی از گزینه های زیر را انتخاب کنید";
                sendmessage($from_id, $text_marzban, $optionhiddfy, 'HTML');
            } elseif (isset($System_Stats['message']) && $System_Stats['message'] == "Unathorized") {
                $text_marzban = "❌  لینک پنل اشتباه ارسال شده است";
                sendmessage($from_id, $text_marzban, $optionhiddfy, 'HTML');
            } else {
                sendmessage($from_id, "پنل متصل نیست", $optionhiddfy, 'HTML');
            }
        }
    } elseif ($marzban_list_get['type'] == "Manualsale") {
        sendmessage($from_id, "یک گزینه را انتخاب نمایید", $optionManualsale, 'HTML');
    } elseif ($marzban_list_get['type'] == "marzneshin") {
        $Check_token = token_panelm($marzban_list_get['code_panel']);
        if (isset($Check_token['access_token'])) {
            $System_Stats = Get_System_Statsm($text);
            if (!empty($System_Stats['status']) && $System_Stats['status'] != 200) {
                $text_marzban = "❌ خطایی در دریافت اطلاعات رخ داده است کد خطا : " . $System_Stats['status'];
                sendmessage($from_id, $text_marzban, $optionMarzban, 'HTML');
                return;
            } elseif (!empty($System_Stats['error'])) {
                $text_marzban = "❌ خطایی در دریافت اطلاعات رخ داده است  خطا : " . $System_Stats['error'];
                sendmessage($from_id, $text_marzban, $optionMarzban, 'HTML');
                return;
            }
            $System_Stats = json_decode($System_Stats['body'], true);
            $active_users = $System_Stats['active'];
            $total_user = $System_Stats['total'];
            $ListSell = number_format(mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) FROM invoice WHERE (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND Service_location = '{$marzban_list_get['name_panel']}' AND name_product != 'سرویس تست'"))['COUNT(*)']);
            $ListSellSUM = number_format(mysqli_fetch_assoc(mysqli_query($connect, "SELECT SUM(price_product) FROM invoice WHERE (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND Service_location = '{$marzban_list_get['name_panel']}' AND name_product != 'سرویس تست'"))['SUM(price_product)']);
            $Condition_marzban = "";
            $text_marzban = "
آمار پنل شما👇:
                             
🖥 وضعیت اتصال پنل مرزبان: ✅ پنل متصل است
👥  تعداد کل کاربران: $total_user
👤 تعداد کاربران فعال: $active_users
🛍 تعداد فروش کل در این پنل : $ListSell
🛍 جمع فروش کل در این پنل : $ListSellSUM تومان
گروه کاربری :{$marzban_list_get['agent']}
        
⭕️ برای مدیریت پنل یکی از گزینه های زیر را انتخاب کنید";
            sendmessage($from_id, $text_marzban, $optionmarzneshin, 'HTML');
        } elseif (isset($Check_token['detail']) && $Check_token['detail'] == "Incorrect username or password") {
            $text_marzban = "❌ نام کاربری یا رمز عبور پنل اشتباه است";
            sendmessage($from_id, $text_marzban, $optionMarzban, 'HTML');
        } else {
            $text_marzban = $textbotlang['Admin']['managepanel']['errorstateuspanel'] . json_encode($Check_token);
            sendmessage($from_id, $text_marzban, $optionMarzban, 'HTML');
        }
    } elseif ($marzban_list_get['type'] == "WGDashboard") {
        sendmessage($from_id, $textbotlang['users']['selectoption'], $optionwg, 'HTML');
    } elseif ($marzban_list_get['type'] == "s_ui") {
        sendmessage($from_id, $textbotlang['users']['selectoption'], $options_ui, 'HTML');
    } elseif ($marzban_list_get['type'] == "ibsng") {
        $result = loginIBsng($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
        if ($result) {
            sendmessage($from_id, $result['msg'], $optionibsng, 'HTML');
        } else {
            sendmessage($from_id, $result['msg'], $optionibsng, 'HTML');
        }
    } elseif ($marzban_list_get['type'] == "mikrotik") {
        $result = login_mikrotik($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
        if (isset($result['error'])) {
            sendmessage($from_id, json_encode($result), $option_mikrotik, 'HTML');
        } else {
            $free_hdd_space = round($result['free-hdd-space'] / pow(1024, 3), 2);
            $free_memory = round($result['free-memory'] / pow(1024, 3), 2);
            $free_memory = round($result['free-memory'] / pow(1024, 3), 2);
            $total_hdd_space = round($result['total-hdd-space'] / pow(1024, 3), 2);
            $total_memory = round($result['total-memory'] / pow(1024, 3), 2);
            sendmessage($from_id, "<b>📡 اطلاعات سیستم MikroTik شما:</b>

<blockquote>
🖥 <b>پلتفرم:</b> {$result['platform']}  
🏷 <b>نسخه:</b> {$result['version']}  
🕰 <b>مدت زمان روشن بودن:</b> {$result['uptime']}  
</blockquote>

<blockquote>
💽 <b>نام معماری:</b> {$result['architecture-name']}  
📋 <b>مدل برد:</b> {$result['board-name']}  
🏗 <b>زمان ساخت سیستم:</b> {$result['build-time']}  
</blockquote>

<blockquote>
⚙️ <b>پردازنده:</b> {$result['cpu']}  
🔢 <b>تعداد هسته‌ها:</b> {$result['cpu-count']}  
🚀 <b>فرکانس CPU:</b> {$result['cpu-frequency']}  
📊 <b>میزان بار CPU:</b> {$result['cpu-load']} %
</blockquote>

<blockquote>
💾 <b>فضای کل هارد:</b> $total_hdd_space گیگ  
📂 <b>فضای آزاد هارد:</b> $free_hdd_space گیگ  
🧠 <b>حافظه کل رم:</b> $total_memory گیگ  
📉 <b>حافظه آزاد رم:</b> $free_memory گیگ
</blockquote>

<blockquote>
📝 <b>سکتورهای نوشته‌شده از زمان ریبوت:</b> {$result['write-sect-since-reboot']}  
🧮 <b>مجموع سکتورهای نوشته‌شده:</b> {$result['write-sect-total']}
</blockquote>
", $option_mikrotik, 'HTML');
        }
    } else {
        sendmessage($from_id, "یک گزینه را انتخاب نمایید", $optionMarzban, 'HTML');
    }
    update("user", "Processing_value", $text, "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "✍️ نام پنل" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['managepanel']['GetNameNew'], $backadmin, 'HTML');
    step('GetNameNew', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GetNameNew")) {
    $rf_admin_handled = true;

    if (in_array($text, $marzban_list)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Repeatpanel'], $backadmin, 'HTML');
        return;
    }
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['ChangedNmaePanel']);
    update("user", "Processing_value", $text, "id", $from_id);
    update("marzban_panel", "name_panel", $text, "name_panel", $user['Processing_value']);
    update("invoice", "Service_location", $text, "Service_location", $user['Processing_value']);
    update("product", "Location", $text, "Location", $user['Processing_value']);
    update("user", "Processing_value", $text, "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🔗 ویرایش آدرس پنل" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['managepanel']['geturlnew'], $backadmin, 'HTML');
    step('GeturlNew', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GeturlNew")) {
    $rf_admin_handled = true;

    if (!filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Invalid-domain'], $backadmin, 'HTML');
        return;
    }
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['ChangedurlPanel']);
    update("marzban_panel", "url_panel", $text, "name_panel", $user['Processing_value']);
    update("marzban_panel", "datelogin", null, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📍 تغییر گروه کاربری" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 نوع کاربری را ارسال کنید
گروه های کاربری : f,n,n2
❌ در صورتی که می خواهید پنل برای تمام گروه کاربری ها نمایش داده شود متن all را ارسال کنید", $backadmin, 'HTML');
    step('getagentpanel', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getagentpanel")) {
    $rf_admin_handled = true;

    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], "📌گروه کاربری با موفقیت تغییر کرد");
    update("marzban_panel", "agent", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🔗 دامنه لینک ساب" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 اگر پنل ثنایی هستید یک لینک ساب کاربر را از پنل کپی کرده سپس در این بخش ارسال کنید .بقیه پنل ها باید طبق ساختارش ارسال نمایید.", $backadmin, 'HTML');
    step('GeturlNewx', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GeturlNewx")) {
    $rf_admin_handled = true;

    $inputLink = trim($text);
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if ($typepanel['type'] !== "x-ui_single" && !filter_var($inputLink, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Invalid-domain'], $backadmin, 'HTML');
        return;
    }
    if ($typepanel['type'] === "x-ui_single") {
        $text = normalizeXuiSingleSubscriptionBaseUrl($inputLink);
    } else {
        $text = $inputLink;
    }
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['ChangedurlPanel']);
    update("marzban_panel", "linksubx", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🔗 uuid admin" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 uuid ادمین را ارسال کنید", $backadmin, 'HTML');
    step('getuuidadmin', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getuuidadmin")) {
    $rf_admin_handled = true;

    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], "✅ uuid ادمین ذخیره گردید");
    update("marzban_panel", "secret_code", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🚨 محدودیت ساخت اکانت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['managepanel']['setlimit'], $backadmin, 'HTML');
    step('getlimitnew', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getlimitnew")) {
    $rf_admin_handled = true;

    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['changedlimit']);
    update("marzban_panel", "limit_panel", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⏳ زمان سرویس تست" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "🕰 مدت زمان سرویس تست را ارسال کنید.
⚠️ زمان بر حسب ساعت است.", $backadmin, 'HTML');
    step('updatetime', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "updatetime")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['saveddata']);
    update("marzban_panel", "time_usertest", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "💾 حجم اکانت تست" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "حجم سرویس تست را ارسال کنید.
⚠️ حجم بر حسب مگابایت است.", $backadmin, 'HTML');
    step('val_usertest', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "val_usertest")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['saveddata']);
    update("marzban_panel", "val_usertest", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "💎 تنظیم شناسه اینباند" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 شناسه اینباندی که می خواهید کانفیگ ازآن ساخته شود راارسال نمایید.  شناسه اینباند یک عدد چند رقمی است که در پنل  در صفحه اینباند ها ستون id  نوشته شده است

⚠️ در صورتی که پنل wgdashboard هستید باید نام کانفیگ را ارسال نمایید", $backadmin, 'HTML');
    step('getinboundiid', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getinboundiid")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ شناسه اینباند با موفقیت ذخیره گردید", $optionX_ui_single, 'HTML');
    update("marzban_panel", "inboundid", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "👤 ویرایش نام کاربری" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getusernamenew'], $backadmin, 'HTML');
    step('GetusernameNew', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GetusernameNew")) {
    $rf_admin_handled = true;

    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['ChangedusernamePanel']);
    update("marzban_panel", "username_panel", $text, "name_panel", $user['Processing_value']);
    update("marzban_panel", "datelogin", null, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⚙️ تنظیم پروتکل" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['managepanel']['Inbound']['GetProtocol'], $keyboardprotocol, 'HTML');
    step('getprotocolx_ui', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getprotocolx_ui")) {
    $rf_admin_handled = true;

    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['setprotocol']);
    $marzbanprotocol = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    update("x_ui", "protocol", $text, "codepanel", $marzbanprotocol['code_panel']);
    step('home', $from_id);
    return;
}

