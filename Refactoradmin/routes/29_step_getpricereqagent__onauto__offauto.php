<?php
rf_set_module('admin/routes/29_step_getpricereqagent__onauto__offauto.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "getpricereqagent")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ تغییرات با موفقیت ذخیره گردید", $setting_panel, 'HTML');
    step("home", $from_id);
    update("setting", "agentreqprice", $text, null, null);
    return;
}

if (!$rf_admin_handled && ($text == "🤖 تایید رسید  بدون بررسی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $paymentverify = select("PaySetting", "ValuePay", "NamePay", "statuscardautoconfirm", "select")['ValuePay'];
    if ($paymentverify == "onautoconfirm") {
        sendmessage($from_id, "❌ ابتدا تایید خودکار را خاموش کنید.", null, 'HTML');
        return;
    }
    $paymentverify = select("PaySetting", "ValuePay", "NamePay", "autoconfirmcart", "select")['ValuePay'];
    $keyboardverify = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $paymentverify, 'callback_data' => $paymentverify],
            ],
        ]
    ]);
    sendmessage($from_id, "📌 با فعال کردن این قابلیت  در زمان هایی که آنلاین نیستید ربات بصورت خودکار تمامی تراکنش های کارت به کارت را تایید می کند سپس بعد از آنلاین شدن شما رسید ها را بررسی میکنید سپس اگر رسید فیک  ارسال شده تراکنش را کنسل میکنید", $keyboardverify, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "onauto")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", "offauto", "NamePay", "autoconfirmcart");
    $paymentverify = select("PaySetting", "ValuePay", "NamePay", "autoconfirmcart", "select")['ValuePay'];
    $keyboardverify = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $paymentverify, 'callback_data' => $paymentverify],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, "خاموش شد", $keyboardverify);
    return;
}

if (!$rf_admin_handled && ($datain == "offauto")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", "onauto", "NamePay", "autoconfirmcart");
    $paymentverify = select("PaySetting", "ValuePay", "NamePay", "autoconfirmcart", "select")['ValuePay'];
    $keyboardverify = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $paymentverify, 'callback_data' => $paymentverify],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, "روشن شد", $keyboardverify);
    return;
}

if (!$rf_admin_handled && (preg_match('/transferaccount_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    update("user", "Processing_value", $iduser, "id", $from_id);
    sendmessage($from_id, "آیدی عددی کاربری که میخواهید تمامی اطلاعات به آن کاربر منتقل شود را ارسال نمایید
    توجه داشتید باشید در کاربر مقصد در صورت داشتن موجودی حذف خواهد شد", $backadmin, 'HTML');
    step("getidfortransfers", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getidfortransfers")) {
    $rf_admin_handled = true;

    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    if ($text == $user['Processing_value']) {
        sendmessage($from_id, "❌ شما نمی توانید اطلاعات به کاربر فعلی منتقل کنید", $keyboardadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "اطلاعات با موفقیت به حساب کاربری جدید منتقل گردید", $keyboardadmin, 'HTML');
    $stmt = $pdo->prepare("DELETE FROM user WHERE id = :id_user");
    $stmt->bindParam(':id_user', $text, PDO::PARAM_STR);
    $stmt->execute();
    update("user", "id", $text, "id", $user['Processing_value']);
    update("Payment_report", "id_user", $text, "id_user", $user['Processing_value']);
    update("invoice", "id_user", $text, "id_user", $user['Processing_value']);
    update("support_message", "iduser", $text, "iduser", $user['Processing_value']);
    update("service_other", "id_user", $text, "id_user", $user['Processing_value']);
    update("Giftcodeconsumed", "id_user", $text, "id_user", $user['Processing_value']);
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🖼 پس زمینه کیوآرکد")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "تصویر خود را برای پس زمینه ارسال کنید", $backadmin, 'HTML');
    step("getimagebackgroundqr", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getimagebackgroundqr")) {
    $rf_admin_handled = true;

    if (!$photo) {
        sendmessage($from_id, "تصویر نامعتبر است", $backadmin, 'HTML');
        return;
    }
    $response = getFileddire($photoid);
    if ($response['ok']) {
        $filePath = $response['result']['file_path'];
        $fileUrl = "https://api.telegram.org/file/bot$APIKEY/$filePath";
        $fileContent = file_get_contents($fileUrl);
        file_put_contents("custom.jpg", $fileContent);
        file_put_contents("images.jpg", $fileContent);
        sendmessage($from_id, "🖼 پس زمینه با موفقیت تنظیم گردید", $setting_panel, 'HTML');
        step("home", $from_id);
    }
    return;
}

if (!$rf_admin_handled && ($text == "⚙️ تنظیم پروتکل و اینباند" || $text == "🎛 تنظیم نام گروه" || $text == "⚙️ تنظیم نود")) {
    $rf_admin_handled = true;

    if ($text == "🎛 تنظیم نام گروه") {
        $textsetprotocol = "📌 نام گروهی که بصورت پیشفرض می خواهید از آن ساخته شود را ارسال نمایید.";
    } elseif ($text == "⚙️ تنظیم نود") {
        $textsetprotocol = "📌 برای تنظیم نود یک کاربر در پنل خود ساخته و  نودهایی که میخواهید فعال باشند. را داخل پنل فعال کرده و نام کاربری کاربر را ارسال نمایید";
    } else {
        $textsetprotocol = "📌 برای تنظیم اینباند  و پروتکل باید یک کانفیگ در پنل خود ساخته و  پروتکل و اینباند هایی که میخواهید فعال باشند. را داخل پنل فعال کرده و نام کاربری کانفیگ را ارسال نمایید";
    }
    sendmessage($from_id, $textsetprotocol, $backadmin, 'HTML');
    step("setinboundandprotocol", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "setinboundandprotocol")) {
    $rf_admin_handled = true;

    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if ($panel['type'] == "marzban") {
        if ($new_marzban) {
            $DataUserOut = getuser($text, $user['Processing_value']);
            if (!empty($DataUserOut['error'])) {
                sendmessage($from_id, $DataUserOut['error'], null, 'HTML');
                return;
            }
            if (!empty($DataUserOut['status']) && $DataUserOut['status'] != 200) {
                sendmessage($from_id, "❌  خطایی رخ داده است کد خطا :  {$DataUserOut['status']}", null, 'HTML');
                return;
            }
            $DataUserOut = json_decode($DataUserOut['body'], true);
            if ((isset($DataUserOut['msg']) && $DataUserOut['msg'] == "User not found") or !isset($DataUserOut['proxy_settings'])) {
                sendmessage($from_id, $textbotlang['users']['stateus']['UserNotFound'], null, 'html');
                return;
            }
            foreach ($DataUserOut['proxy_settings'] as $key => &$value) {
                if ($key == "shadowsocks") {
                    unset($DataUserOut['proxy_settings'][$key]['password']);
                } elseif ($key == "trojan") {
                    unset($DataUserOut['proxy_settings'][$key]['password']);
                } else {
                    unset($DataUserOut['proxy_settings'][$key]['id']);
                }
                if (count($DataUserOut['proxy_settings'][$key]) == 0) {
                    $DataUserOut['proxy_settings'][$key] = new stdClass();
                }
            }
            update("marzban_panel", "inbounds", json_encode($DataUserOut['group_ids']), "name_panel", $user['Processing_value']);
            update("marzban_panel", "proxies", json_encode($DataUserOut['proxy_settings'], true), "name_panel", $user['Processing_value']);
        } else {
            $DataUserOut = getuser($text, $user['Processing_value']);
            if (!empty($DataUserOut['error'])) {
                sendmessage($from_id, $DataUserOut['error'], null, 'HTML');
                return;
            }
            if (!empty($DataUserOut['status']) && $DataUserOut['status'] != 200) {
                sendmessage($from_id, "❌  خطایی رخ داده است کد خطا :  {$DataUserOut['status']}", null, 'HTML');
                return;
            }
            $DataUserOut = json_decode($DataUserOut['body'], true);
            if ((isset($DataUserOut['msg']) && $DataUserOut['msg'] == "User not found") or !isset($DataUserOut['proxies'])) {
                sendmessage($from_id, $textbotlang['users']['stateus']['UserNotFound'], null, 'html');
                return;
            }
            foreach ($DataUserOut['proxies'] as $key => &$value) {
                if ($key == "shadowsocks") {
                    unset($DataUserOut['proxies'][$key]['password']);
                } elseif ($key == "trojan") {
                    unset($DataUserOut['proxies'][$key]['password']);
                } else {
                    unset($DataUserOut['proxies'][$key]['id']);
                }
                if (count($DataUserOut['proxies'][$key]) == 0) {
                    $DataUserOut['proxies'][$key] = new stdClass();
                }
            }
            update("marzban_panel", "inbounds", json_encode($DataUserOut['inbounds']), "name_panel", $user['Processing_value']);
            update("marzban_panel", "proxies", json_encode($DataUserOut['proxies'], true), "name_panel", $user['Processing_value']);
        }
    } elseif ($panel['type'] == "s_ui") {
        $data = GetClientsS_UI($text, $panel['name_panel']); {
            if (count($data) == 0) {
                sendmessage($from_id, "❌ یوزر در پنل وجود ندارد.", $options_ui, 'HTML');
                return;
            }
            $servies = [];
            foreach ($data['inbounds'] as $service) {
                $servies[] = $service;
            }
            update("marzban_panel", "proxies", json_encode($servies, true), "name_panel", $user['Processing_value']);
        }
    } elseif ($panel['type'] == "ibsng" || $panel['type'] == "mikrotik") {
        update("marzban_panel", "proxies", $text, "name_panel", $user['Processing_value']);
    }
    if ($panel['type'] == "ibsng") {
        sendmessage($from_id, "✅ نام گروه با موفقیت تنظیم گردید.", $optionibsng, 'HTML');
    } elseif ($panel['type'] == "mikrotik") {
        sendmessage($from_id, "✅ نام گروه با موفقیت تنظیم گردید.", $option_mikrotik, 'HTML');
    } else {
        sendmessage($from_id, "✅ اینباند و پروتکل های شما با موفقیت تنظیم گردیدند.", $optionMarzban, 'HTML');
    }
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🔋 وضعیت تمدید" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $marzbanstatus = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $keyboardstatus = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanstatus['status_extend'], 'callback_data' => $marzbanstatus['status_extend']],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['activepanel'], $keyboardstatus, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "on_extend")) {
    $rf_admin_handled = true;

    update("marzban_panel", "status_extend", "off_extend", "name_panel", $user['Processing_value']);
    $marzbanstatus = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $keyboardstatus = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanstatus['status_extend'], 'callback_data' => $marzbanstatus['status_extend']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['activepanelStatusOff'], $keyboardstatus);
    return;
}

if (!$rf_admin_handled && ($datain == "off_extend")) {
    $rf_admin_handled = true;

    update("marzban_panel", "status_extend", "on_extend", "name_panel", $user['Processing_value']);
    $marzbanstatus = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $keyboardstatus = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanstatus['status_extend'], 'callback_data' => $marzbanstatus['status_extend']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['activepaneltatuson'], $keyboardstatus);
    return;
}

if (!$rf_admin_handled && ((preg_match('/confirmchannel-(\w+)/', $datain, $dataget)))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    $userdata = select("user", "*", "id", $iduser, "select");
    if ($userdata['joinchannel'] == "active") {
        sendmessage($from_id, "✍️ کاربر از قبل تایید شده است", null, 'HTML');
        return;
    }
    update("user", "joinchannel", "active", "id", $iduser);
    sendmessage($from_id, "📌 کاربر از این پس بدون عضویت در کانال می تواند در ربات فعالیت داشته باشد", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ((preg_match('/zerobalance-(\w+)/', $datain, $dataget)))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    $userdata = select("user", "*", "id", $iduser, "select");
    update("user", "Balance", "0", "id", $iduser);
    sendmessage($from_id, "موجودی کاربر به مبلغ {$userdata['Balance']} صفر گردید", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/removeadmin_(\w+)/', $datain, $dataget) && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $idadmin = trim($dataget[1]);
    $mainAdminId = trim((string) $adminnumber);
    if ($idadmin === $mainAdminId) {
        sendmessage($from_id, "❌ امکان حذف ادمین اصلی وجود ندارد", null, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("DELETE FROM admin WHERE TRIM(id_admin) = :id_admin");
    $stmt->bindParam(':id_admin', $idadmin, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        sendmessage($from_id, "⚠️ ادمینی با این شناسه یافت نشد.", null, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ ادمین با موفقیت حذف گردید", null, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🫣 مخفی کردن پنل برای یک کاربر" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آیدی عددی کاربر را برای این پنل را ارسال نمایید.", $backadmin, 'HTML');
    step('getuserhide', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getuserhide")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], "✅ پنل با موفقیت برای کاربر مخفی گردید");
    if ($typepanel['hide_user'] == null) {
        $hideuserid = [];
    } else {
        $hideuserid = json_decode($typepanel['hide_user'], true);
    }
    $hideuserid[] = $text;
    $hideuserid = json_encode($hideuserid);
    update("marzban_panel", "hide_user", $hideuserid, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "❌  حذف کاربر از لیست مخفی شدگان" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آیدی عددی کاربر را برای این پنل را ارسال نمایید.", $backadmin, 'HTML');
    step('getuserhideforremove', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getuserhideforremove")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    step("home", $from_id);
    if ($typepanel['hide_user'] == null) {
        outtypepanel($typepanel['type'], "❌ هیچ کاربری در لیست مخفی شدگان وجود ندارد");
        return;
    }
    $hideuserid = json_decode($typepanel['hide_user'], true);
    if (count($hideuserid) == 0) {
        outtypepanel($typepanel['type'], "❌  کاربر در لیست وجود ندارد");
        return;
    }
    if (!in_array($text, $hideuserid)) {
        outtypepanel($typepanel['type'], "❌ کاربر در لیست وجود ندارد.");
        return;
    }
    $key = array_search($text, $hideuserid);
    if ($key !== false) {
        unset($hideuserid[$key]);
        $hideuserid = array_values($hideuserid);
    }
    $hideuserid = json_encode($hideuserid);
    update("marzban_panel", "hide_user", $hideuserid, "name_panel", $user['Processing_value']);
    outtypepanel($typepanel['type'], "✅  کاربر با موفقیت از لیست حذف گردید.");
    return;
}

if (!$rf_admin_handled && ($datain == "scoresetting")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $lottery, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "1️⃣ تنظیم جایزه نفر اول")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 مقدار مبلغی که می خواهید حساب کاربر شارژ شود را ارسال نمایید.", $lottery, 'HTML');
    step("getonelotary", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getonelotary")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ جایزه با موفقیت تنظیم شد", $lottery, 'HTML');
    step("home", $from_id);
    $data = json_decode($setting['Lottery_prize'], true);
    $data['one'] = $text;
    $data = json_encode($data, true);
    update("setting", "Lottery_prize", $data, null, null);
    return;
}

if (!$rf_admin_handled && ($text == "2️⃣ تنظیم جایزه نفر دوم")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 مقدار مبلغی که می خواهید حساب کاربر شارژ شود را ارسال نمایید.", $lottery, 'HTML');
    step("getonelotary2", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getonelotary2")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ جایزه با موفقیت تنظیم شد", $lottery, 'HTML');
    step("home", $from_id);
    $data = json_decode($setting['Lottery_prize'], true);
    $data['tow'] = $text;
    $data = json_encode($data, true);
    update("setting", "Lottery_prize", $data, null, null);
    return;
}

if (!$rf_admin_handled && ($text == "3️⃣ تنظیم جایزه نفر سوم")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 مقدار مبلغی که می خواهید حساب کاربر شارژ شود را ارسال نمایید.", $lottery, 'HTML');
    step("getonelotary3", $from_id);
    return;
}

