<?php
rf_set_module('admin/routes/09_reject_pay__step_reject-dec__step_selectloc.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && (preg_match('/reject_pay_(\w+)/', $datain, $datagetr) && ($adminrulecheck['rule'] == "administrator" || $adminrulecheck['rule'] == "Seller"))) {
    $rf_admin_handled = true;

    $id_order = $datagetr[1];
    $Payment_report = select("Payment_report", "*", "id_order", $id_order, "select");
    if ($Payment_report == false) {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "تراکنش حذف شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    update("user", "Processing_value", $Payment_report['id_user'], "id", $from_id);
    update("user", "Processing_value_one", $id_order, "id", $from_id);
    if ($Payment_report['payment_Status'] == "reject" || $Payment_report['payment_Status'] == "paid") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => $textbotlang['Admin']['Payment']['reviewedpayment'],
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }
    update("Payment_report", "payment_Status", "reject", "id_order", $id_order);

    sendmessage($from_id, $textbotlang['Admin']['Payment']['Reasonrejecting'], $backadmin, 'HTML');
    step('reject-dec', $from_id);
    Editmessagetext($from_id, $message_id, $text_inline, null);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "reject-dec")) {
    $rf_admin_handled = true;

    $Payment_report = select("Payment_report", "*", "id_order", $user['Processing_value_one'], "select");
    update("Payment_report", "dec_not_confirmed", $text, "id_order", $user['Processing_value_one']);
    $text_reject = "❌ کاربر گرامی پرداخت شما به دلیل زیر رد گردید.
✍️ $text
🛒 کد پیگیری پرداخت: {$user['Processing_value_one']}
                ";
    sendmessage($from_id, $textbotlang['Admin']['Payment']['Rejected'], $keyboardadmin, 'HTML');
    sendmessage($user['Processing_value'], $text_reject, null, 'HTML');
    step('home', $from_id);
    $text_report = "❌ یک ادمین رسید پرداخت را رد کرد.
        
اطلاعات :
💸 روش پرداخت : {$Payment_report['Payment_Method']}
👤آیدی عددی  ادمین تایید کننده : $from_id
نام کاربری ادمین تایید کننده : @$username
💰 مبلغ پرداخت : {$Payment_report['price']}
دلیل رد کردن : $text
👤 ایدی عددی کاربر: {$Payment_report['id_user']}";
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $paymentreports,
            'text' => $text_report,
            'parse_mode' => "HTML"
        ]);
    }
    return;
}

if (!$rf_admin_handled && ($text == "❌ حذف محصول" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Product']['Rmove_location'], $json_list_marzban_panel, 'HTML');
    step('selectloc', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "selectloc")) {
    $rf_admin_handled = true;

    update("user", "Processing_value", $text, "id", $from_id);
    step('remove-product', $from_id);
    sendmessage($from_id, $textbotlang['Admin']['Product']['selectRemoveProduct'], $json_list_product_list_admin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "remove-product")) {
    $rf_admin_handled = true;

    if (!in_array($text, $name_product)) {
        sendmessage($from_id, $textbotlang['users']['sell']['error-product'], null, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("DELETE FROM product WHERE name_product =:name_product AND (Location= :Location or Location= '/all')");
    $stmt->bindParam(':name_product', $text, PDO::PARAM_STR);
    $stmt->bindParam(':Location', $user['Processing_value'], PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['RemoveedProduct'], $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "✏️ ویرایش محصول" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Product']['Rmove_location'], $list_marzban_panel_edit_product, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/locationedit_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $location = $dataget[1];
    $location = $location == "all" ? "/all" : $location;
    update("user", "Processing_value_one", $location, "id", $from_id);
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "کاربر عادی", 'callback_data' => 'typeagenteditproduct_f'],
            ],
            [
                ['text' => "نماینده پیشرفته", 'callback_data' => 'typeagenteditproduct_n2'],
                ['text' => "نماینده عادی", 'callback_data' => 'typeagenteditproduct_n'],
            ],
            [
                ['text' => "بازگشت", 'callback_data' => "admin"]
            ]
        ]
    ]);
    Editmessagetext($from_id, $message_id, "📌 نوع کاربری را انتخاب کنید", $Response);
    return;
}

if (!$rf_admin_handled && (preg_match('/^typeagenteditproduct_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $typeagent = $dataget[1];
    update("user", "Processing_value_tow", $typeagent, "id", $from_id);
    $product = [];
    $escapedText = mysqli_real_escape_string($connect, $user['Processing_value_one']);
    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $getdataproduct = mysqli_query($connect, "SELECT * FROM product WHERE (Location = '{$panel['name_panel']}' or Location = '/all') AND agent = '$typeagent'");
    $list_product = [
        'inline_keyboard' => [],
    ];
    if (isset($getdataproduct)) {
        while ($row = mysqli_fetch_assoc($getdataproduct)) {
            $list_product['inline_keyboard'][] = [
                ['text' => $row['name_product'], 'callback_data' => "productedit_" . $row['id']]
            ];
        }
        $list_product['inline_keyboard'][] = [
            ['text' => "🏠 بازگشت به منوی قبل", 'callback_data' => "locationedit_" . $user['Processing_value_one']],
        ];

        $json_list_product_list_admin = json_encode($list_product);
    }
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Product']['selectEditProduct'], $json_list_product_list_admin);
    return;
}

if (!$rf_admin_handled && (preg_match('/^productedit_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_product = $dataget[1];
    deletemessage($from_id, $message_id);
    update("user", "Processing_value", $id_product, "id", $from_id);
    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $info_product = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE id = '$id_product'  AND agent = '{$user['Processing_value_tow']}' AND (Location = '{$panel['name_panel']}' OR Location = '/all') LIMIT 1"));
    $count_invoice = select("invoice", "*", "name_product", $info_product['name_product'], "count");
    $infoproduct = "
📌 اطلاعات محصول در حال ویرایش:
نام محصول :  {$info_product['name_product']}
قیمت محصول : {$info_product['price_product']}
حجم محصول : {$info_product['Volume_constraint']}
موقعیت محصول : {$info_product['Location']}
زمان محصول : {$info_product['Service_time']}
نوع کاربری محصول : {$info_product['agent']}
ریست دوره ای حجم محصول : {$info_product['data_limit_reset']}
یادداشت محصول : {$info_product['note']}
دسته بندی محصول : {$info_product['category']}
تعداد محصول فروخته شده : $count_invoice عدد
    ";
    sendmessage($from_id, $infoproduct, $change_product, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "قیمت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "قیمت جدید را ارسال کنید", $backadmin, 'HTML');
    step('change_price', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "change_price")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidPrice'], $backadmin, 'HTML');
        return;
    }
    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $stmt = $pdo->prepare("UPDATE product SET price_product = :price_product WHERE id = :name_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':price_product', $text);
    $stmt->bindParam(':name_product', $user['Processing_value']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    sendmessage($from_id, "✅ قیمت محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "یادداشت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "یادداشت جدید را ارسال کنید", $backadmin, 'HTML');
    step('change_note', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "change_note")) {
    $rf_admin_handled = true;

    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $stmt = $pdo->prepare("UPDATE product SET note = :notes WHERE id = :name_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':notes', $text);
    $stmt->bindParam(':name_product', $user['Processing_value']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    sendmessage($from_id, "✅ یادداشت محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "دسته بندی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "نام دسته بندی جدید را انتخاب کنید", KeyboardCategoryadmin(), 'HTML');
    step('change_categroy', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "change_categroy")) {
    $rf_admin_handled = true;

    $category = select("category", "*", "remark", $text, "count");
    if ($category == 0) {
        sendmessage($from_id, "❌ دسته بندی انتخاب شده وجود ندارد از بخش پلن ها > اضافه کردن دسته بندی ُ دسته بندی خود را اضافه کنید سپس محصول را اضافه نمایید.", KeyboardCategoryadmin(), 'HTML');
        return;
    }
    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $stmt = $pdo->prepare("UPDATE product SET category = :categroy WHERE id = :name_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':categroy', $text);
    $stmt->bindParam(':name_product', $user['Processing_value']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    sendmessage($from_id, "✅ دسته بندی محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "نام محصول" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "نام جدید را ارسال کنید", $backadmin, 'HTML');
    step('change_name', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "change_name")) {
    $rf_admin_handled = true;

    if (strlen($text) > 150) {
        sendmessage($from_id, "❌ نام محصول باید کمتر از 150 کاراکتر باشد", $backadmin, 'HTML');
        return;
    }
    if (in_array($text, $name_product)) {
        sendmessage($from_id, "❌ محصول با نام $text وجود دارد", $backadmin, 'HTML');
        return;
    }
    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $stmt = $pdo->prepare("UPDATE product SET name_product = :name_products WHERE id = :name_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':name_products', $text);
    $stmt->bindParam(':name_product', $user['Processing_value']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    sendmessage($from_id, "✅نام محصول بروزرسانی شد", $change_product, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "نوع کاربری" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "نوع کاربری جدید را ارسال کنید :
نوع کاربری ها :f , n , n2", $backadmin, 'HTML');
    step('change_type_agent', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "change_type_agent")) {
    $rf_admin_handled = true;

    if (!in_array($text, ['f', 'n', 'n2'])) {
        sendmessage($from_id, "❌ گروه کاربری نامعتبر می باشد", null, 'HTML');
        return;
    }
    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $stmt = $pdo->prepare("UPDATE product SET agent = :agents WHERE id = :name_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':agents', $text);
    $stmt->bindParam(':name_product', $user['Processing_value']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    sendmessage($from_id, "✅نام محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "نوع ریست حجم" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "نوع ریست حجم را ارسال کنید", $keyboardtimereset, 'HTML');
    step('change_reset_data', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "change_reset_data")) {
    $rf_admin_handled = true;

    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $stmt = $pdo->prepare("UPDATE product SET data_limit_reset = :data_limit_reset WHERE id = :name_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':data_limit_reset', $text);
    $stmt->bindParam(':name_product', $user['Processing_value']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    sendmessage($from_id, "✅نام محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "موقعیت محصول" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 موقعیت جدید محصول را انتخاب کنید", $json_list_marzban_panel, 'HTML');
    step('change_loc_data', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "change_loc_data")) {
    $rf_admin_handled = true;

    if ($text == "/all") {
        sendmessage($from_id, "❌ نمی توانید محصول تعریف شده را به نام موقعیت /all تغییر دهید.", $shopkeyboard, 'HTML');
        return;
    }
    $product = select("product", "*", "name_product", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $stmt = $pdo->prepare("UPDATE product SET Location = :Location2 WHERE id = :name_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':Location2', $text);
    $stmt->bindParam(':name_product', $user['Processing_value']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    $stmt = $pdo->prepare("UPDATE invoice SET Service_location = :Service_location WHERE name_product = :name_product AND Service_location = :Location ");
    $stmt->bindParam(':Service_location', $text);
    $stmt->bindParam(':name_product', $product['name_product']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->execute();
    sendmessage($from_id, "✅موقعیت محصول بروزرسانی شد", $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "حجم" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "حجم جدید را ارسال کنید", $backadmin, 'HTML');
    step('change_val', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "change_val")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backadmin, 'HTML');
        return;
    }
    $product = select("product", "*", "id", $user['Processing_value']);
    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one']);
    $stmt = $pdo->prepare("UPDATE product SET Volume_constraint = :Volume_constraint WHERE id = :name_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':Volume_constraint', $text);
    $stmt->bindParam(':name_product', $product['id']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['volumeUpdated'], $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "زمان" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Product']['NewTime'], $backadmin, 'HTML');
    step('change_time', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "change_time")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['InvalidTime'], $backadmin, 'HTML');
        return;
    }
    $panel = select("marzban_panel", "*", "code_panel", $user['Processing_value_one'], "select");
    $stmt = $pdo->prepare("UPDATE product SET Service_time = :Service_time WHERE id = :id_product AND (Location = :Location OR Location = '/all') AND agent = :agent");
    $stmt->bindParam(':Service_time', $text);
    $stmt->bindParam(':id_product', $user['Processing_value']);
    $stmt->bindParam(':Location', $panel['name_panel']);
    $stmt->bindParam(':agent', $user['Processing_value_tow']);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Product']['TimeUpdated'], $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "balanceaddall")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Balance']['addallbalance'], $backadmin, 'HTML');
    step('add_Balance_all', $from_id);
    return;
}

