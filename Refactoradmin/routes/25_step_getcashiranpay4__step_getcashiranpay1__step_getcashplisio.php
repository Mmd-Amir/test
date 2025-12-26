<?php
rf_set_module('admin/routes/25_step_getcashiranpay4__step_getcashiranpay1__step_getcashplisio.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "getcashiranpay4")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "chashbackiranpay3");
    return;
}

if (!$rf_admin_handled && ($text == "💰 کش بک ارزی ریالی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید کاربر پس از پرداخت چه درصدی به عنوان هدیه به حسابش واریز شود. ( برای غیرفعال کردن این قابلیت عدد صفر ارسال کنید)", $backadmin, 'HTML');
    step("getcashiranpay1", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcashiranpay1")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت ذخیره گردید.", $Swapinokey, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "chashbackiranpay1");
    return;
}

if (!$rf_admin_handled && ($text == "💰 کش بک plisio")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید کاربر پس از پرداخت چه درصدی به عنوان هدیه به حسابش واریز شود. ( برای غیرفعال کردن این قابلیت عدد صفر ارسال کنید)", $backadmin, 'HTML');
    step("getcashplisio", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcashplisio")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت ذخیره گردید.", $CartManage, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "chashbackplisio");
    return;
}

if (!$rf_admin_handled && ($text == "💰 کش بک nowpayment")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید کاربر پس از پرداخت چه درصدی به عنوان هدیه به حسابش واریز شود. ( برای غیرفعال کردن این قابلیت عدد صفر ارسال کنید)", $backadmin, 'HTML');
    step("getcashnowpayment", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcashnowpayment")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت ذخیره گردید.", $nowpayment_setting_keyboard, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "cashbacknowpayment");
    return;
}

if (!$rf_admin_handled && ($text == "💰 کش بک زرین پال")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید کاربر پس از پرداخت چه درصدی به عنوان هدیه به حسابش واریز شود. ( برای غیرفعال کردن این قابلیت عدد صفر ارسال کنید)", $backadmin, 'HTML');
    step("getcashzarinpal", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcashzarinpal")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت ذخیره گردید.", $keyboardzarinpal, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "chashbackzarinpal");
    return;
}

if (!$rf_admin_handled && ($text == "➕ اضافه کردن کانفیگ")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 برای اضافه کردن کانفیگ ابتدا یک نام ارسال نمایید.", $backadmin, 'HTML');
    step('getnameconfigm', $from_id);
    savedata("clear", "namepanel", $user['Processing_value']);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnameconfigm")) {
    $rf_admin_handled = true;

    $exitsname = select("manualsell", "*", "namerecord", $text, "count");
    if (intval($exitsname) != 0) {
        sendmessage($from_id, "این نام وجود دارد", null, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $product = [];
    savedata("save", "namerecord", $text);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE Location = :text or Location = '/all' ");
    $stmt->bindParam(':text', $userdata['namepanel'], PDO::PARAM_STR);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $product[] = [$row['name_product']];
    }
    $list_product = [
        'keyboard' => [],
        'resize_keyboard' => true,
    ];
    $list_product['keyboard'][] = [
        ['text' => "🏠 بازگشت به منوی مدیریت"],
    ];
    foreach ($product as $button) {
        $list_product['keyboard'][] = [
            ['text' => $button[0]]
        ];
    }
    $json_list_product_list_admin = json_encode($list_product);
    sendmessage($from_id, "📌 نام محصول خود را ارسال نمایید در صورتی که میخواهید  برای اکانت تست تنظیم کنید متن تست را ارسال کنید.", $json_list_product_list_admin, 'HTML');
    step('getnameproduct', $from_id);
    savedata("save", "namerecord", $text);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnameproduct")) {
    $rf_admin_handled = true;

    if ($text != "تست") {
        $product = select("product", "*", "name_product", $text, "select");
        if ($product == false) {
            sendmessage($from_id, "محصول در ربات وجود ندارد", $backadmin, 'HTML');
            return;
        }
        savedata("save", "codeproduct", $product['code_product']);
    } else {
        savedata("save", "codeproduct", "usertest");
    }
    sendmessage($from_id, "📌 کانفیگ یا متن دیگر خود را ارسال نمایید", $backadmin, 'HTML');
    step('getconfigtext', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getconfigtext")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ کانفیگ با موفقیت ذخیره گردید.", $optionManualsale, 'HTML');
    step('home', $from_id);
    $userdata = json_decode($user['Processing_value'], true);
    $panel = select("marzban_panel", "*", "name_panel", $userdata['namepanel'], "select");
    $status = "active";
    $stmt = $pdo->prepare("INSERT IGNORE INTO manualsell (codepanel,namerecord,contentrecord,status,codeproduct) VALUES (:codepanel,:namerecord,:contentrecord,:status,:codeproduct)");
    $stmt->bindParam(':codepanel', $panel['code_panel']);
    $stmt->bindParam(':namerecord', $userdata['namerecord']);
    $stmt->bindParam(':contentrecord', $text);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':codeproduct', $userdata['codeproduct']);
    $stmt->execute();
    update("user", "Processing_value", $panel['name_panel'], "id", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "❌ حذف کانفیگ")) {
    $rf_admin_handled = true;

    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    $listconfig = [];
    $stmt = $pdo->prepare("SELECT * FROM manualsell WHERE codepanel = '{$panel['code_panel']}'");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $listconfig[] = [$row['namerecord']];
    }
    $list_configmanual = [
        'keyboard' => [],
        'resize_keyboard' => true,
    ];
    $list_configmanual['keyboard'][] = [
        ['text' => "🏠 بازگشت به منوی مدیریت"],
    ];
    foreach ($listconfig as $button) {
        $list_configmanual['keyboard'][] = [
            ['text' => $button[0]]
        ];
    }
    $json_list_manualconfig_list = json_encode($list_configmanual);
    sendmessage($from_id, "📌 نام کانفیگی که میخواهید حذف نمایید را ارسال کنید ", $json_list_manualconfig_list, 'HTML');
    step("getnameremove", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnameremove")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ کانفیگ با موفقیت حذف گردید.", $optionManualsale, 'HTML');
    $stmt = $pdo->prepare("DELETE FROM manualsell WHERE namerecord = ?");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🌍 قیمت تغییر لوکیشن" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 قیمت تغییر لوکیشن از سایر پنل‌ها به این پنل را ارسال کنید", $backadmin, 'HTML');
    step('setpricechangelocation', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "setpricechangelocation")) {
    $rf_admin_handled = true;

    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], "📌قیمت تغییر لوکیشن با موفقیت تغییر کرد");
    update("marzban_panel", "priceChangeloc", $text, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "➕ قیمت حجم اضافه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 قیمت حجم اضافه برای این پنل را ارسال نمایید.", $backadmin, 'HTML');
    step('GetPriceExtra', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GetPriceExtra")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    savedata("clear", "namepanel", $user['Processing_value']);
    savedata("save", "price", $text);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['gettypeextra'] . "\n" . "⚠️ در صورتی که می خواهید قیمت برای تمامی گروه های کاربری تنظیم شود متن <code>all</code> را ارسال کنید", $backuser, 'HTML');
    step('gettypeextra', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettypeextra")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f", "all"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidtypeagent'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $typepanel = select("marzban_panel", "*", "name_panel", $userdata['namepanel'], "select");
    outtypepanel($typepanel['type'], $textbotlang['users']['Extra_volume']['ChangedPrice']);
    $eextraprice = json_decode($typepanel['priceextravolume'], true);
    if ($text == 'all') {
        $eextraprice["f"] = $userdata['price'];
        $eextraprice["n"] = $userdata['price'];
        $eextraprice["n2"] = $userdata['price'];
    } else {
        $eextraprice[$text] = $userdata['price'];
    }
    $eextraprice = json_encode($eextraprice);
    update("marzban_panel", "priceextravolume", $eextraprice, "name_panel", $userdata['namepanel']);
    update("user", "Processing_value", $userdata['namepanel'], "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⚙️ قیمت حجم سرویس دلخواه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 قیمت حجم اضافه دلخواه این پنل را ارسال نمایید.", $backadmin, 'HTML');
    step('GetPricecustomvo', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GetPricecustomvo")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    savedata("clear", "namepanel", $user['Processing_value']);
    savedata("save", "price", $text);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['gettypeextra'] . "\n" . "⚠️ در صورتی که می خواهید قیمت برای تمامی گروه های کاربری تنظیم شود متن <code>all</code> را ارسال کنید", $backuser, 'HTML');
    step('gettypeextracustom', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettypeextracustom")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f", "all"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidtypeagent'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $typepanel = select("marzban_panel", "*", "name_panel", $userdata['namepanel'], "select");
    outtypepanel($typepanel['type'], $textbotlang['users']['Extra_volume']['ChangedPrice']);
    $eextraprice = json_decode($typepanel['pricecustomvolume'], true);
    if ($text == 'all') {
        $eextraprice["f"] = $userdata['price'];
        $eextraprice["n"] = $userdata['price'];
        $eextraprice["n2"] = $userdata['price'];
    } else {
        $eextraprice[$text] = $userdata['price'];
    }
    $eextraprice = json_encode($eextraprice);
    update("marzban_panel", "pricecustomvolume", $eextraprice, "name_panel", $userdata['namepanel']);
    update("user", "Processing_value", $userdata['namepanel'], "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⏳ قیمت زمان اضافه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 قیمت زمان اضافه برای این پنل را ارسال نمایید.", $backadmin, 'HTML');
    step('GetPricetimeextra', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GetPricetimeextra")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    savedata("clear", "namepanel", $user['Processing_value']);
    savedata("save", "price", $text);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['gettypeextra'] . "\n" . "⚠️ در صورتی که می خواهید قیمت برای تمامی گروه های کاربری تنظیم شود متن <code>all</code> را ارسال کنید", $backuser, 'HTML');
    step('gettypeextratime', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettypeextratime")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f", "all"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidtypeagent'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $typepanel = select("marzban_panel", "*", "name_panel", $userdata['namepanel'], "select");
    outtypepanel($typepanel['type'], $textbotlang['users']['Extra_volume']['ChangedPrice']);
    $eextraprice = json_decode($typepanel['priceextratime'], true);
    if ($text == 'all') {
        $eextraprice["f"] = $userdata['price'];
        $eextraprice["n"] = $userdata['price'];
        $eextraprice["n2"] = $userdata['price'];
    } else {
        $eextraprice[$text] = $userdata['price'];
    }
    $eextraprice = json_encode($eextraprice);
    update("marzban_panel", "priceextratime", $eextraprice, "name_panel", $userdata['namepanel']);
    update("user", "Processing_value", $userdata['namepanel'], "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⏳ قیمت زمان دلخواه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 قیمت زمان دلخواه برای این پنل را ارسال نمایید.", $backadmin, 'HTML');
    step('GetPriceExtratime', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GetPriceExtratime")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    savedata("clear", "namepanel", $user['Processing_value']);
    savedata("save", "price", $text);
    sendmessage($from_id, $textbotlang['users']['Extra_volume']['gettypeextra'] . "\n" . "⚠️ در صورتی که می خواهید قیمت برای تمامی گروه های کاربری تنظیم شود متن <code>all</code> را ارسال کنید", $backuser, 'HTML');
    step('gettypeextratimecustom', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettypeextratimecustom")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f", "all"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidtypeagent'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $typepanel = select("marzban_panel", "*", "name_panel", $userdata['namepanel'], "select");
    outtypepanel($typepanel['type'], $textbotlang['users']['Extra_volume']['ChangedPrice']);
    $eextraprice = json_decode($typepanel['pricecustomtime'], true);
    if ($text == 'all') {
        $eextraprice["f"] = $userdata['price'];
        $eextraprice["n"] = $userdata['price'];
        $eextraprice["n2"] = $userdata['price'];
    } else {
        $eextraprice[$text] = $userdata['price'];
    }
    $eextraprice = json_encode($eextraprice);
    update("marzban_panel", "pricecustomtime", $eextraprice, "name_panel", $userdata['namepanel']);
    update("user", "Processing_value", $userdata['namepanel'], "id", $from_id);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🔒 نمایش کارت به کارت پس از اولین پرداخت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $paymentverify = select("PaySetting", "ValuePay", "NamePay", "checkpaycartfirst", "select")['ValuePay'];
    $keyboardverify = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $paymentverify, 'callback_data' => $paymentverify],
            ],
        ]
    ]);
    sendmessage($from_id, "📌 با روشن کردن این قابلیت پس از اولین پرداخت کاربر درگاه کارت به کارت برای کاربر فعال می شود", $keyboardverify, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "onpayverify")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", "offpayverify", "NamePay", "checkpaycartfirst");
    $paymentverify = select("PaySetting", "ValuePay", "NamePay", "checkpaycartfirst", "select")['ValuePay'];
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

