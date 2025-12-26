<?php
rf_set_module('admin/routes/36_previous_pageuserzero__step_edit_app__step_get_new_lin_app.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($datain == 'previous_pageuserzero')) {
    $rf_admin_handled = true;

    $page = $user['pagenumber'];
    $items_per_page = 10;
    if ($user['pagenumber'] <= 1) {
        $next_page = 1;
    } else {
        $next_page = $page - 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    $result = mysqli_query($connect, "SELECT * FROM user WHERE Balance < 0  LIMIT $start_index, $items_per_page");
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    $keyboardlists['inline_keyboard'][] = [
        ['text' => "عملیات", 'callback_data' => "action"],
        ['text' => "نام کاربری", 'callback_data' => "username"],
        ['text' => "شناسه", 'callback_data' => "iduser"]
    ];
    while ($row = mysqli_fetch_assoc($result)) {
        $keyboardlists['inline_keyboard'][] = [
            [
                'text' => $textbotlang['Admin']['ManageUser']['mangebtnuser'],
                'callback_data' => "manageuser_" . $row['id']
            ],
            [
                'text' => $row['username'],
                'callback_data' => "username"
            ],
            [
                'text' => $row['id'],
                'callback_data' => $row['id']
            ],
        ];
    }
    $pagination_buttons = [
        [
            'text' => $textbotlang['users']['page']['next'],
            'callback_data' => 'next_pageuserzero'
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_pageuserzero'
        ]
    ];
    $backbtn = [
        [
            'text' => "بازگشت به منوی قبل",
            'callback_data' => 'backlistuser'
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboardlists['inline_keyboard'][] = $backbtn;
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && ($text == "✏️ ویرایش برنامه")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 برای ویرایش برنامه از لیست زیر نام برنامه را انتخاب کنید", $json_list_remove_helpـlink, 'HTML');
    step("edit_app", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "edit_app")) {
    $rf_admin_handled = true;

    savedata("clear", "nameapp", $text);
    step("get_new_lin_app", $from_id);
    sendmessage($from_id, "📌 لینک جدید اپ را ارسال کنید", $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_new_lin_app")) {
    $rf_admin_handled = true;

    step("home", $from_id);
    $userdata = json_decode($user['Processing_value'], true);
    sendmessage($from_id, "✅ لینک برنامه با موفقیت بروزرسانی گردید.", $keyboardlinkapp, 'HTML');
    update("app", "link", $text, "name", $userdata['nameapp']);
    return;
}

if (!$rf_admin_handled && ($datain == "nowpaymentsetting")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $nowpayment_setting_keyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "⏳ زمان تایید خودکار بدون بررسی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید که قابلیت تایید خودکار بدون بررسی  بعد از چند دقیقه رسید را تایید کند.
زمان خود را بر حسب دقیقه ارسال کنید
زمان فعلی : {$setting['timeauto_not_verify']}", $backadmin, 'HTML');
    step("gettimeauto", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettimeauto")) {
    $rf_admin_handled = true;

    if (!is_numeric($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    update("setting", "timeauto_not_verify", $text);
    sendmessage($from_id, "✅ زمان با موفقیت ثبت گردید.", $CartManage, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "نمایش برای خرید اول")) {
    $rf_admin_handled = true;

    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $stmt = $pdo->prepare("SELECT * FROM product WHERE id = :name_product  AND agent = :agent AND (Location = :Location OR Location = '/all') LIMIT 1");
    $stmt->bindParam(':name_product', $user['Processing_value']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    $status_name = [
        '0' => "خاموش",
        '1' => "روشن"
    ][$product['one_buy_status']];
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $status_name, 'callback_data' => 'status_on_buy-' . $product['code_product'] . "-" . $product['one_buy_status']],
            ],
        ]
    ]);
    sendmessage($from_id, "📌 از طریق این قابلیت می توانید تعیین کنید این محصول برای خرید اول باشد یا خیر", $Response, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/status_on_buy-(.*)-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $code_product = $dataget[1];
    $status_now = $dataget[2];
    if ($status_now == '0') {
        $status_now = '1';
    } else {
        $status_now = '0';
    }
    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $stmt = $pdo->prepare("UPDATE product SET one_buy_status = :one_buy_status WHERE code_product = :code_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':one_buy_status', $status_now);
    $stmt->bindParam(':code_product', $code_product);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    $stmt = $pdo->prepare("SELECT * FROM product WHERE code_product = :code_product  AND agent = :agent AND (Location = :Location OR Location = '/all') LIMIT 1");
    $stmt->bindParam(':code_product', $code_product);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    $status_name = [
        '0' => "خاموش",
        '1' => "روشن"
    ][$product['one_buy_status']];
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $status_name, 'callback_data' => 'status_on_buy-' . $product['code_product'] . "-" . $product['one_buy_status']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, "📌 از طریق این قابلیت می توانید تعیین کنید این محصول برای خرید اول باشد یا خیر", $Response);
    return;
}

if (!$rf_admin_handled && ($text == "💳 استثناء کردن کاربر از تایید خودکار")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 یک گزینه را انتخاب کنید
⚠️ این بخش برای تایید خودکار بدون بررسی می باشد", $Exception_auto_cart_keyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "➕ استثناء کردن کاربر")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 آیدی عددی کاربر را ارسال کنید", $backadmin, 'HTML');
    step("getidExceptio", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getidExceptio")) {
    $rf_admin_handled = true;

    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "❌ کاربر وجود ندارد.", $backadmin, 'HTML');
        return;
    }
    $list_Exceptions = select("PaySetting", "ValuePay", "NamePay", "Exception_auto_cart", "select")['ValuePay'];
    $list_Exceptions = is_string($list_Exceptions) ? json_decode($list_Exceptions, true) : [];
    if (in_array($text, $list_Exceptions)) {
        sendmessage($from_id, "❌ کاربر در لیست استثناء وجود دارد", $backadmin, 'HTML');
        return;
    }
    $list_Exceptions[] = $text;
    $list_Exceptions = array_values($list_Exceptions);
    sendmessage($from_id, "✅ کاربر با موفقیت به لیست اضافه گردید.", $Exception_auto_cart_keyboard, 'HTML');
    update("PaySetting", "ValuePay", json_encode($list_Exceptions), "NamePay", "Exception_auto_cart");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "❌ حذف کاربر از لیست")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 آیدی عددی کاربر را جهت حذف از لیست ارسال کنید", $backadmin, 'HTML');
    step("getidExceptioremove", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getidExceptioremove")) {
    $rf_admin_handled = true;

    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, "❌ کاربر وجود ندارد.", $backadmin, 'HTML');
        return;
    }
    $list_Exceptions = select("PaySetting", "ValuePay", "NamePay", "Exception_auto_cart", "select")['ValuePay'];
    $list_Exceptions = is_string($list_Exceptions) ? json_decode($list_Exceptions, true) : [];
    if (!in_array($text, $list_Exceptions)) {
        sendmessage($from_id, "❌ کاربر در لیست استثناء وجود ندارد", $backadmin, 'HTML');
        return;
    }
    $count = 0;
    foreach ($list_Exceptions as $list) {
        if ($list == $text) {
            unset($list_Exceptions[$count]);
            break;
        }
        $count += 1;
    }
    $list_Exceptions = array_values($list_Exceptions);
    sendmessage($from_id, "✅ کاربر با موفقیت از لیست حذف گردید.", $Exception_auto_cart_keyboard, 'HTML');
    update("PaySetting", "ValuePay", json_encode($list_Exceptions), "NamePay", "Exception_auto_cart");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "👁 نمایش لیست افراد")) {
    $rf_admin_handled = true;

    $list_Exceptions = select("PaySetting", "ValuePay", "NamePay", "Exception_auto_cart", "select")['ValuePay'];
    $list_Exceptions = is_string($list_Exceptions) ? json_decode($list_Exceptions, true) : [];
    if (count($list_Exceptions) == 0) {
        sendmessage($from_id, "❌ کاربری در لیست وجود ندارد", null, 'HTML');
        return;
    }
    $list = "";
    foreach ($list_Exceptions as $list_ex) {
        $list .= $list_ex . "\n";
    }
    sendmessage($from_id, "لیست افراد👇", null, 'HTML');
    sendmessage($from_id, $list, null, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "تنظیم api" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "marchent_floypay")['ValuePay'];
    $textaqayepardakht = "api دریافت شده را در این بخش ارسال کنید
        
مرچنت کد فعلی شما : $PaySetting";
    sendmessage($from_id, $textaqayepardakht, $backadmin, 'HTML');
    step('marchent_floypay', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "marchent_floypay")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $Swapinokey, 'HTML');
    update("PaySetting", "ValuePay", $text, "NamePay", "marchent_floypay");
    step('home', $from_id);
    return;
}

