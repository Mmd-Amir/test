<?php
rf_set_module('routes/payment/02_receipt_submission.php');
if (!$rf_chain4_handled && (preg_match('/^sendresidcart-(.*)/', $datain, $dataget))) {
    $rf_chain4_handled = true;
    $timefivemin = time() - 120;
    $timefivemin = date('Y/m/d H:i:s', intval($timefivemin));
    $sql = "SELECT * FROM Payment_report WHERE id_user = '$from_id' AND Payment_Method = 'cart to cart' AND at_updated > '$timefivemin'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $paymentcount = $stmt->rowCount();
    if ($paymentcount != 0 and !in_array($from_id, $admin_ids)) {
        sendmessage($from_id, "❗ شما در ۲ دقیقه اخیر رسید ارسال کرده اید لطفا ۲ دقیقه دیگر رسید جدید را ارسال نمایید.", null, 'HTML');
        rf_stop();
    }
    $payemntcheck = select("Payment_report", "*", "id_order", $dataget[1], "select");
    if ($payemntcheck['payment_Status'] == "paid") {
        sendmessage($from_id, "❗️ تراکنش شما توسط ربات تایید گردیده است.", null, 'HTML');
        rf_stop();
    }
    if ($payemntcheck['payment_Status'] == "expire") {
        sendmessage($from_id, "❗زمان این تراکنش به پایان رسیده و امکان پرداخت این تراکنش وجود ندارد.", null, 'HTML');
        rf_stop();
    }
    deletemessage($from_id, $message_id);
    sendmessage($from_id, "🖼 تصویر رسید خود را ارسال نمایید", $backuser, 'HTML');
    step('cart_to_cart_user', $from_id);
    update("user", "Processing_value", $dataget[1], "id", $from_id);
}
if (!$rf_chain4_handled && (preg_match('/^sendresidarze-(.*)/', $datain, $dataget) and $text_inline != null)) {
    $rf_chain4_handled = true;
    $payemntcheck = select("Payment_report", "*", "id_order", $dataget[1], "select");
    if ($payemntcheck['payment_Status'] == "paid") {
        sendmessage($from_id, "❗️ تراکنش شما توسط ربات تایید گردیده است.", null, 'HTML');
        rf_stop();
    }
    if ($payemntcheck['payment_Status'] == "expire") {
        sendmessage($from_id, "❗زمان این تراکنش به پایان رسیده و امکان پرداخت این تراکنش وجود ندارد.", null, 'HTML');
        rf_stop();
    }
    deletemessage($from_id, $message_id);
    sendmessage($from_id, "📌 تصویر واریزی خود یا لینک تراکنش ترون را ارسال نمایید.", $backuser, 'HTML');
    step('getresidcurrency', $from_id);
    update("user", "Processing_value", $dataget[1], "id", $from_id);
}
if (!$rf_chain4_handled && ($user['step'] == "getresidcurrency")) {
    $rf_chain4_handled = true;
    $format_balance = number_format($user['Balance'], 0);
    step('home', $from_id);
    $PaymentReport = select("Payment_report", "*", "id_order", $user['Processing_value'], "select");
    $Paymentusercount = select("Payment_report", "*", "id_user", $PaymentReport['id_user'], "count");
    if ($PaymentReport == false) {
        sendmessage($from_id, "❌ خطایی رخ داده است لطفا مراحل خرید یا پرداخت  را مجدد انجام دهید", $keyboard, 'HTML');
        rf_stop();
    }
    $Confirm_pay = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['Balance']['Confirmpaying'], 'callback_data' => "Confirm_pay_{$PaymentReport['id_order']}"],
                ['text' => $textbotlang['users']['Balance']['reject_pay'], 'callback_data' => "reject_pay_{$PaymentReport['id_order']}"],
            ],
            [
                ['text' => $textbotlang['users']['Balance']['addbalamceuser'], 'callback_data' => "addbalamceuser_{$PaymentReport['id_order']}"],
                ['text' => $textbotlang['users']['Balance']['blockedfake'], 'callback_data' => "blockuserfake_{$PaymentReport['id_user']}"],
            ]
        ]
    ]);
    $textdiscount = "";
    $format_price_cart = number_format($PaymentReport['price'], 0);
    if ($user['Processing_value_tow'] == "getconfigafterpay") {
        $get_invoice = select("invoice", "*", "username", $user['Processing_value_one'], "select");
        if ($get_invoice == false) {
            sendmessage($from_id, "❌ خطایی رخ داده است لطفا مراحل خرید یا پرداخت  را مجدد انجام دهید", $keyboard, 'HTML');
            rf_stop();
        }
        $textsendrasid = "
⭕️ یک پرداخت جدید انجام شده است .

⭕️⭕️⭕️⭕️⭕️
خرید سرویس جدید

نام کاربری سرویس : {$get_invoice['username']}
نام محصول : {$get_invoice['name_product']}
حجم محصول : {$get_invoice['Volume']} گیگ 
زمان محصول : {$get_invoice['Service_time']} روز
👤 نام اکانت کاربر : $first_name
👤 شناسه کاربر:  <a href = \"tg://user?id=$from_id\">$from_id</a>
💸 موجودی فعلی کاربر : $format_balance تومان
🛒 کد پیگیری پرداخت: {$PaymentReport['id_order']}
⚜️ نام کاربری: @$username
💵 تعداد کل پرداختی های کاربر : $Paymentusercount عدد
💸 مبلغ پرداختی: $format_price_cart تومان

                
توضیحات: $caption $text
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
    } elseif ($user['Processing_value_tow'] == "getextenduser") {
        $partsdic = explode("%", $user['Processing_value_one']);
        $usernamepanel = $partsdic[0];
        $sql = "SELECT * FROM service_other WHERE username = :username  AND value  LIKE CONCAT('%', :value, '%') AND id_user = :id_user ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $usernamepanel, PDO::PARAM_STR);
        $stmt->bindParam(':value', $partsdic[1], PDO::PARAM_STR);
        $stmt->bindParam(':id_user', $from_id);
        $stmt->execute();
        $service_other = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($service_other == false) {
            sendmessage($from_id, '❌ خطایی در هنگام دریافت اطلاعات رخ داده است لطفا مراحل را از اول انجام دهید', $keyboard, 'HTML');
            rf_stop();
        }
        $service_other = json_decode($service_other['value'], true);
        $nameloc = select("invoice", "*", "username", $usernamepanel, "select");
        $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
        $eextraprice = json_decode($marzban_list_get['pricecustomvolume'], true);
        $custompricevalue = $eextraprice[$user['agent']];
        $eextraprice = json_decode($marzban_list_get['pricecustomtime'], true);
        $customtimevalueprice = $eextraprice[$user['agent']];
        $codeproduct = $service_other['code_product'];
        if ($codeproduct == "custom_volume") {
            $prodcut['code_product'] = "custom_volume";
            $prodcut['name_product'] = $nameloc['name_product'];
            $prodcut['price_product'] = ($service_other['volumebuy'] * $custompricevalue) + ($nameloc['Service_time'] * $customtimevalueprice);
            $prodcut['Service_time'] = $service_other['Service_time'];
            $prodcut['Volume_constraint'] = $service_other['volumebuy'];
        } else {
            $nameloc = select("invoice", "*", "username", $usernamepanel, "select");
            $prodcut = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE (Location = '{$nameloc['Service_location']}' OR Location = '/all') AND code_product = '$codeproduct'"));
        }
        $Confirm_pay = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['Confirmpaying'], 'callback_data' => "Confirm_pay_{$PaymentReport['id_order']}"],
                    ['text' => $textbotlang['users']['Balance']['reject_pay'], 'callback_data' => "reject_pay_{$PaymentReport['id_order']}"],
                ],
                [
                    ['text' => $textbotlang['users']['Balance']['addbalamceuser'], 'callback_data' => "addbalamceuser_{$PaymentReport['id_order']}"],
                    ['text' => $textbotlang['users']['Balance']['blockedfake'], 'callback_data' => "blockuserfake_{$PaymentReport['id_user']}"],
                ],
                [
                    ['text' => "⚙️ اطلاعات کانفیگ", 'callback_data' => "manageinvoice_{$nameloc['id_invoice']}"],
                ]
            ]
        ]);
        $textsendrasid = "
⭕️ یک پرداخت جدید انجام شده است .

⭕️⭕️⭕️⭕️⭕️
تمدید
نام کاربری سرویس : $usernamepanel
نام محصول : {$prodcut['name_product']}
👤 نام اکانت کاربر : $first_name
👤 شناسه کاربر:  <a href = \"tg://user?id=$from_id\">$from_id</a>
💸 موجودی فعلی کاربر : $format_balance تومان
🛒 کد پیگیری پرداخت: {$PaymentReport['id_order']}
⚜️ نام کاربری: @$username
💵 تعداد کل پرداختی های کاربر : $Paymentusercount عدد
💸 مبلغ پرداختی: $format_price_cart تومان
                
توضیحات: $caption $text
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
    } elseif ($user['Processing_value_tow'] == "getextravolumeuser") {
        $partsdic = explode("%", $user['Processing_value_one']);
        $usernamepanel = $partsdic[0];
        $volumes = $partsdic[1];
        $textsendrasid = "
⭕️ یک پرداخت جدید انجام شده است .

⭕️⭕️⭕️⭕️⭕️
خرید حجم اضافه
نام کاربری سرویس : $usernamepanel
حجم خریداری شده  : $volumes
👤 نام اکانت کاربر : $first_name
👤 شناسه کاربر:  <a href = \"tg://user?id=$from_id\">$from_id</a>
💸 موجودی فعلی کاربر : $format_balance تومان
🛒 کد پیگیری پرداخت: {$PaymentReport['id_order']}
⚜️ نام کاربری: @$username
💵 تعداد کل پرداختی های کاربر : $Paymentusercount عدد
💸 مبلغ پرداختی: $format_price_cart تومان
                
توضیحات: $caption $text
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
    } elseif ($user['Processing_value_tow'] == "getextratimeuser") {
        $partsdic = explode("%", $user['Processing_value_one']);
        $usernamepanel = $partsdic[0];
        $time = $partsdic[1];
        $textsendrasid = "
⭕️ یک پرداخت جدید انجام شده است .

⭕️⭕️⭕️⭕️⭕️
خرید زمان اضافه
نام کاربری سرویس : $usernamepanel
تعداد روز خریداری شده  : $time
👤 نام اکانت کاربر : $first_name
👤 شناسه کاربر:  <a href = \"tg://user?id=$from_id\">$from_id</a>
💸 موجودی فعلی کاربر : $format_balance تومان
🛒 کد پیگیری پرداخت: {$PaymentReport['id_order']}
⚜️ نام کاربری: @$username
💵 تعداد کل پرداختی های کاربر : $Paymentusercount عدد
💸 مبلغ پرداختی: $format_price_cart تومان
                
توضیحات: $caption $text
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
    } else {

        $textsendrasid = "
⭕️ یک پرداخت جدید انجام شده است .
افزایش موجودی            
👤 نام اکانت کاربر : $first_name
👤 شناسه کاربر:  <a href = \"tg://user?id=$from_id\">$from_id</a>
💸 موجودی فعلی کاربر : $format_balance تومان
🛒 کد پیگیری پرداخت: {$PaymentReport['id_order']}
⚜️ نام کاربری: @$username
💵 تعداد کل پرداختی های کاربر : $Paymentusercount عدد
💸 مبلغ پرداختی: $format_price_cart تومان
                
توضیحات: $caption $text
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
    }
    foreach ($admin_ids as $id_admin) {
        $adminrulecheck = select("admin", "*", "id_admin", $id_admin, "select");
        if ($adminrulecheck['rule'] == "support")
            continue;
        if ($photo) {
            telegram('sendphoto', [
                'chat_id' => $id_admin,
                'photo' => $photoid,
                'caption' => $textbotlang['users']['Balance']['receiptimage'],
                'parse_mode' => "HTML",
            ]);
        }
        sendmessage($id_admin, $textsendrasid, $Confirm_pay, 'HTML');
    }
    if ($user['Processing_value_tow'] == "getconfigafterpay") {
        sendmessage($from_id, $textbotlang['users']['Balance']['Send-receiptadnsendconfig'], $keyboard, 'HTML');
    } else {
        sendmessage($from_id, $textbotlang['users']['Balance']['Send-receipt'], $keyboard, 'HTML');
    }
    update("Payment_report", "payment_Status", "waiting", "id_order", $PaymentReport['id_order']);
    update("Payment_report", "dec_not_confirmed", "$text $caption", "id_order", $PaymentReport['id_order']);
    $dateacc = date('Y/m/d H:i:s');
    update("Payment_report", "at_updated", $dateacc, "id_order", $PaymentReport['id_order']);
}
if (!$rf_chain4_handled && ($user['step'] == "cart_to_cart_user")) {
    $rf_chain4_handled = true;
    $format_balance = number_format($user['Balance'], 0);
    if (!$photo or isset($update['message']['media_group_id'])) {
        sendmessage($from_id, "❌  فقط مجاز به ارسال یک تصویر هستید", null, 'HTML');
        rf_stop();
    }
    step('home', $from_id);
    $PaymentReport = select("Payment_report", "*", "id_order", $user['Processing_value']);
    if ($PaymentReport == false) {
        sendmessage($from_id, '❌ خطایی در هنگام دریافت اطلاعات رخ داده است لطفا مراحل را از اول انجام دهید', $keyboard, 'HTML');
        rf_stop();
    }
    $Confirm_pay = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['Balance']['Confirmpaying'], 'callback_data' => "Confirm_pay_{$PaymentReport['id_order']}"],
                ['text' => $textbotlang['users']['Balance']['reject_pay'], 'callback_data' => "reject_pay_{$PaymentReport['id_order']}"],
            ],
            [
                ['text' => $textbotlang['users']['Balance']['addbalamceuser'], 'callback_data' => "addbalamceuser_{$PaymentReport['id_order']}"],
                ['text' => $textbotlang['users']['Balance']['blockedfake'], 'callback_data' => "blockuserfake_{$PaymentReport['id_user']}"],
            ]
        ]
    ]);
    $format_price_cart = number_format($PaymentReport['price'], 0);
    $split_data = explode('|', $PaymentReport['id_invoice']);
    if ($split_data[0] == "getconfigafterpay") {
        $get_invoice = select("invoice", "*", "username", $split_data[1], "select");
        if ($get_invoice == false) {
            sendmessage($from_id, "❌ خطایی رخ داده است لطفا مراحل خرید یا پرداخت  را مجدد انجام دهید", $keyboard, 'HTML');
            rf_stop();
        }
        $textdiscount = "";
        $textsendrasid = "
⭕️ یک پرداخت جدید انجام شده است .

⭕️⭕️⭕️⭕️⭕️
خرید سرویس جدید
نام کاربری سرویس  : {$get_invoice['username']}
نام محصول : {$get_invoice['name_product']}
حجم محصول : {$get_invoice['Volume']} گیگ
زمان محصول : {$get_invoice['Service_time']} روز
👤 نام اکانت کاربر : $first_name
👤 شناسه کاربر:  <a href = \"tg://user?id=$from_id\">$from_id</a>
💸 موجودی فعلی کاربر : $format_balance تومان
🛒 کد پیگیری پرداخت: {$PaymentReport['id_order']}
⚜️ نام کاربری: @$username
💸 مبلغ پرداختی: $format_price_cart تومان
                
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
        sendmessage($from_id, $textbotlang['users']['Balance']['Send-receiptadnsendconfig'], $keyboard, 'HTML');
    } elseif ($split_data[0] == "getextenduser") {
        $partsdic = explode("%", $split_data[1]);
        $usernamepanel = $partsdic[0];
        $sql = "SELECT * FROM service_other WHERE username = :username  AND value  LIKE CONCAT('%', :value, '%') AND id_user = :id_user ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $usernamepanel, PDO::PARAM_STR);
        $stmt->bindParam(':value', $partsdic[1], PDO::PARAM_STR);
        $stmt->bindParam(':id_user', $from_id);
        $stmt->execute();
        $service_other = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($service_other == false) {
            sendmessage($from_id, '❌ خطایی در هنگام دریافت اطلاعات رخ داده است لطفا مراحل را از اول انجام دهید', $keyboard, 'HTML');
            rf_stop();
        }
        $service_other = json_decode($service_other['value'], true);
        $nameloc = select("invoice", "*", "username", $usernamepanel, "select");
        $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
        $eextraprice = json_decode($marzban_list_get['pricecustomvolume'], true);
        $custompricevalue = $eextraprice[$user['agent']];
        $eextraprice = json_decode($marzban_list_get['pricecustomtime'], true);
        $customtimevalueprice = $eextraprice[$user['agent']];
        $codeproduct = $service_other['code_product'];
        if ($codeproduct == "custom_volume") {
            $prodcut['code_product'] = "custom_volume";
            $prodcut['name_product'] = $nameloc['name_product'];
            $prodcut['price_product'] = ($service_other['volumebuy'] * $custompricevalue) + ($service_other['Service_time'] * $customtimevalueprice);
            $prodcut['Service_time'] = $service_other['Service_time'];
            $prodcut['Volume_constraint'] = $service_other['volumebuy'];
        } else {
            $nameloc = select("invoice", "*", "username", $usernamepanel, "select");
            $prodcut = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM product WHERE (Location = '{$nameloc['Service_location']}' OR Location = '/all') AND code_product = '$codeproduct'"));
        }
        $Confirm_pay = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['Balance']['Confirmpaying'], 'callback_data' => "Confirm_pay_{$PaymentReport['id_order']}"],
                    ['text' => $textbotlang['users']['Balance']['reject_pay'], 'callback_data' => "reject_pay_{$PaymentReport['id_order']}"],
                ],
                [
                    ['text' => $textbotlang['users']['Balance']['addbalamceuser'], 'callback_data' => "addbalamceuser_{$PaymentReport['id_order']}"],
                    ['text' => $textbotlang['users']['Balance']['blockedfake'], 'callback_data' => "blockuserfake_{$PaymentReport['id_user']}"],
                ],
                [
                    ['text' => "⚙️ اطلاعات کانفیگ", 'callback_data' => "manageinvoice_{$nameloc['id_invoice']}"],
                ]
            ]
        ]);
        $textsendrasid = "
⭕️ یک پرداخت جدید انجام شده است .

⭕️⭕️⭕️⭕️⭕️
تمدید
نام کاربری سرویس : $usernamepanel
نام محصول : {$prodcut['name_product']}
👤 نام اکانت کاربر : $first_name
👤 شناسه کاربر:  <a href = \"tg://user?id=$from_id\">$from_id</a>
💸 موجودی فعلی کاربر : $format_balance تومان
🛒 کد پیگیری پرداخت: {$PaymentReport['id_order']}
⚜️ نام کاربری: @$username
💸 مبلغ پرداختی: $format_price_cart تومان
                
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
        sendmessage($from_id, "🚀 رسید شما ارسال و پس از بررسی سرویس شما تمدید خواهد شد", $keyboard, 'HTML');
    } elseif ($split_data[0] == "getextravolumeuser") {
        $partsdic = explode("%", $split_data[1]);
        $usernamepanel = $partsdic[0];
        $volumes = $partsdic[1];
        $textsendrasid = "
⭕️ یک پرداخت جدید انجام شده است .

⭕️⭕️⭕️⭕️⭕️
خرید حجم اضافه
نام کاربری سرویس : $usernamepanel
حجم خریداری شده  : $volumes
👤 نام اکانت کاربر : $first_name
👤 شناسه کاربر:  <a href = \"tg://user?id=$from_id\">$from_id</a>
💸 موجودی فعلی کاربر : $format_balance تومان
🛒 کد پیگیری پرداخت: {$PaymentReport['id_order']}
⚜️ نام کاربری: @$username
💸 مبلغ پرداختی: $format_price_cart تومان
                
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
        sendmessage($from_id, "🚀 رسید شما ارسال و پس از بررسی  به سرویس شما حجم اضافه خواهد شد.", $keyboard, 'HTML');
    } elseif ($split_data[0] == "getextratimeuser") {
        $partsdic = explode("%", $split_data[1]);
        $usernamepanel = $partsdic[0];
        $time = $partsdic[1];
        $textsendrasid = "
⭕️ یک پرداخت جدید انجام شده است .

⭕️⭕️⭕️⭕️⭕️
خرید زمان اضافه
نام کاربری سرویس : $usernamepanel
تعداد روز خریداری شده  : $time
👤 نام اکانت کاربر : $first_name
👤 شناسه کاربر:  <a href = \"tg://user?id=$from_id\">$from_id</a>
💸 موجودی فعلی کاربر : $format_balance تومان
🛒 کد پیگیری پرداخت: {$PaymentReport['id_order']}
⚜️ نام کاربری: @$username
💸 مبلغ پرداختی: $format_price_cart تومان
                
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
        sendmessage($from_id, "🚀 رسید شما ارسال و پس از بررسی به سرویس شما زمان اضافه خواهد شد", $keyboard, 'HTML');
    } else {

        $textsendrasid = "
⭕️ یک پرداخت جدید انجام شده است .
افزایش موجودی            
👤 نام اکانت کاربر : $first_name
👤 شناسه کاربر:  <a href = \"tg://user?id=$from_id\">$from_id</a>
💸 موجودی فعلی کاربر : $format_balance تومان
🛒 کد پیگیری پرداخت: {$PaymentReport['id_order']}
⚜️ نام کاربری: @$username
💸 مبلغ پرداختی: $format_price_cart تومان
                
✍️ در صورت درست بودن رسید پرداخت را تایید نمایید.";
        sendmessage($from_id, $textbotlang['users']['Balance']['Send-receipt'], $keyboard, 'HTML');
    }
    foreach ($admin_ids as $id_admin) {
        $adminrulecheck = select("admin", "*", "id_admin", $id_admin, "select");
        if ($adminrulecheck['rule'] == "support")
            continue;
        telegram('sendphoto', [
            'chat_id' => $id_admin,
            'photo' => $photoid,
            'caption' => $caption,
            'parse_mode' => "HTML",
        ]);
        sendmessage($id_admin, $textsendrasid, $Confirm_pay, 'HTML');
    }
    update("Payment_report", "payment_Status", "waiting", "id_order", $PaymentReport['id_order']);
    $dateacc = date('Y/m/d H:i:s');
    update("Payment_report", "at_updated", $dateacc, "id_order", $PaymentReport['id_order']);
}
if (!$rf_chain4_handled && ($datain == "Discount")) {
    $rf_chain4_handled = true;
    $bakinfos = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "account"],
            ]
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textbotlang['users']['Discount']['getcode'], $bakinfos);
    step('get_code_user', $from_id);
}
if (!$rf_chain4_handled && ($user['step'] == "get_code_user")) {
    $rf_chain4_handled = true;
    if (!in_array($text, $code_Discount)) {
        sendmessage($from_id, $textbotlang['users']['Discount']['notcode'], null, 'HTML');
        rf_stop();
    }
    $checklimit = select("Discount", "*", "code", $text, "select");
    if ($checklimit['limitused'] >= $checklimit['limituse']) {
        sendmessage($from_id, $textbotlang['users']['Discount']['erorrlimitdiscount'], $backuser, 'HTML');
        rf_stop();
    }
    $stmt = $pdo->prepare("SELECT * FROM Giftcodeconsumed WHERE id_user = :from_id AND code = :code");
    $stmt->bindParam(':from_id', $from_id, PDO::PARAM_STR);
    $stmt->bindParam(':code', $text, PDO::PARAM_STR);
    $stmt->execute();
    $Checkcodesql = $stmt->rowCount();
    if ($Checkcodesql != 0) {
        sendmessage($from_id, $textbotlang['users']['Discount']['giftcodeonce'], $keyboard, 'HTML');
        step('home', $from_id);
        rf_stop();
    }
    $stmt = $pdo->prepare("SELECT * FROM Discount WHERE code = :code LIMIT 1");
    $stmt->bindParam(':code', $text);
    $stmt->execute();
    $get_codesql = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance_user = $user['Balance'] + $get_codesql['price'];
    update("user", "Balance", $balance_user, "id", $from_id);
    $discountlimitadd = intval($checklimit['limitused']) + 1;
    update("Discount", "limitused", $discountlimitadd, "code", $text);
    step('home', $from_id);
    $text_balance_code = sprintf($textbotlang['users']['Discount']['giftcodesuccess'], $get_codesql['price']);
    sendmessage($from_id, $text_balance_code, $keyboard, 'HTML');
    $stmt = $pdo->prepare("INSERT INTO Giftcodeconsumed (id_user, code) VALUES (:id_user, :code)");
    $stmt->execute([
        ':id_user' => $from_id,
        ':code' => $text,
    ]);
    $text_report = sprintf($textbotlang['users']['Discount']['giftcodeused'], $username, $from_id, $text);
    if (strlen($setting['Channel_Report'] ?? '') > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherreport,
            'text' => $text_report,
            'parse_mode' => "HTML"
        ]);
    }
}
if (!$rf_chain4_handled && ($text == $datatextbot['text_Tariff_list'] || $datain == "Tariff_list")) {
    $rf_chain4_handled = true;
    sendmessage($from_id, $datatextbot['text_dec_Tariff_list'], null, 'HTML');
}
if (!$rf_chain4_handled && ($datain == "colselist")) {
    $rf_chain4_handled = true;
    deletemessage($from_id, $message_id);
    sendmessage($from_id, $textbotlang['users']['back'], $keyboard, 'HTML');
}
