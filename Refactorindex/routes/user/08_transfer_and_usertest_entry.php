<?php
rf_set_module('routes/user/08_transfer_and_usertest_entry.php');
if (!$rf_chain1_handled && ($user['step'] == "getidfortransfer")) {
    $rf_chain1_handled = true;
    if (!userExists($text)) {
        sendmessage($from_id, $textbotlang['Admin']['transfor']['notusertrns'], $backuser, 'HTML');
        rf_stop();
    }
    update("user", "Processing_value_one", $text, "id", $from_id);
    $confirmtransfer = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "✅ تایید انتقال سرویس", 'callback_data' => "confrimtransfers_{$user['Processing_value_tow']}"],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['transfor']['confirm'], $confirmtransfer, 'HTML');
    step("home", $from_id);
}
if (!$rf_chain1_handled && (preg_match('/confrimtransfers_(\w+)/', $datain, $dataget))) {
    $rf_chain1_handled = true;
    if ($from_id == $user['Processing_value_one']) {
        sendmessage($from_id, $textbotlang['Admin']['transfor']['notsendserviceyou'], $keyboard, 'HTML');
        rf_stop();
    }
    $id_invoice = $dataget[1];
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    update("invoice", "id_user", $user['Processing_value_one'], "id_invoice", $id_invoice);
    $bakinfos = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "backorder"],
            ]
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['transfor']['confirmed'], $bakinfos);
    $texttransfer = "✅ کاربر گرامی  سرویس با نام کاربری {$nameloc['username']} از طرف کاربر با شناسه کاربری $from_id  به حساب کاربری شما منتقل گردید.";
    sendmessage($user['Processing_value_one'], $texttransfer, $keyboard, 'HTML');
    $stmt = $connect->prepare("INSERT IGNORE INTO service_other (id_user, username,value,type,time,price) VALUES (?, ?, ?, ?,?,?)");
    $value = $user['Processing_value_one'];
    $dateacc = date('Y/m/d H:i:s');
    $type = "transfertouser";
    $price = "0";
    $stmt->bind_param("ssssss", $from_id, $nameloc['username'], $value, $type, $dateacc, $price);
    $stmt->execute();
    $stmt->close();
}
if (!$rf_chain1_handled && ($text == $datatextbot['text_usertest'] || $datain == "usertestbtn" || $text == "usertest")) {
    $rf_chain1_handled = true;
    if (!check_active_btn($setting['keyboardmain'], "text_usertest")) {
        sendmessage($from_id, "📌 سرویس تست در حال حاضر در دسترس نیست .", null, 'HTML');
        rf_stop();
    }
    $locationproduct = select("marzban_panel", "*", "TestAccount", "ONTestAccount", "count");
    if ($locationproduct == 0) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['nullpanel'], null, 'HTML');
        rf_stop();
    }
    if ($locationproduct != 1) {
        if ($setting['get_number'] == "onAuthenticationphone" && $user['step'] != "get_number" && $user['number'] == "none") {
            sendmessage($from_id, $textbotlang['users']['number']['Confirming'], $request_contact, 'HTML');
            step('get_number', $from_id);
        }
        if ($user['number'] == "none" && $setting['get_number'] == "onAuthenticationphone")
            rf_stop();
        if ($user['limit_usertest'] <= 0 && !in_array($from_id, $admin_ids)) {
            sendmessage($from_id, $textbotlang['users']['usertest']['limitwarning'], $keyboard_buy, 'html');
            rf_stop();
        }
        sendmessage($from_id, $datatextbot['textselectlocation'], $list_marzban_usertest, 'html');
    }
}
