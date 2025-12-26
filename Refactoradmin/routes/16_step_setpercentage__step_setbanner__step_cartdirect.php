<?php
rf_set_module('admin/routes/16_step_setpercentage__step_setbanner__step_cartdirect.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($text == "🧮 تنظیم درصد زیرمجموعه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['affiliates']['setpercentage'], $backadmin, 'HTML');
    step('setpercentage', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "setpercentage")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, "درصد نامعتبر", $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['users']['affiliates']['changedpercentage'], $affiliates, 'HTML');
    update("setting", "affiliatespercentage", $text);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🏞 تنظیم بنر زیرمجموعه گیری")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['affiliates']['banner'], $backadmin, 'HTML');
    step('setbanner', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "setbanner")) {
    $rf_admin_handled = true;

    if (!$photo) {
        sendmessage($from_id, $textbotlang['users']['affiliates']['invalidbanner'], $backadmin, 'HTML');
        return;
    }
    update("affiliates", "id_media", $photoid);
    update("affiliates", "description", $caption);
    sendmessage($from_id, $textbotlang['users']['affiliates']['insertbanner'], $affiliates, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "👤 آیدی پشتیبانی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "CartDirect");
    $textcart = "📌 نام کاربری خود را بدون @ برای دریافت شماره کارت ارسال کنید\n\n{$PaySetting['ValuePay']}";
    sendmessage($from_id, $textcart, $backadmin, 'HTML');
    step('CartDirect', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "CartDirect")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingPayment']['CartDirect'], $CartManage, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "CartDirect");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "💳 درگاه آفلاین در پیوی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "Cartstatuspv")['ValuePay'];
    $card_Statuspv = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $PaySetting, 'callback_data' => $PaySetting],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['cardTitlepv'], $card_Statuspv, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "oncardpv" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", "offcardpv", "NamePay", "Cartstatuspv");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['cardStatusOffpv'], null);
    return;
}

if (!$rf_admin_handled && ($datain == "offcardpv" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", "oncardpv", "NamePay", "Cartstatuspv");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['cardStatusonpv'], null);
    return;
}

if (!$rf_admin_handled && (preg_match('/addbalamceuser_(\w+)/', $datain, $datagetr) && ($adminrulecheck['rule'] == "administrator" || $adminrulecheck['rule'] == "Seller"))) {
    $rf_admin_handled = true;

    $id_order = $datagetr[1];
    $Payment_report = select("Payment_report", "*", "id_order", $id_order, "select");
    update("user", "Processing_value", $id_order, "id", $from_id);
    if ($Payment_report['payment_Status'] == "paid" || $Payment_report['payment_Status'] == "reject") {
        $ff = telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['Admin']['Payment']['reviewedpayment'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    update("Payment_report", "payment_Status", "paid", "id_order", $id_order);

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['addbalanceuserdec'], $backadmin, 'html');
    step('addbalancemanual', $from_id);
    Editmessagetext($from_id, $message_id, $text_inline, null);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "addbalancemanual")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['AddBalanceUser'], $keyboardadmin, 'HTML');
    $Payment_report = select("Payment_report", "*", "id_order", $user['Processing_value'], "select");
    $Balance_user = select("user", "*", "id", $Payment_report['id_user'], "select");
    $Balance_add_user = $Balance_user['Balance'] + $text;
    $balanceusers = number_format($text, 0);
    update("user", "Balance", $Balance_add_user, "id", $Payment_report['id_user']);
    $textadd = "💎 کاربر عزیز مبلغ $balanceusers تومان به موجودی کیف پول تان اضافه گردید.";
    sendmessage($Payment_report['id_user'], $textadd, null, 'HTML');
    $text_report = "تایید رسید کارت به کارت و افزایش دستی موجودی توسط ادمین
        
آیدی عددی کاربر : {$Payment_report['id_user']}
نام کاربری کاربر : {$Balance_user['username']}
مبلغ تراکنش در فاکتور :  {$Payment_report['price']}
مبلغ تراکنش واریزی توسط ادمین : $text";
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $paymentreports,
            'text' => $text_report,
            'parse_mode' => "HTML"
        ]);
    }
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🎁 پورسانت بعد از خرید" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $marzbancommission = select("affiliates", "*", null, null, "select");
    $keyboardcommission = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbancommission['status_commission'], 'callback_data' => $marzbancommission['status_commission']],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['commission'], $keyboardcommission, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "oncommission")) {
    $rf_admin_handled = true;

    update("affiliates", "status_commission", "offcommission");
    $marzbancommission = select("affiliates", "*", null, null, "select");
    $keyboardcommission = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbancommission['status_commission'], 'callback_data' => $marzbancommission['status_commission']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['commissionStatusOff'], $keyboardcommission);
    return;
}

if (!$rf_admin_handled && ($datain == "offcommission")) {
    $rf_admin_handled = true;

    update("affiliates", "status_commission", "oncommission");
    $marzbancommission = select("affiliates", "*", null, null, "select");
    $keyboardcommission = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbancommission['status_commission'], 'callback_data' => $marzbancommission['status_commission']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['commissionStatuson'], $keyboardcommission);
    return;
}

if (!$rf_admin_handled && ($text == "🎁 هدیه استارت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $marzbanDiscountaffiliates = select("affiliates", "*", null, null, "select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanDiscountaffiliates['Discount'], 'callback_data' => $marzbanDiscountaffiliates['Discount']],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['Discountaffiliates'], $keyboardDiscountaffiliates, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "onDiscountaffiliates")) {
    $rf_admin_handled = true;

    update("affiliates", "Discount", "offDiscountaffiliates");
    $marzbanDiscountaffiliates = select("affiliates", "*", null, null, "select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanDiscountaffiliates['Discount'], 'callback_data' => $marzbanDiscountaffiliates['Discount']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['DiscountaffiliatesStatusOff'], $keyboardDiscountaffiliates);
    return;
}

if (!$rf_admin_handled && ($datain == "offDiscountaffiliates")) {
    $rf_admin_handled = true;

    update("affiliates", "Discount", "onDiscountaffiliates");
    $marzbanDiscountaffiliates = select("affiliates", "*", null, null, "select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanDiscountaffiliates['Discount'], 'callback_data' => $marzbanDiscountaffiliates['Discount']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['DiscountaffiliatesStatuson'], $keyboardDiscountaffiliates);
    return;
}

if (!$rf_admin_handled && ($text == "🌟 مبلغ هدیه استارت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['affiliates']['priceDiscount'], $backadmin, 'HTML');
    step('getdiscont', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getdiscont")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['affiliates']['changedpriceDiscount'], $affiliates, 'HTML');
    update("affiliates", "price_Discount", $text);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "mainbalanceaccount" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = json_decode(select("PaySetting", "ValuePay", "NamePay", "minbalance", "select")[$user['agent']], true);
    $textmin = "📌 حداقل مبلغی که می خواهید کاربر حساب خود را شارژ کند را تعیین کنید";
    sendmessage($from_id, $textmin, $backadmin, 'HTML');
    step('minbalance', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "minbalance")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    update("user", "Processing_value", $text, "id", $from_id);
    step('getagentbalancemin', $from_id);
    sendmessage($from_id, "📌حداقل موجودی برای کدام گروه کاربری باشید.
f
n
n2", $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getagentbalancemin")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f", "allusers"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['invalidagentcode'], $bakcadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    $balancemaax = json_decode(select("PaySetting", "ValuePay", "NamePay", "minbalance", "select")['ValuePay'], true);
    $balancemaax[$text] = $user['Processing_value'];
    $balancemaax = json_encode($balancemaax);
    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $keyboardadmin, 'HTML');
    update("PaySetting", "ValuePay", $balancemaax, "NamePay", "minbalance");
    return;
}

if (!$rf_admin_handled && ($datain == "maxbalanceaccount" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "maxbalance", "select");
    $textmax = "📌 حداکثر مبلغی که می خواهید کاربر حساب خود را شارژ کند را تعیین کنید";
    sendmessage($from_id, $textmax, $backadmin, 'HTML');
    step('maxbalance', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "walletaddress" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "walletaddress", "select");
    $currentWallet = $PaySetting['ValuePay'] ?? '';
    $texttronseller = "💼 لطفاً آدرس ولت ترون (TRC20) خود را ارسال کنید.\n\nولت فعلی شما: {$currentWallet}";
    sendmessage($from_id, $texttronseller, $backadmin, 'HTML');

    savedata('clear', 'walletaddress_origin', 'general');

    step('walletaddresssiranpay', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "maxbalance")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    update("user", "Processing_value", $text, "id", $from_id);
    step('getagentbalancemax', $from_id);
    sendmessage($from_id, "📌حداقل موجودی برای کدام گروه کاربری باشید.
f
n
n2", $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getagentbalancemax")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f", "allusers"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['invalidagentcode'], $bakcadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    $balancemaax = json_decode(select("PaySetting", "ValuePay", "NamePay", "maxbalance", "select")['ValuePay'], true);
    $balancemaax[$text] = $user['Processing_value'];
    $balancemaax = json_encode($balancemaax);
    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $keyboardadmin, 'HTML');
    update("PaySetting", "ValuePay", $balancemaax, "NamePay", "maxbalance");
    return;
}

if (!$rf_admin_handled && (preg_match('/removeagent_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_user = $dataget[1];
    telegram('sendmessage', [
        'chat_id' => $from_id,
        'text' => $textbotlang['Admin']['agent']['useragentremoved'],
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
    update("user", "agent", "f", "id", $id_user);
    update("user", "pricediscount", "0", "id", $id_user);
    update("user", "expire", null, "id", $id_user);
    $stmt = $pdo->prepare("DELETE FROM Requestagent WHERE id = '$id_user'");
    $stmt->execute();
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/addagent_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_user = $dataget[1];
    update("user", "Processing_value", $id_user, "id", $from_id);
    telegram('sendmessage', [
        'chat_id' => $from_id,
        'text' => $textbotlang['Admin']['agent']['gettypeagent'],
        'parse_mode' => "HTML",
        'reply_markup' => $backadmin,
        'reply_to_message_id' => $message_id,
    ]);
    step('gettypeagentoflist', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettypeagentoflist")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidtypeagent'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['agent']['useragented'], $keyboardadmin, 'HTML');
    update("user", "expire", null, "id", $user['Processing_value']);
    update("user", "agent", $text, "id", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/Percentlow_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_user = $dataget[1];
    update("user", "Processing_value", $id_user, "id", $from_id);
    telegram('sendmessage', [
        'chat_id' => $from_id,
        'text' => "📌 تعداد درصدی که میخواهید در صورتی که کاربر هرگونه خریدی انجام داده است تخفیفی دریافت کند را ارسال نمایید.",
        'reply_markup' => $backadmin,
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
    step('getpercentuser', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpercentuser")) {
    $rf_admin_handled = true;

    if (intval($text) > 100 || intval($text) < 0 || !ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $keyboardadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "تغییرات با موفقیت اعمال شد", $keyboardadmin, 'HTML');
    update("user", "pricediscount", $text, "id", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/maxbuyagent_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_user = $dataget[1];
    update("user", "Processing_value", $id_user, "id", $from_id);
    sendmessage($from_id, "📌 حداکثر مبلغی که کاربر می توانید موجودی  اش در زمان خرید منفی شود را ارسال نمایید
توجه : عدد بدون خط تیره یا نماد منفی باشد
در صورتی که می خواهید کاربر نامحدود خریداری کند عدد 0 ارسال کنید", $backadmin, 'HTML');
    step('getmaxbuyagent', $from_id);
    return;
}

