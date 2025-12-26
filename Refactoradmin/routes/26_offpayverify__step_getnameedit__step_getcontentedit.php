<?php
rf_set_module('admin/routes/26_offpayverify__step_getnameedit__step_getcontentedit.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($datain == "offpayverify")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", "onpayverify", "NamePay", "checkpaycartfirst");
    $paymentverify = select("PaySetting", "ValuePay", "NamePay", "checkpaycartfirst", "select")['ValuePay'];
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

if (!$rf_admin_handled && ($text == "✏️ ویرایش کانفیگ")) {
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
    sendmessage($from_id, "📌 نام کانفیگی که میخواهید ویرایش نمایید را ارسال کنید ", $json_list_manualconfig_list, 'HTML');
    step("getnameedit", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnameedit")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "یکی از گزینه های زیر را انتخاب کنید ", $configedit, 'HTML');
    step("home", $from_id);
    update("user", "Processing_value_one", $text, "id", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "مخشصات کانفیگ")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "محتوا جدید کانفیگ را ارسال کنید", $backadmin, 'HTML');
    step("getcontentedit", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcontentedit")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ ذخیره گردید.", $optionManualsale, 'HTML');
    update("manualsell", "contentrecord", $text, "namerecord", $user['Processing_value_one']);
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ افزایش گروهی قیمت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 محصولات کدام پنل میخواهید افزایش قیمت دهید؟
در صورتی که  موقع تعریف محصول /all زدید  اگر میخواید این دسته تغییر قیمت داشته باشد حتما باید /all ارسال شود", $json_list_marzban_panel, 'HTML');
    step("getaddpricepeoductloc", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getaddpricepeoductloc")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 قیمت برای کدام گروه کاربری اعمال شود 
f,n.n2", $backadmin, 'HTML');
    savedata("clear", "namepanel", $text);
    step("getagentaddpriceproduct", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getagentaddpriceproduct")) {
    $rf_admin_handled = true;

    $keyboard_type_price = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "درصدی", 'callback_data' => 'typeaddprice_percent'],
                ['text' => "ثابت", 'callback_data' => 'typeaddprice_static'],
            ],
        ]
    ]);
    sendmessage($from_id, "📌 مبلغ به صورت درصدی اضافه شود یا مبلغ ثابت", $keyboard_type_price, 'HTML');
    savedata("save", "agent", $text);
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/^typeaddprice_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $type = $dataget[1];
    deletemessage($from_id, $message_id);
    if ($type == "static") {
        sendmessage($from_id, "📌 مبلغی که میخواهید اعمال شود را ارسال نمایید", $backadmin, 'HTML');
    } else {
        sendmessage($from_id, "📌 درصدی که میخواهید اعمال شود را ارسال نمایید", $backadmin, 'HTML');
    }
    savedata("save", "type_price", $type);
    step("getaddpricepeoduct", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getaddpricepeoduct")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE Location = '{$userdata['namepanel']}' AND agent = '{$userdata['agent']}'");
    $stmt->execute();
    $product = $stmt->fetchAll();
    if ($product == false) {
        sendmessage($from_id, "❌ محصولی برای تغییر قیمت یافت نشد", $shopkeyboard, 'HTML');
        step("home", $from_id);
        return;
    }
    if ($userdata['type_price'] == "static") {
        $stmt = $pdo->prepare("UPDATE  product set price_product = price_product + :price WHERE Location = '{$userdata['namepanel']}' AND agent = '{$userdata['agent']}'");
        $stmt->bindParam(':price', $text, PDO::PARAM_STR);
    } else {
        $stmt = $pdo->prepare("UPDATE  product set price_product = price_product + (price_product * :price / 100)  WHERE Location = '{$userdata['namepanel']}' AND agent = '{$userdata['agent']}'");
        $stmt->bindParam(':price', $text, PDO::PARAM_STR);
    }
    $stmt->execute();
    sendmessage($from_id, "✅ مبلغ با موفقیت برای تمامی محصولات اعمال شد", $shopkeyboard, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ کاهش  گروهی قیمت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 محصولات کدام پنل میخواهید کاهش قیمت دهید؟
در صورتی که  موقع تعریف محصول /all زدید  اگر میخواید این دسته تغییر قیمت داشته باشد حتما باید /all ارسال شود", $json_list_marzban_panel, 'HTML');
    step("getlowpricepeoductloc", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getlowpricepeoductloc")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 قیمت برای کدام گروه کاربری اعمال شود 
f,n.n2", $backadmin, 'HTML');
    savedata("clear", "namepanel", $text);
    step("getkampricepeoductloc", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getkampricepeoductloc")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 مبلغی که میخواهید اعمال شود را ارسال نمایید", $backadmin, 'HTML');
    savedata("save", "agent", $text);
    step("getkampricepeoduct", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getkampricepeoduct")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $stmt = $pdo->prepare("SELECT * FROM product WHERE Location = '{$userdata['namepanel']}' AND agent = '{$userdata['agent']}'");
    $stmt->execute();
    $product = $stmt->fetchAll();
    if ($product == false) {
        sendmessage($from_id, "❌ محصولی برای تغییر قیمت یافت نشد", $shopkeyboard, 'HTML');
        return;
    }
    foreach ($product as $products) {
        $result = $products['price_product'] - intval($text);
        update("product", "price_product", round($result), "code_product", $products['code_product']);
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت برای تمامی محصولات اعمال شد", $shopkeyboard, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ کارت به کارت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmaincart", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmaincart")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $CartManage, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalancecart");
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ کارت به کارت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmaxcart", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmaxcart")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $CartManage, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalancecart");
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ plisio")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmainplisio", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmainplisio")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $NowPaymentsManage, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalanceplisio");
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ plisio")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmaxplisio", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmaxplisio")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $NowPaymentsManage, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalanceplisio");
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ رمزارز آفلاین")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmaindigitaltron", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmaindigitaltron")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $tronnowpayments, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalancedigitaltron");
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ رمزارز آفلاین")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmaxdigitaltron", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmaxdigitaltron")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $tronnowpayments, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalancedigitaltron");
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ ارزی ریالی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmainiranpay1", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmainiranpay1")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $Swapinokey, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalanceiranpay1");
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ ارزی ریالی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmaaxiranpay1", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmaaxiranpay1")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $Swapinokey, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalanceiranpay1");
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ ارزی ریالی دوم")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmainiranpay2", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmainiranpay2")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $trnado, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalanceiranpay2");
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ ارزی ریالی دوم")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmaaxiranpay2", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmaaxiranpay2")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $Swapinokey, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalanceiranpay2");
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ آقای پرداخت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmainaqayepardakht", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmainaqayepardakht")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $aqayepardakht, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalanceaqayepardakht");
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ آقای پرداخت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmaaxaqayepardakht", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmaaxaqayepardakht")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $aqayepardakht, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalanceaqayepardakht");
    return;
}

