<?php
rf_set_module('admin/routes/20_step_updateextendmethod__onautoconfirm__offautoconfirm.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "updateextendmethod")) {
    $rf_admin_handled = true;

    $aarayvalid = array(
        'ریست حجم و زمان',
        'اضافه شدن زمان و حجم به ماه بعد',
        'ریست زمان و اضافه کردن حجم قبلی',
        'ریست شدن حجم و اضافه شدن زمان',
        'اضافه شدن زمان و تبدیل حجم کل به حجم باقی مانده'
    );
    if (!in_array($text, $aarayvalid)) {
        sendmessage($from_id, "❌ روش تمدید نامعتبر می باشد از لیست زیر روش تمدید درست را انتخاب کنید", null, 'HTML');
        return;
    }
    update("marzban_panel", "Methodextend", $text, "name_panel", $user['Processing_value']);
    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['Algortimeextend']['SaveData']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "♻️ تایید خودکار رسید" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $paymentverify = select("PaySetting", "ValuePay", "NamePay", "autoconfirmcart", "select")['ValuePay'];
    if ($paymentverify == "onauto") {
        sendmessage($from_id, "❌ ابتدا تایید خودکار بدون بررسی را خاموش کنید.", null, 'HTML');
        return;
    }
    $PaySetting = select("PaySetting", "ValuePay", "NamePay", "statuscardautoconfirm", "select")['ValuePay'];
    $card_Status_auto = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $PaySetting, 'callback_data' => $PaySetting],
            ],
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['autoconfirmcard'], $card_Status_auto, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "onautoconfirm" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", "offautoconfirm", "NamePay", "statuscardautoconfirm");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['cardStatusOffautoconfirmcard'], null);
    return;
}

if (!$rf_admin_handled && ($datain == "offautoconfirm" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    update("PaySetting", "ValuePay", "onautoconfirm", "NamePay", "statuscardautoconfirm");
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['cardStatusonautoconfirmcard'], null);
    return;
}

if (!$rf_admin_handled && ($text == "/token")) {
    $rf_admin_handled = true;

    $secret_key = select("admin", "*", "id_admin", $from_id, "select");
    $secret_key = base64_encode($secret_key['password']);
    sendmessage($from_id, "<code>$secret_key</code>", null, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "/token2")) {
    $rf_admin_handled = true;

    $token = bin2hex(random_bytes(16));
    file_put_contents('api/hash.txt', $token);
    sendmessage($from_id, "توکن api شما : <code>$token</code>", null, 'HTML');
    sendDocument($from_id, 'api/documents.txt', "📌 داکیومنت api ربات 
نکات : 
۱ - در صورتی که به endpoint خاصی نیاز داشتید به اکانت پشتیبانی پیام دهید تا بررسی شود.");
    return;
}

if (!$rf_admin_handled && ($text == "✅ فعالسازی پنل تحت وب" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $admin_select = select("admin", "*", "id_admin", $from_id, "select");
    $randomString = bin2hex(random_bytes(6));
    update("admin", "username", $from_id, "id_admin", $from_id);
    if ($admin_select['password'] == null) {
        update("admin", "password", $randomString, "id_admin", $from_id);
    } else {
        $randomString = $admin_select['password'];
    }
    $keyboardstatistics = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "تنظیم آیپی ورود", 'callback_data' => 'iploginset'],
            ],
        ]
    ]);
    sendmessage($from_id, "✅  پنل تحت وب شما با موفقیت فعال گردید.


🔗آدرس ورود : https://$domainhosts/panel
👤نام کاربری :  <code>$from_id</code>
🔑رمز عبور :  <code>$randomString</code>", $keyboardstatistics, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/addordermanualـ(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    update("user", "Processing_value", $iduser, "id", $from_id);
    sendmessage($from_id, $textbotlang['Admin']['addorder']['towstep'], $backadmin, 'HTML');
    step('getusernameconfig', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getusernameconfig")) {
    $rf_admin_handled = true;

    $text = strtolower($text);
    if (!preg_match('/^\w{3,32}$/', $text)) {
        sendmessage($from_id, $textbotlang['users']['stateus']['Invalidusername'], $backuser, 'html');
        return;
    }
    if (in_array($text, $usernameinvoice)) {
        sendmessage($from_id, "❌ این نام کاربری از قبل داخل ربات وجود دارد.", null, 'HTML');
        return;
    }
    update("user", "Processing_value_one", $text, "id", $from_id);
    sendmessage($from_id, $textbotlang['Admin']['addorder']['threestep'], $json_list_marzban_panel, 'HTML');
    step('getnamepanelconfig', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnamepanelconfig")) {
    $rf_admin_handled = true;

    update("user", "Processing_value_tow", $text, "id", $from_id);
    sendmessage($from_id, $textbotlang['Admin']['addorder']['fourstep'], $json_list_product_list_admin, 'HTML');
    step('stependforaddorder', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "stependforaddorder")) {
    $rf_admin_handled = true;

    $sql = "SELECT * FROM product  WHERE name_product = :name_product AND (Location = :location OR Location = '/all') LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name_product', $text, PDO::PARAM_STR);
    $stmt->bindParam(':location', $user['Processing_value_tow'], PDO::PARAM_STR);
    $stmt->execute();
    $info_product = $stmt->fetch(PDO::FETCH_ASSOC);
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $user['Processing_value_tow'], "select");
    $DataUserOut = $ManagePanel->DataUser($user['Processing_value_tow'], $user['Processing_value_one']);
    if ($DataUserOut['status'] == "Unsuccessful") {
        $datetimestep = strtotime("+" . $info_product['Service_time'] . "days");
        if ($info_product['Service_time'] == 0) {
            $datetimestep = 0;
        } else {
            $datetimestep = strtotime(date("Y-m-d H:i:s", $datetimestep));
        }
        $datac = array(
            'expire' => $datetimestep,
            'data_limit' => $info_product['Volume_constraint'] * pow(1024, 3),
            'from_id' => $user['Processing_value'],
            'username' => "",
            'type' => 'buy'
        );
        $DataUserOut = $ManagePanel->createUser($user['Processing_value_tow'], $info_product['code_product'], $user['Processing_value_one'], $datac);
        if ($DataUserOut['username'] == null) {
            sendmessage($from_id, "❌ خطایی در ساخت اشتراک رخ داده است برای رفع مشکل علت خطا را در گروه گزارش تان بررسی کنید", null, 'HTML');
            $DataUserOut['msg'] = json_encode($DataUserOut['msg']);
            $texterros = "
خطا در ساخت کافنیگ از پنل ادمین
✍️ دلیل خطا : 
{$DataUserOut['msg']}
آیدی ادمین : $from_id
نام پنل : {$marzban_list_get['name_panel']}";
            if (strlen($setting['Channel_Report']) > 0) {
                telegram('sendmessage', [
                    'chat_id' => $setting['Channel_Report'],
                    'message_thread_id' => $errorreport,
                    'text' => $texterros,
                    'parse_mode' => "HTML"
                ]);
                step("home", $from_id);
            }
            return;
        }
    } else {
        $DataUserOut['configs'] = $DataUserOut['links'];
    }
    $date = time();
    $randomString = bin2hex(random_bytes(4));
    $notifctions = json_encode(array(
        'volume' => false,
        'time' => false,
    ));
    $stmt = $pdo->prepare("INSERT IGNORE INTO invoice (id_user, id_invoice, username, time_sell, Service_location, name_product, price_product, Volume, Service_time, Status,notifctions) VALUES (:id_user, :id_invoice, :username, :time_sell, :Service_location, :name_product, :price_product, :Volume, :Service_time, :Status,:notifctions)");
    $Status = "active";
    $stmt->bindParam(':id_user', $user['Processing_value'], PDO::PARAM_STR);
    $stmt->bindParam(':id_invoice', $randomString, PDO::PARAM_STR);
    $stmt->bindParam(':username', $user['Processing_value_one'], PDO::PARAM_STR);
    $stmt->bindParam(':time_sell', $date, PDO::PARAM_STR);
    $stmt->bindParam(':Service_location', $user['Processing_value_tow'], PDO::PARAM_STR);
    $stmt->bindParam(':name_product', $info_product['name_product'], PDO::PARAM_STR);
    $stmt->bindParam(':price_product', $info_product['price_product'], PDO::PARAM_STR);
    $stmt->bindParam(':Volume', $info_product['Volume_constraint'], PDO::PARAM_STR);
    $stmt->bindParam(':Service_time', $info_product['Service_time'], PDO::PARAM_STR);
    $stmt->bindParam(':Status', $Status, PDO::PARAM_STR);
    $stmt->bindParam(':notifctions', $notifctions, PDO::PARAM_STR);
    $stmt->execute();
    $output_config_link = $marzban_list_get['sublink'] == "onsublink" ? $DataUserOut['subscription_url'] : "";
    $config = "";
    if ($marzban_list_get['config'] == "onconfig" && is_array($DataUserOut['configs'])) {
        foreach ($DataUserOut['configs'] as $link) {
            $config .= "\n" . $link;
        }
    }
    $Shoppinginfo = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['help']['btninlinebuy'], 'callback_data' => "helpbtn"],
            ]
        ]
    ]);
    $datatextbot['textafterpay'] = $marzban_list_get['type'] == "Manualsale" ? $datatextbot['textmanual'] : $datatextbot['textafterpay'];
    $datatextbot['textafterpay'] = $marzban_list_get['type'] == "WGDashboard" ? $datatextbot['text_wgdashboard'] : $datatextbot['textafterpay'];
    $datatextbot['textafterpay'] = $marzban_list_get['type'] == "ibsng" || $marzban_list_get['type'] == "mikrotik" ? $datatextbot['textafterpayibsng'] : $datatextbot['textafterpay'];
    if (intval($info_product['Service_time']) == 0)
        $info_product['Service_time'] = $textbotlang['users']['stateus']['Unlimited'];
    if (intval($info_product['Volume_constraint']) == 0)
        $info_product['Volume_constraint'] = $textbotlang['users']['stateus']['Unlimited'];
    $textcreatuser = str_replace('{username}', "<code>{$DataUserOut['username']}</code>", $datatextbot['textafterpay']);
    $textcreatuser = str_replace('{name_service}', $info_product['name_product'], $textcreatuser);
    $textcreatuser = str_replace('{location}', $marzban_list_get['name_panel'], $textcreatuser);
    $textcreatuser = str_replace('{day}', $info_product['Service_time'], $textcreatuser);
    $textcreatuser = str_replace('{volume}', $info_product['Volume_constraint'], $textcreatuser);
    $textcreatuser = applyConnectionPlaceholders($textcreatuser, $output_config_link, $config);
    if (intval($info_product['Volume_constraint']) == 0) {
        $textcreatuser = str_replace('گیگابایت', "", $textcreatuser);
    }
    if ($marzban_list_get['type'] == "Manualsale" || $marzban_list_get['type'] == "ibsng" || $marzban_list_get['type'] == "mikrotik") {
        $textcreatuser = str_replace('{password}', $DataUserOut['subscription_url'], $textcreatuser);
        update("invoice", "user_info", $DataUserOut['subscription_url'], "id_invoice", $randomString);
    }
    sendMessageService($marzban_list_get, $DataUserOut['configs'], $output_config_link, $DataUserOut['username'], $Shoppinginfo, $textcreatuser, $randomString, $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['addorder']['fivestep'], $keyboardadmin, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل موجودی خرید عمده" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $PaySetting = select("shopSetting", "value", "Namevalue", "minbalancebuybulk", "select")['value'];
    $textmin = "📌 حداقل مبلغی که می خواهید کاربر  خرید انبوه کند را ارسال کنید.
        
مبلغ فعلی : $PaySetting";
    sendmessage($from_id, $textmin, $backadmin, 'HTML');
    step('minbalancebulk', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "minbalancebulk")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['SettingnowPayment']['Savaapi'], $shopkeyboard, 'HTML');
    update("shopSetting", "value", $text, "Namevalue", "minbalancebuybulk");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/showcarduser-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_user = $dataget[1];
    sendmessage($id_user, "💳 کاربر عزیز شماره کارت برای شما فعال شد هم اکنون می توانید خرید خود را انجام دهید.", null, 'HTML');
    sendmessage($from_id, "✅  شماره کارت فعال گردید", null, 'HTML');
    update("user", "cardpayment", "1", "id", $id_user);
    return;
}

if (!$rf_admin_handled && (preg_match('/carduserhide-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_user = $dataget[1];
    sendmessage($from_id, "✅  شماره کارت غیرفعال گردید", null, 'HTML');
    update("user", "cardpayment", "0", "id", $id_user);
    return;
}

if (!$rf_admin_handled && ($text == "❌ حذف شماره کارت" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 شماره کارتی که می خواهید حذف کنید را ارسال نمایید.", $list_card_remove, 'HTML');
    step('getcardremove', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcardremove")) {
    $rf_admin_handled = true;

    $stmt = $pdo->prepare("DELETE FROM card_number WHERE cardnumber = :cardnumber");
    $stmt->bindParam(':cardnumber', $text, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, "✅ شماره کارت با موفقیت حذف گردید.", $CartManage, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/rejectrequesta_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    $request_agent = select("Requestagent", "*", "id", $id_user, "select");

    if (!$request_agent) {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "درخواست مورد نظر یافت نشد.",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }

    if ($request_agent['status'] == "reject" || $request_agent['status'] == "accept") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "این درخواست توسط ادمین دیگری بررسی شده است",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        return;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE Requestagent SET status = :status, type = :type WHERE id = :id AND status = :expected_status");
        $stmt->execute([
            ':status' => 'reject',
            ':type' => 'None',
            ':id' => $id_user,
            ':expected_status' => 'waiting',
        ]);

        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            telegram('answerCallbackQuery', array(
                'callback_query_id' => $callback_query_id,
                'text' => "این درخواست توسط ادمین دیگری بررسی شده است",
                'show_alert' => true,
                'cache_time' => 5,
            ));
            return;
        }

        $stmtBalance = $pdo->prepare("UPDATE user SET Balance = Balance + :amount WHERE id = :id");
        $stmtBalance->execute([
            ':amount' => intval($setting['agentreqprice']),
            ':id' => $id_user,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    $keyboardreject = json_encode([
        'inline_keyboard' => [
            [['text' => "✅درخواست رد شده.", 'callback_data' => "reject"]],
        ]
    ]);
    sendmessage($from_id, "✅ درخواست با موفقیت رد گردید.", null, 'HTML');
    sendmessage($id_user, "❌ کاربر گرامی درخواست نمایندگی شما رد گردید.", null, 'HTML');
    $textrequestagent = "📣 یک کاربر درخواست نمایندگی ثبت کرده لطفا اطلاعات را بررسی و وضعیت را مشخص کنید.\n\nآیدی عددی : $id_user\nنام کاربری : {$request_agent['username']}\nتوضیحات :  {$request_agent['Description']} ";
    $textrequestagent .= "\nوضعیت: رد شد.";
    Editmessagetext($from_id, $message_id, $textrequestagent, $keyboardreject);
    telegram('answerCallbackQuery', array(
        'callback_query_id' => $callback_query_id,
        'text' => "درخواست با موفقیت رد شد.",
        'show_alert' => false,
        'cache_time' => 5,
    ));
    return;
}

