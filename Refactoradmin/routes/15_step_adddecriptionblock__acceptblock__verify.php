<?php
rf_set_module('admin/routes/15_step_adddecriptionblock__acceptblock__verify.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "adddecriptionblock")) {
    $rf_admin_handled = true;

    update("user", "description_blocking", $text, "id", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['DescriptionBlock'], $keyboardadmin, 'HTML');
    step('home', $from_id);

    return;
}

if (!$rf_admin_handled && ((preg_match('/acceptblock_(\w+)/', $datain, $dataget) || preg_match('/blockuserfake_(\w+)/', $datain, $dataget)))) {
    $rf_admin_handled = true;


    $iduser = $dataget[1];
    update("user", "Processing_value", $iduser, "id", $from_id);
    update("user", "User_Status", "block", "id", $iduser);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['BlockUser'], $backadmin, 'HTML');
    step('adddecriptionblock', $from_id);
    $textblok = "کاربر با آیدی عددی
$iduser  در ربات مسدود گردید 
ادمین مسدود کننده : $from_id";
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['Admin']['ManageUser']['mangebtnuser'], 'callback_data' => 'manageuser_' . $iduser],
            ],
        ]
    ]);
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherservice,
            'text' => $textblok,
            'parse_mode' => "HTML",
            'reply_markup' => $Response
        ]);
    }
    return;
}

if (!$rf_admin_handled && (preg_match('/verify_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    update("user", "verify", "1", "id", $iduser);
    sendmessage($from_id, "✅ کاربر با موفقیت احراز گردید.", null, 'HTML');
    sendmessage($iduser, "💎 کاربر گرامی حساب کاربری شما توسط ادمین با موفقیت احراز هویت گردید و هم اکنون می توانیدخرید خود را انجام دهید", $keyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/unverify-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    update("user", "verify", "0", "id", $iduser);
    sendmessage($from_id, "✅ کاربر با موفقیت از حالت احراز خارج گردید.", null, 'HTML');


    return;
}

if (!$rf_admin_handled && (preg_match('/unbanuserr_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    $userdata = select("user", "*", "id", $iduser, "select");
    if ($userdata['User_Status'] == "Active") {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['UserNotBlock'], null, 'HTML');
        return;
    }
    $textblok = "کاربر با آیدی عددی
$iduser  در ربات  رفع مسدود گردید 
ادمین مسدود کننده : $from_id";
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['Admin']['ManageUser']['mangebtnuser'], 'callback_data' => 'manageuser_' . $iduser],
            ],
        ]
    ]);
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherservice,
            'text' => $textblok,
            'parse_mode' => "HTML",
            'reply_markup' => $Response
        ]);
    }
    update("user", "User_Status", "Active", "id", $iduser);
    update("user", "description_blocking", " ", "id", $iduser);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['UserUnblocked'], $keyboardadmin, 'HTML');
    sendmessage($iduser, "✳️ حساب کاربری شما از مسدودی خارج شد ✳️
اکنون میتوانید از ربات استفاده کنید ✔️", $keyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/confirmnumber_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    update("user", "number", "confrim number by admin", "id", $iduser);
    sendmessage($from_id, $textbotlang['Admin']['phone']['active'], $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/viewpaymentuser_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    $PaymentUsers = mysqli_query($connect, "SELECT * FROM Payment_report WHERE id_user = '$iduser'");
    foreach ($PaymentUsers as $paymentUser) {
        $text_order = "🛒 شماره پرداخت  :  <code>{$paymentUser['id_order']}</code>
🙍‍♂️ شناسه کاربر : <code>{$paymentUser['id_user']}</code>
💰 مبلغ پرداختی : {$paymentUser['price']} تومان
⚜️ وضعیت پرداخت : {$paymentUser['payment_Status']}
⭕️ روش پرداخت : {$paymentUser['Payment_Method']} 
📆 تاریخ خرید :  {$paymentUser['time']}";
        sendmessage($from_id, $text_order, null, 'HTML');
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['sendpayemntlist'], $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/affiliates-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    $affiliatesUsers = select("user", "*", "affiliates", $iduser, "count");
    if ($affiliatesUsers == 0) {
        sendmessage($from_id, "❌ کاربر دارای زیرمجموعه نمی باشد.", null, 'HTML');
        return;
    }
    $affiliatesUsers = select("user", "*", "affiliates", $iduser, "fetchAll");
    $count = 0;
    $text_affiliates = "";
    foreach ($affiliatesUsers as $affiliatesUser) {
        $text_affiliates .= "<code>{$affiliatesUser['id']}</code>\n\r";
        $count++;
        if ($count == 10) {
            sendmessage($from_id, $text_affiliates, null, 'HTML');
            $count = 0;
            $text_affiliates = "";
        }
    }
    sendmessage($from_id, $text_affiliates, null, 'HTML');
    sendmessage($from_id, "📌 شناسه مربوط به زیرمجموعه های کاربر ارسال گردید.", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/removeaffiliate-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    $user2 = select("user", "*", "id", $iduser, "select");
    $user2 = select("user", "*", "id", $user2['affiliates'], "select");
    $affiliatescount = intval($user2['affiliatescount']) - 1;
    update("user", "affiliatescount", $affiliatescount, "id", $user2['id']);
    update("user", "affiliates", "0", "id", $iduser);
    sendmessage($from_id, "📌 کاربر از زیرمجموعه خارج شد.", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/removeaffiliateuser-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    update("user", "affiliatescount", "0", "id", $iduser);
    update("user", "affiliates", "0", "affiliates", $iduser);
    sendmessage($from_id, "📌 زیرمجموعه های کاربر حذف شد.", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/removeservice-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $username = $dataget[1];
    $info_product = select("invoice", "*", "id_invoice", $username, "select");
    $DataUserOut = $ManagePanel->DataUser($info_product['Service_location'], $info_product['username']);
    $ManagePanel->RemoveUser($info_product['Service_location'], $info_product['username']);
    update('invoice', 'status', 'removebyadmin', 'id_invoice', $username);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['RemovedService'], $keyboardadmin, 'HTML');
    Editmessagetext($from_id, $message_id, $text_inline, json_encode(['inline_keyboard' => []]));
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/removeserviceandback-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $username = $dataget[1];
    $info_product = select("invoice", "*", "id_invoice", $username, "select");
    if ($info_product['Status'] == "removebyadmin") {
        sendmessage($from_id, "❌ سرویس از قبل حذف شده است", $keyboardadmin, 'HTML');
        return;
    }
    $DataUserOut = $ManagePanel->DataUser($info_product['Service_location'], $info_product['username']);
    if (isset($DataUserOut['msg']) && $DataUserOut['msg'] == "User not found") {
        sendmessage($from_id, $textbotlang['users']['stateus']['UserNotFound'], null, 'html');
    } else {
        if ($DataUserOut['status'] == "Unsuccessful") {
            sendmessage($from_id, 'خطایی رخ داده است', $keyboardadmin, 'HTML');
        }
    }
    $ManagePanel->RemoveUser($info_product['Service_location'], $info_product['username']);
    update('invoice', 'status', 'removebyadmin', 'id_invoice', $username);
    $Balance_user = select("user", "*", "id", $info_product['id_user'], "select");
    $Balance_add_user = $Balance_user['Balance'] + $info_product['price_product'];
    update("user", "Balance", $Balance_add_user, "id", $info_product['id_user']);
    $textadd = "💎 کاربر عزیز مبلغ {$info_product['price_product']} تومان به موجودی کیف پول تان اضافه گردید.";
    sendmessage($info_product['id_user'], $textadd, null, 'HTML');
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['RemovedService'], $keyboardadmin, 'HTML');
    Editmessagetext($from_id, $message_id, $text_inline, json_encode(['inline_keyboard' => []]));
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🎁 ساخت کد تخفیف" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Discountsell']['GetCode'], $backadmin, 'HTML');
    step('get_codesell', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_codesell")) {
    $rf_admin_handled = true;

    if (!preg_match('/^[A-Za-z\d]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['ErrorCode'], null, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Discount']['PriceCodesell'], null, 'HTML');
    step('get_price_codesell', $from_id);
    savedata("clear", "code", strtolower($text));
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_price_codesell")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    savedata("save", "price", $text);
    sendmessage($from_id, $textbotlang['Admin']['Discountsell']['getlimit'], $backadmin, 'HTML');
    step('getlimitcode', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getlimitcode")) {
    $rf_admin_handled = true;

    savedata("save", "limitDiscount", $text);
    sendmessage($from_id, $textbotlang['Admin']['Discount']['agentcode'], $backadmin, 'HTML');
    step('gettypecodeagent', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettypecodeagent")) {
    $rf_admin_handled = true;

    $agentst = ["n", "n2", "f", "allusers"];
    if (!in_array($text, $agentst)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['invalidagentcode'], $bakcadmin, 'HTML');
        return;
    }
    savedata("save", "agent", $text);
    sendmessage($from_id, "📌 کد تخفیف برای چند ساعت فعال باشد . در صورتی که میخواهید نامحدود باشد عدد 0 را ارسال کنید", $backadmin, 'HTML');
    step('gettimediscount', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettimediscount")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    if (intval($text) == 0) {
        $text = "0";
    } else {
        $text = time() + (intval($text) * 3600);
    }
    savedata("save", "time", $text);
    $keyboarddiscount = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "تمامی خرید ها", 'callback_data' => "discountlimitbuy_0"],
                ['text' => "خرید اول", 'callback_data' => "discountlimitbuy_1"],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Discount']['firstdiscount'], $keyboarddiscount, 'HTML');
    step('getfirstdiscount', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/discountlimitbuy_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $discountbuylimit = $dataget[1];
    savedata("save", "usefirst", $discountbuylimit);
    if (intval($discountbuylimit) == 1) {
        sendmessage($from_id, "📌محدودیت استفاده برای یک کاربر را ارسال نمایید.", $backadmin, 'HTML');
        step('getuseuser', $from_id);
        savedata("save", "typediscount", "all");
    } else {
        $keyboarddiscount = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "خرید", 'callback_data' => "discounttype_buy"],
                    ['text' => "تمدید", 'callback_data' => "discounttype_extend"],
                ],
                [
                    ['text' => "هردو", 'callback_data' => "discounttype_all"]
                ]
            ]
        ]);
        Editmessagetext($from_id, $message_id, "📌 کد تخفیف برای کدوم بخش باشد", $keyboarddiscount);
    }
    return;
}

if (!$rf_admin_handled && (preg_match('/discounttype_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $discountbuytype = $dataget[1];
    Editmessagetext($from_id, $message_id, $text_inline, json_encode(['inline_keyboard' => []]));
    savedata("save", "typediscount", $discountbuytype);
    sendmessage($from_id, "📌محدودیت استفاده برای یک کاربر را ارسال نمایید.", $backadmin, 'HTML');
    step('getuseuser', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getuseuser")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    $numberlimit = $userdata['limitDiscount'];
    if (intval($text) > intval($userdata['limitDiscount'])) {
        sendmessage($from_id, "📌 تعداد استفاده برای یک کاربر باید کوچیک تر از محدودیت کل باشد", $backadmin, 'HTML');
        return;
    }
    step('getlocdiscount', $from_id);
    savedata("save", "useuser", $text);
    sendmessage($from_id, "📌 برای تنظیم  کد تخفیف مخصوص یک محصول ابتدا موقعیت محصول راانتخاب نمایید.
توجه : برای انتخاب تمام پنل ها کلمه<code>/all</code> را ارسال کنید", $json_list_marzban_panel, 'HTML');
    step('getlocdiscount', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getlocdiscount")) {
    $rf_admin_handled = true;

    if ($text == "/all") {
        $panel['code_panel'] = "/all";
    } else {
        $panel = select("marzban_panel", "*", "name_panel", $text, "select");
    }
    if ($panel == false)
        return;
    savedata("save", "code_panel", $panel['code_panel']);
    savedata("save", "name_panel", $text);
    sendmessage($from_id, "📌  میخواهید کد تخفیف برای کدام محصول باشد. توجه داشتید درصورتی که میخواهید کد تخفیف برای تمامی محصولات باشد کلمه all را ارسال کنید", $json_list_product_list_admin, 'HTML');
    step('getproductdiscount', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getproductdiscount")) {
    $rf_admin_handled = true;

    if ($text != "all") {
        $product = select("product", "*", "name_product", $text, "select");
    } else {
        $product['code_product'] = "all";
    }
    if ($product == false) {
        sendmessage($from_id, "❌ محصول انتخابی وجود ندارد", $keyboardadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $stmt = $pdo->prepare("INSERT INTO DiscountSell (codeDiscount, usedDiscount, price, limitDiscount, agent, usefirst, useuser, code_panel, code_product, time,type) VALUES (:codeDiscount, :usedDiscount, :price, :limitDiscount, :agent, :usefirst, :useuser, :code_panel, :code_product, :time,:type)");
    $values = "0";
    $values1 = "1";
    $code_product = "0";
    $stmt->bindParam(':codeDiscount', $userdata['code'], PDO::PARAM_STR);
    $stmt->bindParam(':usedDiscount', $values, PDO::PARAM_STR);
    $stmt->bindParam(':price', $userdata['price'], PDO::PARAM_STR);
    $stmt->bindParam(':limitDiscount', $userdata['limitDiscount'], PDO::PARAM_STR);
    $stmt->bindParam(':agent', $userdata['agent'], PDO::PARAM_STR);
    $stmt->bindParam(':usefirst', $userdata['usefirst'], PDO::PARAM_STR);
    $stmt->bindParam(':useuser', $userdata['useuser'], PDO::PARAM_STR);
    $stmt->bindParam(':code_panel', $userdata['code_panel'], PDO::PARAM_STR);
    $stmt->bindParam(':code_product', $product['code_product'], PDO::PARAM_STR);
    $stmt->bindParam(':time', $userdata['time'], PDO::PARAM_STR);
    $stmt->bindParam(':type', $userdata['typediscount'], PDO::PARAM_STR);
    $stmt->execute();
    $textdiscount = "
🎁 کد تخفیف شما با موفقیت ساخته شد.

📩 نام کد تخفیف: <code>{$userdata['code']}</code>
🧮 درصد کد تخفیف: {$userdata['price']}
🎛 پنل :  {$userdata['name_panel']}
📌  محصول : $text
♻️ نوع کاربری :‌ {$userdata['agent']}
🔴 محدودیت استفاده :‌ {$userdata['limitDiscount']}";
    sendmessage($from_id, $textdiscount, $keyboardadmin, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "❌ حذف کد تخفیف" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemoveCode'], $json_list_Discount_list_admin_sell, 'HTML');
    step('remove-Discountsell', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "remove-Discountsell")) {
    $rf_admin_handled = true;

    if (!in_array($text, $SellDiscount)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['NotCode'], null, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("DELETE FROM Giftcodeconsumed WHERE code = :code");
    $stmt->bindParam(':code', $text, PDO::PARAM_STR);
    $stmt->execute();
    $stmt = $pdo->prepare("DELETE FROM DiscountSell WHERE codeDiscount = :codeDiscount");
    $stmt->bindParam(':codeDiscount', $text, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemovedCode'], $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "/end")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    $panel = select("marzban_panel", "*", "name_panel", $userdata['name_panel'], "select");
    if ($panel['type'] == "marzneshin") {
        update("user", "Processing_value", $userdata['name_panel'], "id", $from_id);
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Inbound']['endInbound'], $optionmarzneshin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['managepanel']['Inbound']['endInbound'], $optionMarzban, 'HTML');
    step('home', $from_id);
    return;
    return;
}

