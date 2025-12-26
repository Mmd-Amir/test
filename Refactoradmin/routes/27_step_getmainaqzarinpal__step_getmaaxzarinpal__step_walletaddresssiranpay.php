<?php
rf_set_module('admin/routes/27_step_getmainaqzarinpal__step_getmaaxzarinpal__step_walletaddresssiranpay.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ زرین پال")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmainaqzarinpal", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmainaqzarinpal")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $aqayepardakht, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalancezarinpal");
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ زرین پال")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmaaxzarinpal", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmaaxzarinpal")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $aqayepardakht, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalancezarinpal");
    return;
}

if (!$rf_admin_handled && ($user['step'] == "walletaddresssiranpay")) {
    $rf_admin_handled = true;

    $walletInputSource = $text;
    if (isset($update) && is_array($update)) {
        if (isset($update['message']['text']) && is_string($update['message']['text'])) {
            $walletInputSource = $update['message']['text'];
        } elseif (isset($update['edited_message']['text']) && is_string($update['edited_message']['text'])) {
            $walletInputSource = $update['edited_message']['text'];
        }
    }

    $walletInput = trim((string) $walletInputSource);

    $userRecord = select("user", "*", "id", $from_id, "select");
    $processingData = [];
    if ($userRecord && isset($userRecord['Processing_value'])) {
        $decodedProcessing = json_decode($userRecord['Processing_value'], true);
        if (is_array($decodedProcessing)) {
            $processingData = $decodedProcessing;
        }
    }

    $walletOrigin = $processingData['walletaddress_origin'] ?? 'general';
    $invalidKeyboard = $walletOrigin === 'trnado' ? $trnado : $backadmin;

    if ($walletInput === '' || !preg_match('/^T[a-zA-Z0-9]{33}$/', $walletInput)) {
        sendmessage($from_id, "❌ آدرس ولت وارد شده نامعتبر است. لطفاً آدرس TRC20 معتبر ارسال کنید.", $invalidKeyboard, 'HTML');
        return;
    }

    $standardizedWallet = $walletInput;

    $successKeyboard = $walletOrigin === 'trnado' ? $trnado : $keyboardadmin;

    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $successKeyboard, 'HTML');
    update("PaySetting", "ValuePay", $standardizedWallet, "NamePay", "walletaddress");
    update("user", "Processing_value", '{}', "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "💼 ثبت آدرس ولت ترون (TRC20)" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "walletaddress", "select");
    $currentWallet = $PaySetting['ValuePay'] ?? '';
    $texttronseller = "💼 لطفاً آدرس ولت ترون (TRC20) مرتبط با درگاه ترنادو را ارسال کنید.\n\nولت فعلی شما: {$currentWallet}";
    sendmessage($from_id, $texttronseller, $trnado, 'HTML');
    savedata('clear', 'walletaddress_origin', 'trnado');
    step('walletaddresssiranpay', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "api  درگاه ارزی ریالی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "apiiranpay", "select")['ValuePay'];
    $texttronseller = "📌 کد api خود را ارسال نمایید.
        
        مرچنت فعلی شما : $PaySetting";
    sendmessage($from_id, $texttronseller, $backadmin, 'HTML');
    step('apiiranpay', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "apiiranpay")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $iranpaykeyboard, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "apiiranpay");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ ارزی ریالی سوم")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("minbalanceiranpay", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "minbalanceiranpay")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $iranpaykeyboard, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalanceiranpay");
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ ارزی ریالی سوم")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("maxbalanceiranpay", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "maxbalanceiranpay")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $iranpaykeyboard, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalanceiranpay");
    return;
}

if (!$rf_admin_handled && ($text == "📍 حداقل حجم دلخواه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل حجم که کاربر میتواند تهیه کند  برای این پنل را ارسال نمایید.", $backadmin, 'HTML');
    step('GetmaineExtra', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GetmaineExtra")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backuser, 'HTML');
        return;
    }
    savedata("clear", "namepanel", $user['Processing_value']);
    savedata("save", "mainvalume", $text);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['gettypeextra'], $backuser, 'HTML');
    step('gettypeextramain', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettypeextramain")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidtypeagent'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $typepanel = select("marzban_panel", "*", "name_panel", $userdata['namepanel'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['saveddata']);
    $eextraprice = json_decode($typepanel['mainvolume'], true);
    $eextraprice[$text] = $userdata['mainvalume'];
    $eextraprice = json_encode($eextraprice);
    update("marzban_panel", "mainvolume", $eextraprice, "name_panel", $userdata['namepanel']);
    update("user", "Processing_value", $userdata['namepanel'], "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📍 حداکثر حجم دلخواه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر حجم که کاربر میتواند تهیه کند  برای این پنل را ارسال نمایید.", $backadmin, 'HTML');
    step('GetmaxeExtra', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GetmaxeExtra")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backuser, 'HTML');
        return;
    }
    savedata("clear", "namepanel", $user['Processing_value']);
    savedata("save", "maxvolume", $text);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['gettypeextra'], $backuser, 'HTML');
    step('gettypeextramax', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettypeextramax")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidtypeagent'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $typepanel = select("marzban_panel", "*", "name_panel", $userdata['namepanel'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['saveddata']);
    $eextraprice = json_decode($typepanel['maxvolume'], true);
    $eextraprice[$text] = $userdata['maxvolume'];
    $eextraprice = json_encode($eextraprice);
    update("marzban_panel", "maxvolume", $eextraprice, "name_panel", $userdata['namepanel']);
    update("user", "Processing_value", $userdata['namepanel'], "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📍 حداقل زمان دلخواه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل زمانی دلخواهی  که کاربر میتواند تهیه کند  برای این پنل را ارسال نمایید.", $backadmin, 'HTML');
    step('Getmaintime', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "Getmaintime")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backuser, 'HTML');
        return;
    }
    savedata("clear", "namepanel", $user['Processing_value']);
    savedata("save", "maintime", $text);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['gettypeextra'], $backuser, 'HTML');
    step('gettypeextramaintime', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettypeextramaintime")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidtypeagent'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $typepanel = select("marzban_panel", "*", "name_panel", $userdata['namepanel'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['saveddata']);
    $eextraprice = json_decode($typepanel['maintime'], true);
    $eextraprice[$text] = $userdata['maintime'];
    $eextraprice = json_encode($eextraprice);
    update("marzban_panel", "maintime", $eextraprice, "name_panel", $userdata['namepanel']);
    update("user", "Processing_value", $userdata['namepanel'], "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📍 حداکثر زمان دلخواه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر زمانی دلخواهی  که کاربر میتواند تهیه کند  برای این پنل را ارسال نمایید.", $backadmin, 'HTML');
    step('Getmaxtime', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "Getmaxtime")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backuser, 'HTML');
        return;
    }
    savedata("clear", "namepanel", $user['Processing_value']);
    savedata("save", "maxtime", $text);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['gettypeextra'], $backuser, 'HTML');
    step('gettypeextramaxtime', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettypeextramaxtime")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidtypeagent'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $typepanel = select("marzban_panel", "*", "name_panel", $userdata['namepanel'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['saveddata']);
    $eextraprice = json_decode($typepanel['maxtime'], true);
    $eextraprice[$text] = $userdata['maxtime'];
    $eextraprice = json_encode($eextraprice);
    update("marzban_panel", "maxtime", $eextraprice, "name_panel", $userdata['namepanel']);
    update("user", "Processing_value", $userdata['namepanel'], "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🔼 اضافه کردن دپارتمان")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 ایدی عددی ادمینی که میخواهید پیام ها به آن ادمین ارسال شود را بفرستید", $backadmin, 'HTML');
    step("getidadmindep", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getidadmindep")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    savedata('clear', 'idadmin', $text);
    sendmessage($from_id, "📌 نام دپارتمان را ارسال نمایید", $backadmin, 'HTML');
    step("getdeparteman", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getdeparteman")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    $stmt = $pdo->prepare("INSERT IGNORE INTO departman (idsupport,name_departman) VALUES (:idsupport,:name_departman)");
    $stmt->bindParam(':idsupport', $userdata['idadmin']);
    $stmt->bindParam(':name_departman', $text);
    $stmt->execute();
    step("home", $from_id);
    sendmessage($from_id, "📌 دپارتمان با موفقیت اضافه گردید.", $supportcenter, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🔽 حذف کردن دپارتمان")) {
    $rf_admin_handled = true;

    $countdeparteman = select("departman", "*", null, null, "count");
    if ($countdeparteman == 0) {
        sendmessage($from_id, "❌ دپارتمانی برای حذف وجود ندارد.", $departemanslist, 'HTML');
        return;
    }
    sendmessage($from_id, "📌 نوع دپارتمان را برای حذف ارسال کنید.", $departemanslist, 'HTML');
    step("getremovedep", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getremovedep")) {
    $rf_admin_handled = true;

    $stmt = $pdo->prepare("DELETE FROM departman WHERE name_departman = ?");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    sendmessage($from_id, "📌 بخش مورد نظر حذف گردید.", $supportcenter, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⚙️ تنظیمات سرویس" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $textsetservice = "📌 برای تنظیم سرویس یک کانفیگ در پنل خود ساخته و  سرویس هایی که میخواهید فعال باشند. را داخل پنل فعال کرده و نام کاربری کانفیگ را ارسال نمایید";
    sendmessage($from_id, $textsetservice, $backadmin, 'HTML');
    step('getservceid', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getservceid")) {
    $rf_admin_handled = true;

    $userdata = json_decode(getuserm($text, $user['Processing_value'])['body'], true);
    if (isset($userdata['detail']) and $userdata['detail'] == "User not found") {
        sendmessage($from_id, "کاربر در پنل وجود ندارد", null, 'HTML');
        return;
    }
    update("marzban_panel", "proxies", json_encode($userdata['service_ids']), "name_panel", $user['Processing_value']);
    step("home", $from_id);
    sendmessage($from_id, "✅ اطلاعات با موفقیت تنظیم گردید", $optionmarzneshin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "👤 تنظیم آیدی پشتیبانی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $textcart = "📌 نام کاربری خود را بدون @ برای پشتیبانی  ارسال کنید\n\n{$setting['id_support']}";
    sendmessage($from_id, $textcart, $backadmin, 'HTML');
    step('idsupportset', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "idsupportset")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingPayment']['CartDirect'], $supportcenter, 'HTML');
    update("setting", "id_support", $text, null, null);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش کارت به کارت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("gethelpcart", $from_id);
    return;
}

