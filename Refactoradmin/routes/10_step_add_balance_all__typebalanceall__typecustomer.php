<?php
rf_set_module('admin/routes/10_step_add_balance_all__typebalanceall__typecustomer.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "add_Balance_all")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    step("home", $from_id);
    savedata("clear", "price", $text);
    $keyboardagent = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "همه کاربران", 'callback_data' => 'typebalanceall_all'],
            ],
            [
                ['text' => "کاربران گروه f", 'callback_data' => 'typebalanceall_f'],
                ['text' => "کاربران گروه n", 'callback_data' => 'typebalanceall_nl'],
                ['text' => "کاربران گروه n2", 'callback_data' => 'typebalanceall_n2'],
            ],
            [
                ['text' => "بازگشت به منوی اصلی", 'callback_data' => 'backuser'],
            ]
        ]
    ]);
    sendmessage($from_id, "📌 شارژ برای کدام یک از گروه کاربری زیر واریز شود.", $keyboardagent, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/typebalanceall_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $typeagent = $dataget[1];
    savedata("save", "agent", $typeagent);
    $keyboardtypeuser = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "همه کاربران", 'callback_data' => 'typecustomer_all'],
            ],
            [
                ['text' => "کاربرانی که خرید داشتند", 'callback_data' => 'typecustomer_customer'],
            ],
            [
                ['text' => "کاربرانی که خرید نداشتند", 'callback_data' => 'typecustomer_notcustomer'],
            ],
            [
                ['text' => "بازگشت به منوی اصلی", 'callback_data' => 'backuser'],
            ]
        ]
    ]);
    Editmessagetext($from_id, $message_id, "📌 چه کاربر شارژ همگانی ارسال شود", $keyboardtypeuser);
    return;
}

if (!$rf_admin_handled && (preg_match('/typecustomer_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $typecustomer = $dataget[1];
    savedata("save", "typecustomer", $typecustomer);
    sendmessage($from_id, "📌 برای کاربران پیام ارسال شارژ ارسال شود یا خیر؟ 
بله : 1 
خیر : 0", $backadmin, 'HTML');
    step("getmeesagestatus", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmeesagestatus")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    sendmessage($from_id, $textbotlang['Admin']['Balance']['AddBalanceUsers'], $keyboardadmin, 'HTML');
    $query_where = "";
    if ($userdata['agent'] == "all") {
        if ($userdata['typecustomer'] == "all") {
            $query_where = "";
        } elseif ($userdata['typecustomer'] == "customer") {
            $query_where = "WHERE EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id);";
        } elseif ($userdata['typecustomer'] == "notcustomer") {
            $query_where = "WHERE  NOT EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id);";
        }
    } else {
        if ($userdata['typecustomer'] == "all") {
            $query_where = null;
            ;
        } elseif ($userdata['typecustomer'] == "customer") {
            $query_where = " WHERE u.agent =  '{$userdata['agent']}' AND EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id);";
        } elseif ($userdata['typecustomer'] == "notcustomer") {
            $query_where = " WHERE u.agent =  '{$userdata['agent']}' AND NOT EXISTS ( SELECT 1 FROM invoice i WHERE i.id_user = u.id);";
        }
    }
    $stmt = $pdo->prepare("SELECT u.id FROM user u " . $query_where);
    $stmt->execute();
    $Balance_user = $stmt->fetchAll();
    $stmt = $pdo->prepare("UPDATE user as u SET  Balance = Balance + {$userdata['price']} " . $query_where);
    $stmt->execute();
    step('home', $from_id);
    if ($text == "1") {
        $cancelmessage = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "لغو عملیات", 'callback_data' => 'cancel_sendmessage'],
                ],
            ]
        ]);
        $textgift = "🎁 کاربر  عزیز مبلغ {$userdata['price']} تومان از طرف مدیریت به عنوان هدیه به کیف پول شما واریز گردید.";
        $message_id = sendmessage($from_id, "✅ عملیات ارسال پیام آغاز گردید پس از پایان اطلاع رسانی خواهد شد.", $cancelmessage, "html");
        $data = json_encode(array(
            "id_admin" => $from_id,
            'type' => "sendmessage",
            "id_message" => $message_id['result']['message_id'],
            "message" => $textgift,
            "pingmessage" => "no",
            "btnmessage" => "start"
        ));
        file_put_contents("cronbot/users.json", json_encode($Balance_user));
        file_put_contents('cronbot/info', $data);
    }
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ کم کردن موجودی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Balance']['NegativeBalance'], $backadmin, 'HTML');
    step('Negative_Balance', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "Negative_Balance")) {
    $rf_admin_handled = true;

    if (!in_array($text, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['PriceBalancek'], $backadmin, 'HTML');
    update("user", "Processing_value", $text, "id", $from_id);
    step('get_price_Negative', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_price_Negative")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    if (intval($text) >= 100000000) {
        sendmessage($from_id, "📌 حداکثر مقدار 100 میلیون ریال است.", $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Balance']['NegativeBalanceUser'], $keyboardadmin, 'HTML');
    $Balance_usersa = select("user", "*", "id", $user['Processing_value'], "select");
    $Balance_Low_userkam = $Balance_usersa['Balance'] - $text;
    update("user", "Balance", $Balance_Low_userkam, "id", $user['Processing_value']);
    $balances1 = number_format($text, 0);
    $Balance_user_afters = number_format(select("user", "*", "id", $user['Processing_value'], "select")['Balance']);
    $textkam = "❌ کاربر عزیز مبلغ $balances1 تومان از  موجودی کیف پول تان کسر گردید.";
    sendmessage($user['Processing_value'], $textkam, null, 'HTML');
    step('home', $from_id);
    if (strlen($setting['Channel_Report']) > 0) {
        $textaddbalance = "📌 یک ادمین موجودی کاربر را کم کرده است :
        
🪪 اطلاعات ادمین کم کننده موجودی : 
نام کاربری :@$username
آیدی عددی : $from_id
👤 اطلاعات کاربر  :
آیدی عددی کاربر  : {$user['Processing_value']}
مبلغ موجودی : $text
موجودی کاربر پس از کم کردن : $Balance_user_afters";
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $paymentreports,
            'text' => $textaddbalance,
            'parse_mode' => "HTML"
        ]);
    }
    return;
}

if (!$rf_admin_handled && ($datain == "searchuser")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIdUserunblock'], $backadmin, 'HTML');
    step('show_info', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "show_info" || preg_match('/manageuser_(\w+)/', $datain, $dataget) || preg_match('/updateinfouser_(\w+)/', $datain, $dataget) || strpos($text, "/user ") !== false || strpos($text, "/id ") !== false)) {
    $rf_admin_handled = true;

    if ($user['step'] == "show_info") {
        $id_user = $text;
    } elseif (explode(" ", $text)[0] == "/user") {
        $id_user = explode(" ", $text)[1];
    } elseif (explode(" ", $text)[0] == "/id") {
        $id_user = explode(" ", $text)[1];
    } else {
        $id_user = $dataget[1];
    }
    if (!in_array($id_user, $users_ids)) {
        sendmessage($from_id, $textbotlang['Admin']['not-user'], null, 'HTML');
        return;
    }
    $date = date("Y-m-d");
    $dayListSell = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) FROM invoice WHERE (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND id_user = '$id_user'"));
    $balanceall = mysqli_fetch_assoc(mysqli_query($connect, "SELECT SUM(price) FROM Payment_report WHERE payment_Status = 'paid' AND id_user = '$id_user' AND Payment_Method != 'low balance by admin'"));
    $subbuyuser = mysqli_fetch_assoc(mysqli_query($connect, "SELECT SUM(price_product) FROM invoice WHERE (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND id_user = '$id_user'"));
    $invoicecount = select("invoice", '*', "id_user", $id_user, "count");
    if ($invoicecount == 0) {
        $sumvolume['SUM(Volume)'] = 0;
    } else {
        $sumvolume = mysqli_fetch_assoc(mysqli_query($connect, "SELECT SUM(Volume) FROM invoice WHERE (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND id_user = '$id_user' AND name_product != 'سرویس تست'"));
    }
    $user = select("user", "*", "id", $id_user, "select");
    $roll_Status = [
        '1' => $textbotlang['Admin']['ManageUser']['Acceptedphone'],
        '0' => $textbotlang['Admin']['ManageUser']['Failedphone'],
    ][$user['roll_Status']];
    if ($subbuyuser['SUM(price_product)'] == null)
        $subbuyuser['SUM(price_product)'] = 0;
    $keyboardmanage = [
        'inline_keyboard' => [
            [['text' => "♻️  بروزرسانی اطلاعات", 'callback_data' => "updateinfouser_" . $id_user],],
            [['text' => $textbotlang['Admin']['ManageUser']['addbalanceuser'], 'callback_data' => "addbalanceuser_" . $id_user], ['text' => $textbotlang['Admin']['ManageUser']['lowbalanceuser'], 'callback_data' => "lowbalanceuser_" . $id_user],],
            [['text' => $textbotlang['Admin']['ManageUser']['banuserlist'], 'callback_data' => "banuserlist_" . $id_user], ['text' => $textbotlang['Admin']['ManageUser']['unbanuserlist'], 'callback_data' => "unbanuserr_" . $id_user]],
            [['text' => $textbotlang['Admin']['ManageUser']['addagent'], 'callback_data' => "addagent_" . $id_user], ['text' => $textbotlang['Admin']['ManageUser']['removeagent'], 'callback_data' => "removeagent_" . $id_user]],
            [['text' => $textbotlang['Admin']['ManageUser']['confirmnumber'], 'callback_data' => "confirmnumber_" . $id_user]],
            [['text' => "🎁 درصد تخفیف", 'callback_data' => "Percentlow_" . $id_user], ['text' => "✍️ ارسال پیام به کاربر", 'callback_data' => "sendmessageuser_" . $id_user]],
            [['text' => $textbotlang['Admin']['ManageUser']['vieworderuser'], 'callback_data' => "vieworderuser_" . $id_user]],
            [['text' => "👥 زیرمجموعه های کاربر", 'callback_data' => "affiliates-" . $id_user]],
            [['text' => "🔄 خارج کردن از زیرمجموعه", 'callback_data' => "removeaffiliate-" . $id_user], ['text' => "🔄 حذف زیرمجموعه های کاربر", 'callback_data' => "removeaffiliateuser-" . $id_user]],
            [['text' => "💳 فعالسازی شماره کارت", 'callback_data' => "showcarduser-" . $id_user]],
            [['text' => "احراز هویت کاربر", 'callback_data' => "verify_" . $id_user], ['text' => "عدم احراز کاربر", 'callback_data' => "unverify-" . $id_user]],
            [['text' => "💳  غیرفعالسازی شماره کارت", 'callback_data' => "carduserhide-" . $id_user]],
            [['text' => "🛒 افزودن سفارش", 'callback_data' => "addordermanualـ" . $id_user], ['text' => "➕ محدودیت اکانت تست", 'callback_data' => "limitusertest_" . $id_user]],
            [['text' => $textbotlang['Admin']['ManageUser']['viewpaymentuser'], 'callback_data' => "viewpaymentuser_" . $id_user], ['text' => "انتقال حساب کاربری ", 'callback_data' => "transferaccount_" . $id_user]],
            [['text' => "💡 خاموش کردن اکانت", 'callback_data' => "disableconfig-" . $id_user], ['text' => "💡 روشن کردن اکانت", 'callback_data' => "activeconfig-" . $id_user]],
            [['text' => "📑 احراز عضویت کانال", 'callback_data' => "confirmchannel-" . $id_user], ['text' => "0️⃣ صفر کردن موجودی", 'callback_data' => "zerobalance-" . $id_user]],
            [['text' => "🕚 وضعیت ارسال پیام های کرون", 'callback_data' => "statuscronuser-" . $id_user]],
        ]
    ];
    if ($user['agent'] == "n2")
        $keyboardmanage['inline_keyboard'][] = [['text' => "سقف خرید  نماینده", 'callback_data' => "maxbuyagent_" . $id_user]];
    if ($user['agent'] != "f") {
        $keyboardmanage['inline_keyboard'][] = [
            ['text' => "🤖 فعالسازی ربات فروش", 'callback_data' => "createbot_" . $id_user],
            ['text' => "❌ حذف ربات فروش", 'callback_data' => "removebotsell_" . $id_user]
        ];
    }
    if ($user['agent'] != "f") {
        $keyboardmanage['inline_keyboard'][] = [
            ['text' => "🔋 قیمت پایه حجم", 'callback_data' => "setvolumesrc_" . $id_user],
            ['text' => "⏳ قیمت پایه زمان", 'callback_data' => "settimepricesrc_" . $id_user]
        ];
        $keyboardmanage['inline_keyboard'][] = [
            ['text' => "❌ مخفی کردن یک پنل برای نماینده", 'callback_data' => "hidepanel_" . $id_user],
        ];
        $keyboardmanage['inline_keyboard'][] = [
            ['text' => "🗑 نمایش پنل های مخفی شده", 'callback_data' => "removehide_" . $id_user],
        ];
        $keyboardmanage['inline_keyboard'][] = [
            ['text' => "⏱️ زمان انقضا نمایندگی", 'callback_data' => "expireset_" . $id_user],
        ];
    }
    if (intval($setting['statuslimitchangeloc']) == 1) {
        $keyboardmanage['inline_keyboard'][] = [
            ['text' => "محدودیت تغییر لوکیشن", 'callback_data' => "changeloclimitbyuser_" . $id_user]
        ];
    }
    $keyboardmanage['inline_keyboard'][] = [
        ['text' => "❌ بستن", 'callback_data' => 'close_stat']
    ];
    $keyboardmanage = json_encode($keyboardmanage, JSON_UNESCAPED_UNICODE);
    $user['Balance'] = number_format($user['Balance']);
    if ($user['register'] != "none") {
        if ($user['register'] == null)
            return;
        $userjoin = jdate('Y/m/d H:i:s', $user['register']);
    } else {
        $userjoin = "نامشخص";
    }
    $userverify = [
        '0' => "احراز نشده",
        '1' => "احراز شده"
    ][$user['verify']];
    $showcart = [
        '0' => "مخفی",
        '1' => "نمایش داده می شود"
    ][$user['cardpayment']];
    if ($user['last_message_time'] == null) {
        $lastmessage = "";
    } else {
        $lastmessage = jdate('Y/m/d H:i:s', $user['last_message_time']);
    }
    $datefirst = time() - 86400;
    $desired_date_time_start = time() - 3600;
    $month_date_time_start = time() - 2592000;
    $sql = "SELECT * FROM invoice WHERE time_sell > :requestedDate AND (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND name_product != 'سرویس تست' AND id_user = :id_user";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_user', $id_user);
    $stmt->bindParam(':requestedDate', $desired_date_time_start);
    $stmt->execute();
    $listhours = $stmt->rowCount();
    $sql = "SELECT SUM(price_product) FROM invoice WHERE time_sell > :requestedDate AND (Status = 'active' OR Status = 'end_of_time'  OR Status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND name_product != 'سرویس تست' AND id_user = :id_user";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_user', $id_user);
    $stmt->bindParam(':requestedDate', $desired_date_time_start);
    $stmt->execute();
    $suminvoicehours = $stmt->fetchColumn();
    if ($suminvoicehours == null) {
        $suminvoicehours = "0";
    }
    $sql = "SELECT * FROM invoice WHERE time_sell > :requestedDate AND (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND name_product != 'سرویس تست' AND id_user = :id_user";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_user', $id_user);
    $stmt->bindParam(':requestedDate', $month_date_time_start);
    $stmt->execute();
    $listmonth = $stmt->rowCount();
    $sql = "SELECT SUM(price_product) FROM invoice WHERE time_sell > :requestedDate AND (Status = 'active' OR Status = 'end_of_time'  OR Status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND name_product != 'سرویس تست' AND id_user = :id_user";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_user', $id_user);
    $stmt->bindParam(':requestedDate', $month_date_time_start);
    $stmt->execute();
    $suminvoicemonth = $stmt->fetchColumn();
    if ($suminvoicemonth == null) {
        $suminvoicemonth = "0";
    }
    if ($user['agent'] != "f" && $user['expire'] != null) {
        $text_expie_agent = "⭕️ تاریخ پایان نمایندگی : " . jdate('Y/m/d H:i:s', $user['expire']);
    } else {
        $text_expie_agent = "";
    }
    $textinfouser = "👀 اطلاعات کاربر:

🔗 اطلاعات کاربری کاربر

⭕️ وضعیت کاربر : {$user['User_Status']}
⭕️ نام کاربری کاربر : @{$user['username']}
⭕️ آیدی عددی کاربر :  <a href = \"tg://user?id=$id_user\">$id_user</a>
⭕️ کد معرف کاربر : {$user['codeInvitation']}
⭕️ زمان عضویت کاربر : $userjoin
⭕️ آخرین زمان  استفاده کاربر از ربات : $lastmessage
⭕️ محدودیت اکانت تست :  {$user['limit_usertest']} 
⭕️ وضعیت تایید قانون : $roll_Status
⭕️ شماره موبایل : <code>{$user['number']}</code>
⭕️ نوع کاربری : {$user['agent']}
⭕️ تعداد زیرمجموعه کاربر : {$user['affiliatescount']}
⭕  معرف کاربر : {$user['affiliates']}
⭕  وضعیت احراز هویت: $userverify   
⭕  نمایش شماره کارت :‌$showcart
⭕ امتیاز کاربر : {$user['score']}
⭕️  مجموع حجم خریداری شده فعال ( برای آمار دقیق حجم باید کرون روشن باشد): {$sumvolume['SUM(Volume)']}
$text_expie_agent

💎 گزارشات مالی

🔰 موجودی کاربر : {$user['Balance']}
🔰 تعداد خرید کل کاربر : {$dayListSell['COUNT(*)']}
🔰️ مبلغ کل پرداختی  :  {$balanceall['SUM(price)']}
🔰 جمع کل خرید : {$subbuyuser['SUM(price_product)']}
🔰 درصد تخفیف کاربر : {$user['pricediscount']}
🔰 تعداد فروش یک ساعت گذشته : $listhours عدد
🔰 مجموع فروش یک ساعت گذشته : $suminvoicehours تومان
🔰 تعداد فروش یک ماه گذشته : $listmonth عدد
🔰 مجموع فروش یک ماه گذشته : $suminvoicemonth تومان

";
    if (is_string($datain) && isset($datain[0]) && $datain[0] == "u") {
        telegram('answerCallbackQuery', array(
            'callback_query_id' => $callback_query_id,
            'text' => "اطلاعات بروزرسانی گردید",
            'show_alert' => true,
            'cache_time' => 5,
        ));
        Editmessagetext($from_id, $message_id, $textinfouser, $keyboardmanage);
    } else {
        sendmessage($from_id, $textinfouser, $keyboardmanage, 'HTML');
        sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardadmin, 'HTML');
    }
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🎁 ساخت کد هدیه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Discount']['GetCode'], $backadmin, 'HTML');
    step('get_code', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_code")) {
    $rf_admin_handled = true;

    if (!preg_match('/^[A-Za-z\d]+$/', $text)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['ErrorCode'], null, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("INSERT INTO Discount (code, limitused) VALUES (:code, :limitused)");
    $value = "0";
    $stmt->bindParam(':code', $text, PDO::PARAM_STR);
    $stmt->bindParam(':limitused', $value, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Discount']['PriceCode'], null, 'HTML');
    step('get_price_code', $from_id);
    update("user", "Processing_value", $text, "id", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_price_code")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Balance']['Invalidprice'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['Discount']['setlimituse'], $backadmin, 'HTML');
    update("Discount", "price", $text, "code", $user['Processing_value']);
    step('getlimitcodedis', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getlimitcodedis")) {
    $rf_admin_handled = true;

    step("home", $from_id);
    update("Discount", "limituse", $text, "code", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Discount']['SaveCode'], $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "❌ حذف کد هدیه" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemoveCode'], $json_list_Discount_list_admin, 'HTML');
    step('remove-Discount', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "remove-Discount")) {
    $rf_admin_handled = true;

    if (!in_array($text, $code_Discount)) {
        sendmessage($from_id, $textbotlang['Admin']['Discount']['NotCode'], null, 'HTML');
        return;
    }
    $stmt = $pdo->prepare("DELETE FROM Discount WHERE code = :code");
    $stmt->bindParam(':code', $text, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Discount']['RemovedCode'], $shopkeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🗑 حذف پروتکل" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Protocol']['RemoveProtocol'], $keyboardprotocollist, 'HTML');
    step('removeprotocol', $from_id);
    return;
}

