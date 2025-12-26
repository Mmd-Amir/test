<?php
rf_set_module('admin/routes/30_step_getonelotary3__gradonhshans__step_getpricewheel.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "getonelotary3")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ جایزه با موفقیت تنظیم شد", $lottery, 'HTML');
    step("home", $from_id);
    $data = json_decode($setting['Lottery_prize'], true);
    $data['theree'] = $text;
    $data = json_encode($data, true);
    update("setting", "Lottery_prize", $data, null, null);
    return;
}

if (!$rf_admin_handled && ($datain == "gradonhshans")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $wheelkeyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🎲 مبلغ برنده شدن کاربر")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 مقدار مبلغی که می خواهید حساب کاربر شارژ شود را ارسال نمایید.", $backadmin, 'HTML');
    step("getpricewheel", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpricewheel")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ جایزه با موفقیت تنظیم شد", $wheelkeyboard, 'HTML');
    step("home", $from_id);
    update("setting", "wheelـluck_price", $text, null, null);
    return;
}

if (!$rf_admin_handled && ($text == "💵 رسید های تایید نشده")) {
    $rf_admin_handled = true;

    $sql = "SELECT * FROM Payment_report WHERE Payment_Method = 'cart to cart' AND payment_Status = 'waiting'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $list_payment = $stmt->fetchAll();
    $list_payment_count = $stmt->rowCount();
    if ($list_payment_count == 0) {
        sendmessage($from_id, "❌ هیچ پرداخت تایید نشده ای ندارید.", null, 'HTML');
        return;
    }
    $list_pay = ['inline_keyboard' => []];
    foreach ($list_payment as $payment) {
        $list_pay['inline_keyboard'][] = [
            ['text' => $payment['id_user'], 'callback_data' => "checkpay"]
        ];
        $list_pay['inline_keyboard'][] = [
            ['text' => "✅", 'callback_data' => "Confirm_pay_{$payment['id_order']}"],
            ['text' => "❌", 'callback_data' => "reject_pay_{$payment['id_order']}"],
            ['text' => "📝", 'callback_data' => "showinfopay_{$payment['id_order']}"],
            ['text' => "🗑", 'callback_data' => "removeresid_{$payment['id_order']}"],
        ];
        $list_pay['inline_keyboard'][] = [
            ['text' => "💸💸💸💸💸💸💸💸💸", 'callback_data' => "checkpay"]
        ];
    }
    $list_pay['inline_keyboard'][] = [
        ['text' => "❌ حذف همه رسید ها", 'callback_data' => "removeresid"]
    ];
    $list_pay_json = json_encode($list_pay, JSON_UNESCAPED_UNICODE);
    if ($list_pay_json === false) {
        error_log('Failed to encode pending receipts keyboard: ' . json_last_error_msg());
        $list_pay_json = json_encode(['inline_keyboard' => []], JSON_UNESCAPED_UNICODE);
    }
    sendmessage($from_id, "📌 پرداخت های تایید نشده کارت به کارت
در این بخش میتوانید پرداخت های تایید نشده مشاهده و تایید یا رد نمایید.
❌ : رد کردن پرداخت
✅ : تایید پرداخت
📝 مشخصات پرداخت
🗑 : حذف رسید بدون اطلاع کاربر", $list_pay_json, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "removeresid")) {
    $rf_admin_handled = true;

    deletemessage($from_id, $message_id);
    sendmessage($from_id, "✅  تمامی رسید ها با موفقیت حذف شدند ", null, 'HTML');
    $sql = "UPDATE Payment_report SET payment_Status = 'reject',dec_not_confirmed = 'remove_all' WHERE Payment_Method = 'cart to cart' AND payment_Status = 'waiting'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return;
}

if (!$rf_admin_handled && (preg_match('/showinfopay_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $idorder = $dataget[1];
    $paymentUser = select("Payment_report", "*", "id_order", $idorder, "select");
    if ($paymentUser == false) {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "تراکنش حذف شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    $text_order = "🛒 شماره پرداخت  :  <code>{$paymentUser['id_order']}</code>
🙍‍♂️ شناسه کاربر : <code>{$paymentUser['id_user']}</code>
💰 مبلغ پرداختی : {$paymentUser['price']} تومان
⚜️ وضعیت پرداخت : {$paymentUser['payment_Status']}
⭕️ روش پرداخت : {$paymentUser['Payment_Method']} 
📆 تاریخ خرید :  {$paymentUser['time']}";
    sendmessage($from_id, $text_order, null, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🎛 تنظیم اینباند")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در صورتی که پنل مرزبان  یا مرزنشین هستید یک نام کاربری کانفیگ از پنل کپی و ارسال نمایید در غیراینصورت برای پنل های ثنایی و علیرضا شناسه اینباند را ارسال نمایید", $backadmin, 'HTML');
    step("getdatainboundproduct", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getdatainboundproduct")) {
    $rf_admin_handled = true;

    $marzban_list_get = select("marzban_panel", "*", "code_panel", $user['Processing_value_one']);
    $datainbound = "";
    if ($marzban_list_get['type'] == "marzban") {
        $DataUserOut = getuser($text, $marzban_list_get['name_panel']);
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
        $stmt = $pdo->prepare("UPDATE product SET proxies = :proxies WHERE id = :name_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
        $proxies_json = json_encode($DataUserOut['proxies']);
        $stmt->bindParam(':proxies', $proxies_json);
        $stmt->bindParam(':name_product', $user['Processing_value']);
        $stmt->bindParam(':Location', $marzban_list_get['name_panel']);
        $stmt->bindParam(':agent', $user['Processing_value_tow']);
        $stmt->execute();
        $datainbound = json_encode($DataUserOut['inbounds']);
    } elseif ($marzban_list_get['type'] == "marzneshin") {
        $userdata = json_decode(getuserm($text, $marzban_list_get['name_panel'])['body'], true);
        if (isset($userdata['detail']) and $userdata['detail'] == "User not found") {
            sendmessage($from_id, "کاربر در پنل وجود ندارد", null, 'HTML');
            return;
        }
        $datainbound = json_encode($userdata['service_ids'], true);
    } elseif ($marzban_list_get['type'] == "x-ui_single" || $marzban_list_get['type'] == "alireza_single") {
        $datainbound = $text;
    } elseif ($marzban_list_get['type'] == "s_ui") {
        $data = GetClientsS_UI($text, $panel['name_panel']);
        if (count($data) == 0) {
            sendmessage($from_id, "❌ یوزر در پنل وجود ندارد.", $options_ui, 'HTML');
            return;
        }
        $servies = [];
        foreach ($data['inbounds'] as $service) {
            $servies[] = $service;
        }
        $datainbound = json_encode($servies);
    } elseif ($marzban_list_get['type'] == "ibsng" || $marzban_list_get['type'] == "mikrotik") {
        $datainbound = $text;
    } else {
        sendmessage($from_id, "❌ برای این پنل قابلیت تعریف اینباند وجود ندارد", $shopkeyboard, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("UPDATE product SET inbounds = :inbounds WHERE id = :name_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':inbounds', $datainbound);
    $stmt->bindParam(':name_product', $user['Processing_value']);
    $stmt->bindParam(':Location', $marzban_list_get['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    sendmessage($from_id, "✅محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "iploginset")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 جهت ورود به پنل تحت وب نیاز است حتما یک آیپی ثابت ثبت کنید تا ورود را با آن آیپی انجام دهید  لطفا آیپی خود را ارسال نمایید", $shopkeyboard, 'HTML');
    step("getiplogin", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getiplogin")) {
    $rf_admin_handled = true;

    update("setting", "iplogin", $text, null, null);
    step("home", $from_id);
    sendmessage($from_id, "✅ آیپی با موفقیت تنظیم شد", $shopkeyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/extendadmin_(\w+)/', $datain, $dataget) || strpos($text, "/extend ") !== false)) {
    $rf_admin_handled = true;

    if ($text[0] == "/") {
        $usernameconfig = explode(" ", $text)[1];
        $id_invoice = select("invoice", "id_invoice", "username", $usernameconfig, 'select');
        if ($id_invoice == false) {
            sendmessage($from_id, "❌ کاربر وجو ندارد.", null, 'HTML');
            return;
        }
        $id_invoice = $id_invoice['id_invoice'];
    } else {
        $id_invoice = $dataget[1];
    }
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    if ($nameloc == false) {
        sendmessage($from_id, "❌ تمدید با خطا مواجه گردید مراحل تمدید را مجددا انجام دهید.", null, 'HTML');
        return;
    }
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $nameloc['username']);
    if ($DataUserOut['status'] == "Unsuccessful") {
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], null, 'html');
        return;
    }
    update("user", "Processing_value_one", $nameloc['id_invoice'], "id", $from_id);
    savedata("clear", "id_invoice", $nameloc['id_invoice']);
    $textcustom = "📌 حجم درخواستی خود را ارسال کنید.";
    sendmessage($from_id, $textcustom, $backuser, 'html');
    step('gettimecustomvolomforextendadmin', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettimecustomvolomforextendadmin")) {
    $rf_admin_handled = true;

    $userdate = json_decode($user['Processing_value'], true);
    $nameloc = select("invoice", "*", "id_invoice", $userdate['id_invoice'], "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backuser, 'HTML');
        return;
    }
    savedata("save", "volume", $text);
    $textcustom = "⌛️ زمان سرویس خود را انتخاب نمایید ";
    sendmessage($from_id, $textcustom, $backuser, 'html');
    step('getvolumecustomuserforextendadmin', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getvolumecustomuserforextendadmin")) {
    $rf_admin_handled = true;

    $userdate = json_decode($user['Processing_value'], true);
    $nameloc = select("invoice", "*", "id_invoice", $userdate['id_invoice'], "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidtime'], $backuser, 'HTML');
        return;
    }
    $prodcut['name_product'] = $nameloc['name_product'];
    $prodcut['note'] = "";
    $prodcut['price_product'] = 0;
    $prodcut['Service_time'] = $text;
    $prodcut['Volume_constraint'] = $userdate['volume'];
    update("invoice", "name_product", $prodcut['name_product'], "id_invoice", $userdate['id_invoice']);
    update("invoice", "price_product", $prodcut['price_product'], "id_invoice", $userdate['id_invoice']);
    update("invoice", "Volume", $prodcut['Volume_constraint'], "id_invoice", $userdate['id_invoice']);
    update("invoice", "Service_time", $prodcut['Service_time'], "id_invoice", $userdate['id_invoice']);
    step("home", $from_id);
    $keyboardextend = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['extend']['confirm'], 'callback_data' => "confirmserivceadmin-" . $nameloc['id_invoice']],
            ],
            [
                ['text' => "🏠 بازگشت به منوی اصلی", 'callback_data' => "backuser"]
            ]
        ]
    ]);
    $textextend = "📜 فاکتور تمدید شما برای نام کاربری {$nameloc['username']} ایجاد شد.
        
🛍 نام محصول :{$prodcut['name_product']}
⏱ مدت زمان تمدید :{$prodcut['Service_time']} روز
🔋 حجم تمدید :{$prodcut['Volume_constraint']} گیگ
✍️ توضیحات : {$prodcut['note']}
✅ برای تایید و تمدید سرویس روی دکمه زیر کلیک کنید";
    if ($user['step'] == "getvolumecustomuserforextendadmin") {
        sendmessage($from_id, $textextend, $keyboardextend, 'HTML');
    } else {
        Editmessagetext($from_id, $message_id, $textextend, $keyboardextend);
    }
    return;
}

if (!$rf_admin_handled && (preg_match('/^confirmserivceadmin-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    Editmessagetext($from_id, $message_id, $text_inline, json_encode(['inline_keyboard' => []]));
    $id_invoice = $dataget[1];
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $prodcut['code_product'] = "custom_volume";
    $prodcut['name_product'] = $nameloc['name_product'];
    $prodcut['price_product'] = 0;
    $prodcut['Service_time'] = $nameloc['Service_time'];
    $prodcut['Volume_constraint'] = $nameloc['Volume'];
    if ($prodcut == false || !in_array($nameloc['Status'], ['active', 'end_of_time', 'end_of_volume', 'sendedwarn', 'send_on_hold'])) {
        sendmessage($from_id, "❌ تمدید با خطا مواجه گردید مراحل تمدید را مجددا انجام دهید.", null, 'HTML');
        return;
    }
    deletemessage($from_id, $message_id);
    $extend = $ManagePanel->extend($marzban_list_get['Methodextend'], $prodcut['Volume_constraint'], $prodcut['Service_time'], $nameloc['username'], $prodcut['code_product'], $marzban_list_get['code_panel']);
    if ($extend['status'] == false) {
        $extend['msg'] = json_encode($extend['msg']);
        $textreports = "
        خطای تمدید سرویس
نام پنل : {$marzban_list_get['name_panel']}
نام کاربری سرویس : {$nameloc['username']}
دلیل خطا : {$extend['msg']}";
        sendmessage($from_id, "❌خطایی در تمدید سرویس رخ داده با پشتیبانی در ارتباط باشید", null, 'HTML');
        if (strlen($setting['Channel_Report']) > 0) {
            telegram('sendmessage', [
                'chat_id' => $setting['Channel_Report'],
                'message_thread_id' => $errorreport,
                'text' => $textreports,
                'parse_mode' => "HTML"
            ]);
        }
        return;
    }
    $stmt = $pdo->prepare("INSERT IGNORE INTO service_other (id_user, username, value, type, time, price, output) VALUES (:id_user, :username, :value, :type, :time, :price, :output)");
    $dateacc = date('Y/m/d H:i:s');
    $value = $prodcut['Volume_constraint'] . "_" . $prodcut['Service_time'];
    $type = "extend_user_by_admin";
    $stmt->bindParam(':id_user', $from_id, PDO::PARAM_STR);
    $stmt->bindParam(':username', $nameloc['username'], PDO::PARAM_STR);
    $stmt->bindParam(':value', $value, PDO::PARAM_STR);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':time', $dateacc, PDO::PARAM_STR);
    $stmt->bindParam(':price', $prodcut['price_product'], PDO::PARAM_STR);
    $output_json = json_encode($extend);
    $stmt->bindParam(':output', $output_json, PDO::PARAM_STR);
    $stmt->execute();
    update("invoice", "Status", "active", "id_invoice", $id_invoice);
    sendmessage($from_id, $textbotlang['users']['extend']['thanks'], null, 'HTML');
    $text_report = "⭕️ ادمین سرویس کاربر را تمدید کرد.
        
اطلاعات کاربر : 
        
🪪 آیدی عددی ادمین : <code>$from_id</code>
🪪 آیدی عددی : <code>{$nameloc['id_user']}</code>
🛍 نام محصول :  {$prodcut['name_product']}
👤 نام کاربری مشتری در پنل  : {$nameloc['username']}
موقعیت سرویس سرویس کاربر : {$nameloc['Service_location']}";
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherservice,
            'text' => $text_report,
            'parse_mode' => "HTML"
        ]);
    }
    return;
}

if (!$rf_admin_handled && (preg_match('/removeresid_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $idorder = $dataget[1];
    $stmt = $pdo->prepare("DELETE FROM Payment_report WHERE id_order = :id_order");
    $stmt->bindParam(':id_order', $idorder, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, "✅ رسید با موفقیت حذف شد.", null, 'HTML');
    return;
}

if (!$rf_admin_handled && (isset($update["inline_query"]))) {
    $rf_admin_handled = true;

    $sql = "SELECT * FROM invoice WHERE (username LIKE CONCAT('%', :username, '%') OR note  LIKE CONCAT('%', :notes, '%') OR Volume LIKE CONCAT('%',:Volume, '%') OR Service_time LIKE CONCAT('%',:Service_time, '%')) AND (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold')";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $query, PDO::PARAM_STR);
    $stmt->bindParam(':Service_time', $query, PDO::PARAM_STR);
    $stmt->bindParam(':Volume', $query, PDO::PARAM_STR);
    $stmt->bindParam(':notes', $query, PDO::PARAM_STR);
    $stmt->execute();
    $invoices = $stmt->fetchAll();
    $results = [];
    foreach ($invoices as $OrderUser) {
        if (isset($OrderUser['time_sell'])) {
            $datatime = jdate('Y/m/d H:i:s', $OrderUser['time_sell']);
        } else {
            $datatime = $textbotlang['Admin']['ManageUser']['dataorder'];
        }
        if ($OrderUser['name_product'] == "سرویس تست") {
            $OrderUser['Service_time'] = $OrderUser['Service_time'] . "ساعته";
            $OrderUser['Volume'] = $OrderUser['Volume'] . "مگابایت";
        } else {
            $OrderUser['Service_time'] = $OrderUser['Service_time'] . "روزه";
            $OrderUser['Volume'] = $OrderUser['Volume'] . "گیگابایت";
        }
        $results[] = [
            "type" => "article",
            "id" => uniqid(),
            'cache_time' => 0,
            'is_personal' => true,
            "title" => $OrderUser['username'],
            "input_message_content" => [
                "message_text" => "
🛒 شماره سفارش  :  {$OrderUser['id_invoice']}
🛒  وضعیت سفارش در ربات : {$OrderUser['Status']}
🙍‍♂️ شناسه کاربر : {$OrderUser['id_user']}
👤 نام کاربری اشتراک :  {$OrderUser['username']}
📍 موقعیت سرویس :  {$OrderUser['Service_location']}
🛍 نام محصول :  {$OrderUser['name_product']}
💰 قیمت پرداختی سرویس : {$OrderUser['price_product']} تومان
⚜️ حجم سرویس خریداری شده : {$OrderUser['Volume']}
⏳ زمان سرویس خریداری شده : {$OrderUser['Service_time']} 
📆 تاریخ خرید : $datatime  
"
            ]
        ];
    }
    answerInlineQuery($inline_query_id, $results);
    return;
}

