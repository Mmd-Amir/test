<?php
rf_set_module('admin/routes/08_step_addchannelid__step_get_limit__step_get_agent.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "addchannelid")) {
    $rf_admin_handled = true;

    $outputcheck = sendmessage($text, $textbotlang['Admin']['Channel']['TestChannel'], null, 'HTML');
    if (empty($outputcheck['ok'])) {
        $errorDescription = 'نامشخص';
        if (is_array($outputcheck) && isset($outputcheck['description'])) {
            $errorDescription = $outputcheck['description'];
        } elseif (is_string($outputcheck) && $outputcheck !== '') {
            $errorDescription = $outputcheck;
        }
        $texterror = "❌ اتصال به گروه با موفقیت انجام نشد

خطای دریافتی :  {$errorDescription}";
        sendmessage($from_id, $texterror, null, 'HTML');
        return;
    }

    if ($outputcheck['result']['chat']['is_forum'] == false) {
        $texterror = "❌ گروه انتخاب شده درحالت انجمن نیست از تنظیمات گروه حالت تاپیک گروه را روشن کرده سپس آیدی عددی گروه را مجددا تنظیم نمایید";
        sendmessage($from_id, $texterror, null, 'HTML');
        return;
    }

    // --- جدید: ریست کردن تاپیک‌های پورسانت/شبانه/اطلاع‌رسانی/بکاپ برای گروه جدید ---
    $resetReports = ['porsantreport', 'reportnight', 'reportcron', 'backupfile'];
    foreach ($resetReports as $reportKey) {
        update("topicid", "idreport", 0, "report", $reportKey);
    }

    // --- ساخت تاپیک‌ها با فاصله‌ی ۵ ثانیه ---

    // 🛍 گزارش های خرید
    $createForumTopic = telegram('createForumTopic', [
        'chat_id' => $text,
        'name'   => "🛍 گزارش های خرید"
    ]);
    if (!$createForumTopic['ok']) {
        $texterror = "❌ ربات ادمین گروه نیست";
        sendmessage($from_id, $texterror, null, 'HTML');
        return;
    }
    if ($buyreport != $createForumTopic['result']['message_thread_id']) {
        update("topicid", "idreport", $createForumTopic['result']['message_thread_id'], "report", "buyreport");
    }
    sleep(5);

    $createForumTopic = telegram('createForumTopic', [
        'chat_id' => $text,
        'name'   => "📌 گزارش خرید خدمات"
    ]);
    if (!$createForumTopic['ok']) {
        $texterror = "❌ ربات ادمین گروه نیست";
        sendmessage($from_id, $texterror, null, 'HTML');
        return;
    }
    if ($otherservice != $createForumTopic['result']['message_thread_id']) {
        update("topicid", "idreport", $createForumTopic['result']['message_thread_id'], "report", "otherservice");
    }
    sleep(5);

    $createForumTopic = telegram('createForumTopic', [
        'chat_id' => $text,
        'name'   => "🔑 گزارش اکانت تست"
    ]);
    if (!$createForumTopic['ok']) {
        $texterror = "❌ ربات ادمین گروه نیست";
        sendmessage($from_id, $texterror, null, 'HTML');
        return;
    }
    if ($reporttest != $createForumTopic['result']['message_thread_id']) {
        update("topicid", "idreport", $createForumTopic['result']['message_thread_id'], "report", "reporttest");
    }
    sleep(5);

    $createForumTopic = telegram('createForumTopic', [
        'chat_id' => $text,
        'name'   => "⚙️ سایر گزارشات"
    ]);
    if (!$createForumTopic['ok']) {
        $texterror = "❌ ربات ادمین گروه نیست";
        sendmessage($from_id, $texterror, null, 'HTML');
        return;
    }
    if ($errorreport != $createForumTopic['result']['message_thread_id']) {
        update("topicid", "idreport", $createForumTopic['result']['message_thread_id'], "report", "otherreport");
    }
    sleep(5);

    $createForumTopic = telegram('createForumTopic', [
        'chat_id' => $text,
        'name'   => "❌ گزارش خطا ها"
    ]);
    if (!$createForumTopic['ok']) {
        $texterror = "❌ ربات ادمین گروه نیست";
        sendmessage($from_id, $texterror, null, 'HTML');
        return;
    }
    if ($errorreport != $createForumTopic['result']['message_thread_id']) {
        update("topicid", "idreport", $createForumTopic['result']['message_thread_id'], "report", "errorreport");
    }
    sleep(5);

    $createForumTopic = telegram('createForumTopic', [
        'chat_id' => $text,
        'name'   => "💰 گزارش مالی"
    ]);
    if (!$createForumTopic['ok']) {
        $texterror = "❌ ربات ادمین گروه نیست";
        sendmessage($from_id, $texterror, null, 'HTML');
        return;
    }
    if ($paymentreports != $createForumTopic['result']['message_thread_id']) {
        update("topicid", "idreport", $createForumTopic['result']['message_thread_id'], "report", "paymentreport");
    }


    sendmessage($from_id, $textbotlang['Admin']['Channel']['SetChannelReport'], $setting_panel, 'HTML');
    update("setting", "Channel_Report", $text);
    step('home', $from_id);

    return;
}

if (!$rf_admin_handled && ($text == "🏬 تنظیمات فروشگاه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $shopkeyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🛍 اضافه کردن محصول" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $locationproduct = select("marzban_panel", "*", null, null, "count");
    if ($locationproduct == 0) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpaneladmin'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Product']['AddProductStepOne'], $backadmin, 'HTML');
    step('get_limit', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_limit")) {
    $rf_admin_handled = true;

    if (strlen($text) > 150) {
        sendmessage($from_id, "❌ نام محصول باید کمتر از 150 کاراکتر باشد", $backadmin, 'HTML');
        return;
    }
    if (in_array($text, $name_product)) {
        sendmessage($from_id, "❌ محصول با نام $text وجود دارد", $backadmin, 'HTML');
        return;
    }
    savedata("clear", "name_product", $text);
    sendmessage($from_id, $textbotlang['Admin']['agent']['setagentproduct'], $backadmin, 'HTML');
    step('get_agent', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_agent")) {
    $rf_admin_handled = true;

    $agent = ["n", "f", "n2"];
    if (!in_array($text, $agent)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    savedata("save", "agent", $text);
    sendmessage($from_id, $textbotlang['Admin']['Product']['Service_location'], $json_list_marzban_panel, 'HTML');
    step('get_location', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_location")) {
    $rf_admin_handled = true;

    $marzban_list[] = '/all';
    if (!in_array($text, $marzban_list)) {
        sendmessage($from_id, "❌ پنل انتخابی اشتباه است", null, 'HTML');
        return;
    }
    savedata("save", "Location", $text);
    if ($setting['statuscategorygenral'] == "oncategorys") {
        sendmessage($from_id, "📌 نام دسته بندی خود را ارسال نمایید.", KeyboardCategoryadmin(), 'HTML');
        step("getcategory", $from_id);
        return;
    }
    $panel = $text === '/all' ? null : select("marzban_panel", "*", "name_panel", $text, "select");
    if ($text !== '/all' && !is_array($panel)) {
        sendmessage($from_id, "❌ پنل انتخابی در دسترس نیست", $backadmin, 'HTML');
        step('home', $from_id);
        return;
    }
    if (is_array($panel) && ($panel['type'] ?? '') == "Manualsale") {
        savedata("save", "Service_time", "0");
        savedata("save", "Volume_constraint", "0");
        sendmessage($from_id, $textbotlang['Admin']['Product']['GetPrice'], $backadmin, 'HTML');
        step('gettimereset', $from_id);
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Product']['GetLimit'], $backadmin, 'HTML');
    step('get_time', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcategory")) {
    $rf_admin_handled = true;

    $category = select("category", "*", "remark", $text, "count");
    if ($category == 0) {
        sendmessage($from_id, "❌ دسته بندی انتخاب شده وجود ندارد از بخش پلن ها > اضافه کردن دسته بندی دسته بندی خود را اضافه کنید سپس محصول را اضافه نمایید.", KeyboardCategoryadmin(), 'HTML');
        return;
    }
    savedata("save", "category", $text);
    $userdata = json_decode($user['Processing_value'], true);
    $panel = $userdata['Location'] === '/all' ? null : select("marzban_panel", "*", "name_panel", $userdata['Location'], "select");
    if ($userdata['Location'] !== '/all' && !is_array($panel)) {
        sendmessage($from_id, "❌ پنل انتخابی در دسترس نیست", $backadmin, 'HTML');
        step('home', $from_id);
        return;
    }
    if (is_array($panel) && ($panel['type'] ?? '') == "Manualsale") {
        savedata("save", "Service_time", "0");
        savedata("save", "Volume_constraint", "0");
        sendmessage($from_id, $textbotlang['Admin']['Product']['GetPrice'], $backadmin, 'HTML');
        step('gettimereset', $from_id);
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Product']['GetLimit'], $backadmin, 'HTML');
    step('get_time', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_time")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    savedata("save", "Volume_constraint", $text);
    sendmessage($from_id, $textbotlang['Admin']['Product']['GettIime'], $backadmin, 'HTML');
    step('get_price', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_price")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    savedata("save", "Service_time", $text);
    sendmessage($from_id, $textbotlang['Admin']['Product']['GetPrice'], $backadmin, 'HTML');
    step('gettimereset', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettimereset")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidPrice'], $backadmin, 'HTML');
        return;
    }
    savedata("save", "price_product", $text);
    $userdata = json_decode($user['Processing_value'], true);
    $panel = $userdata['Location'] === '/all' ? null : select("marzban_panel", "*", "name_panel", $userdata['Location'], "select");
    if ($userdata['Location'] !== '/all' && !is_array($panel)) {
        sendmessage($from_id, "❌ پنل انتخابی در دسترس نیست", $backadmin, 'HTML');
        step('home', $from_id);
        return;
    }
    $panelType = is_array($panel) ? ($panel['type'] ?? '') : '';
    if ($panelType == "marzban" || $panelType == "marzneshin") {
        sendmessage($from_id, $textbotlang['Admin']['Product']['gettimereset'], $keyboardtimereset, 'HTML');
        step('getnote', $from_id);
        return;
    }
    savedata("save", "data_limit_reset", "no_reset");
    sendmessage($from_id, " 🗒 یادداشت را برای محصول ارسال کنید. این یادداشت در پیش فاکتور کاربر نشان داده می شود.", $backadmin, 'HTML');
    step('endstep', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnote")) {
    $rf_admin_handled = true;

    savedata("save", "data_limit_reset", $text);
    sendmessage($from_id, " 🗒 یادداشت را برای محصول ارسال کنید.این یادداشت در پیش فاکتور کاربر نشان داده می شود.", $backadmin, 'HTML');
    step('endstep', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "endstep")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    $randomString = bin2hex(random_bytes(2));
    $varhide_panel = "{}";
    if (!isset($userdata['category']))
        $userdata['category'] = null;
    $stmt = $pdo->prepare("INSERT IGNORE INTO product (name_product,code_product,price_product,Volume_constraint,Service_time,Location,agent,data_limit_reset,note,category,hide_panel,one_buy_status) VALUES (:name_product,:code_product,:price_product,:Volume_constraint,:Service_time,:Location,:agent,:data_limit_reset,:note,:category,:hide_panel,'0')");
    $stmt->bindParam(':name_product', $userdata['name_product']);
    $stmt->bindParam(':code_product', $randomString);
    $stmt->bindParam(':price_product', $userdata['price_product']);
    $stmt->bindParam(':Volume_constraint', $userdata['Volume_constraint']);
    $stmt->bindParam(':Service_time', $userdata['Service_time']);
    $stmt->bindParam(':Location', $userdata['Location']);
    $stmt->bindParam(':agent', $userdata['agent']);
    $stmt->bindParam(':data_limit_reset', $userdata['data_limit_reset']);
    $stmt->bindParam(':category', $userdata['category'], PDO::PARAM_STR);
    $stmt->bindParam(':note', $text, PDO::PARAM_STR);
    $stmt->bindParam(':hide_panel', $varhide_panel, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['SaveProduct'], $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "👨‍🔧 بخش ادمین" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $list_admin = select("admin", "*", null, null, "fetchAll");
    $keyboardadmin = ['inline_keyboard' => []];
    foreach ($list_admin as $admin) {
        $adminId = isset($admin['id_admin']) ? trim($admin['id_admin']) : '';
        if ($adminId === '') {
            continue;
        }
        $keyboardadmin['inline_keyboard'][] = [
            ['text' => "❌", 'callback_data' => "removeadmin_" . $adminId],
            ['text' => $adminId, 'callback_data' => "adminlist"],
        ];
    }
    $keyboardadmin['inline_keyboard'][] = [
        ['text' => "👨‍💻 اضافه کردن ادمین", 'callback_data' => "addnewadmin"],
    ];
    $keyboardadmin = json_encode($keyboardadmin);
    sendmessage($from_id, "📌 در بخش زیر می توانید لیست ادمین ها را مشاهده کنید همچنین با زدن دکمه ضربدر می توانید یک ادمین را حذف کنید", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "⚙️ تنظیمات عمومی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $setting_panel, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🤙 بخش پشتیبانی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $supportcenter, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/Confirm_pay_(\w+)/', $datain, $dataget) && ($adminrulecheck['rule'] == "administrator" || $adminrulecheck['rule'] == "Seller"))) {
    $rf_admin_handled = true;

    $order_id = $dataget[1];
    $Payment_report = select("Payment_report", "*", "id_order", $order_id, "select");
    $Confirm_pay = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "✅ تایید شده", 'callback_data' => "confirmpaid"],
            ],
            [
                ['text' => "⚙️ مدیریت کاربر", 'callback_data' => "manageuser_" . $Payment_report['id_user']],
            ]
        ]
    ]);
    if ($Payment_report == false) {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "تراکنش حذف شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $sql = "SELECT * FROM Payment_report WHERE id_user = '{$Payment_report['id_user']}' AND payment_Status != 'paid' AND payment_Status != 'Unpaid' AND payment_Status != 'expire' AND payment_Status != 'reject' AND  (id_invoice  LIKE CONCAT('%','getconfigafterpay', '%') OR id_invoice  LIKE CONCAT('%','getextenduser', '%') OR id_invoice  LIKE CONCAT('%','getextravolumeuser', '%') OR id_invoice  LIKE CONCAT('%','getextratimeuser', '%'))";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $countpay = $stmt->rowCount();
    $typepay = explode('|', $Payment_report['id_invoice']);
    if ($countpay > 0 and !in_array($typepay[0], ['getconfigafterpay', 'getextenduser', 'getextravolumeuser', 'getextratimeuser'])) {
        sendmessage($from_id, "⚠️ برای تأیید درخواست‌های کاربر، ابتدا رسیدهای خرید یا تمدید اشتراک را بررسی و تأیید کنید. سپس رسید شارژ کیف پول را تأیید کنید. ", null, 'HTML');
        return;
    }
    $format_price_cart = number_format($Payment_report['price']);
    $Balance_id = select("user", "*", "id", $Payment_report['id_user'], "select");
    if ($Payment_report['payment_Status'] == "paid" || $Payment_report['payment_Status'] == "reject") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['Admin']['Payment']['reviewedpayment'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        $textconfrom = "✅. پرداخت توسط ادمین دیگری تایید شده
👤 شناسه کاربر: <code>{$Balance_id['id']}</code>
🛒 کد پیگیری پرداخت: {$Payment_report['id_order']}
⚜️ نام کاربری: @{$Balance_id['username']}
💎 موجودی بعد از تایید : {$Balance_id['Balance']}
💸 مبلغ پرداختی: $format_price_cart تومان
";
        Editmessagetext($from_id, $message_id, $textconfrom, $Confirm_pay);
        return;
    }
    DirectPayment($order_id);
    $pricecashback = select("PaySetting", "ValuePay", "NamePay", "chashbackcart", "select")['ValuePay'];
    $Balance_id = select("user", "*", "id", $Payment_report['id_user'], "select");
    if ($pricecashback != "0") {
        $result = ($Payment_report['price'] * $pricecashback) / 100;
        $Balance_confrim = intval($Balance_id['Balance']) + $result;
        update("user", "Balance", $Balance_confrim, "id", $Balance_id['id']);
        $pricecashback = number_format($pricecashback);
        $text_report = "🎁 کاربر عزیز مبلغ $result تومان به عنوان هدیه واریز به حساب شما واریز گردید.";
        sendmessage($Balance_id['id'], $text_report, null, 'HTML');
    }
    $Payment_report['price'] = number_format($Payment_report['price']);
    $text_report = "📣 یک ادمین رسید پرداخت  را تایید کرد.
        
اطلاعات :
💸 روش پرداخت : {$Payment_report['Payment_Method']}
👤آیدی عددی  ادمین تایید کننده : $from_id
💰 مبلغ پرداخت : {$Payment_report['price']}
👤 ایدی عددی کاربر : <code>{$Payment_report['id_user']}</code>
👤 نام کاربری کاربر : @{$Balance_id['username']} 
        کد پیگیری پرداحت : $order_id";
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $paymentreports,
            'text' => $text_report,
            'parse_mode' => "HTML"
        ]);
    }
    update("Payment_report", "payment_Status", "paid", "id_order", $Payment_report['id_order']);
    update("user", "Processing_value_one", "none", "id", $Balance_id['id']);
    update("user", "Processing_value_tow", "none", "id", $Balance_id['id']);
    update("user", "Processing_value_four", "none", "id", $Balance_id['id']);
    return;
}

