<?php
rf_set_module('admin/routes/35_on_buy_porsant__off_buy_porsant__step_text_request_agent_dec.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($text == "🎉 پورسانت فقط برای خرید اول" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $marzbanporsant_one_buy = select("affiliates", "*", null, null, "select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanporsant_one_buy['porsant_one_buy'], 'callback_data' => $marzbanporsant_one_buy['porsant_one_buy']],
            ],
        ]
    ]);
    sendmessage($from_id, "می‌توانید تعیین کنید که پورسانت به کاربر فقط برای اولین خرید زیرمجموعه‌اش داده شود یا برای همه خریدهای او.", $keyboardDiscountaffiliates, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "on_buy_porsant")) {
    $rf_admin_handled = true;

    update("affiliates", "porsant_one_buy", "off_buy_porsant");
    $marzbanporsant_one_buy = select("affiliates", "*", null, null, "select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanporsant_one_buy['porsant_one_buy'], 'callback_data' => $marzbanporsant_one_buy['porsant_one_buy']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, "می‌توانید تعیین کنید که پورسانت به کاربر فقط برای اولین خرید زیرمجموعه‌اش داده شود یا برای همه خریدهای او.", $keyboardDiscountaffiliates);
    return;
}

if (!$rf_admin_handled && ($datain == "off_buy_porsant")) {
    $rf_admin_handled = true;

    update("affiliates", "porsant_one_buy", "on_buy_porsant");
    $marzbanporsant_one_buy = select("affiliates", "*", null, null, "select");
    $keyboardDiscountaffiliates = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $marzbanporsant_one_buy['porsant_one_buy'], 'callback_data' => $marzbanporsant_one_buy['porsant_one_buy']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, "می‌توانید تعیین کنید که پورسانت به کاربر فقط برای اولین خرید زیرمجموعه‌اش داده شود یا برای همه خریدهای او.", $keyboardDiscountaffiliates);
    return;
}

if (!$rf_admin_handled && ($text == "متن توضیحات درخواست نمایندگی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . "<code>{$datatextbot['text_request_agent_dec']}</code>", $backadmin, 'HTML');
    step('text_request_agent_dec', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_request_agent_dec")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_request_agent_dec");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/changestatusadmin_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_invoice = $dataget[1];
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $nameloc['username']);
    if ($DataUserOut['status'] == "on_hold") {
        sendmessage($from_id, "❌ هنوز به کانفیگ متصل نشده است کانفیگ و امکان تغییر وضعیت سرویس وجود ندارد. بعد از متصل شدن به کانفیگ می توانید از این قابلیت استفاده نمایید.", null, 'html');
        return;
    }
    if ($DataUserOut['status'] == "Unsuccessful") {
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], null, 'html');
        return;
    }
    if ($DataUserOut['status'] == "active") {
        $confirmdisableaccount = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '✅ تایید و غیرفعال کردن کانفیگ', 'callback_data' => "confirmaccountdisableadmin_" . $id_invoice],
                ],
                [
                    ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "manageinvoice_" . $nameloc['id_invoice']],
                ]
            ]
        ]);
        Editmessagetext($from_id, $message_id, "📌 با تایید گزینه زیر کانفیگ شما خاموش و دیگر امکان اتصال به کانفیگ وجود ندارد.
⚠️ در صورتی که میخواهید مجدد کانفیگ فعال شود باید از بخش مدیریت سرویس دکمه <u>💡 روشن کردن اکانت</u> را کلیک کنید", $confirmdisableaccount);
    } else {
        $confirmdisableaccount = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '✅ تایید و فعال کردن کانفیگ', 'callback_data' => "confirmaccountdisableadmin_" . $id_invoice],
                ],
                [
                    ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "manageinvoice_" . $nameloc['id_invoice']],
                ]
            ]
        ]);
        Editmessagetext($from_id, $message_id, "📌 با تایید گزینه زیر کانفیگ شما روشن خواهد شد. و می توانید به کانفیگ خود متصل شوید
⚠️ در صورتی که میخواهید مجدد کانفیگ غیرفعال شود باید از بخش مدیریت سرویس دکمه <u>❌ خاموش کردن اکانت</u>را کلیک کنید", $confirmdisableaccount);
    }
    return;
}

if (!$rf_admin_handled && (preg_match('/confirmaccountdisableadmin_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_invoice = $dataget[1];
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $bakinfos = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "manageinvoice_" . $nameloc['id_invoice']],
            ]
        ]
    ]);
    $dataoutput = $ManagePanel->Change_status($nameloc['username'], $nameloc['Service_location']);
    if ($dataoutput['status'] == "Unsuccessful") {
        Editmessagetext($from_id, $message_id, $textbotlang['users']['stateus']['notchanged'], $bakinfos);
        return;
    }
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $nameloc['username']);
    if ($DataUserOut['status'] == "active") {
        update("invoice", "Status", "active", "id_invoice", $nameloc['id_invoice']);
        Editmessagetext($from_id, $message_id, $textbotlang['users']['stateus']['activedconfig'], $bakinfos);
    } else {
        update("invoice", "Status", "disablebyadmin", "id_invoice", $nameloc['id_invoice']);
        Editmessagetext($from_id, $message_id, $textbotlang['users']['stateus']['disabledconfig'], $bakinfos);
    }
    return;
}

if (!$rf_admin_handled && (preg_match('/removefull-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_invoice = $dataget[1];
    $bakinfos = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "تایید و حذف ", 'callback_data' => "confirmremovefulls-" . $id_invoice],
            ],
            [
                ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "manageinvoice_" . $id_invoice],
            ]
        ]
    ]);
    Editmessagetext($from_id, $message_id, "📌 با تایید بر روی گزینه زیر این سرویس بطور کامل از دیتابیس ربات حذف خواهد شد و دیگرجزء آمار حساب نخواهد شد ( این بخش سرویس را از پنل حذف نمی کند و فقط از دیتابیس ربات حذف می کند)", $bakinfos);
    return;
}

if (!$rf_admin_handled && (preg_match('/confirmremovefulls-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_invoice = $dataget[1];
    $invocie = select("invoice", "*", "id_invoice", $id_invoice, "select");
    $stmt = $pdo->prepare("DELETE FROM invoice WHERE id_invoice = :id_invoice");
    $stmt->bindParam(':id_invoice', $id_invoice, PDO::PARAM_STR);
    $stmt->execute();
    Editmessagetext($from_id, $message_id, "✅ سرویس با موفقیت حذف گردید.", json_encode(['inline_keyboard' => []]));
    if (strlen($setting['Channel_Report']) > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherreport,
            'text' => "🔗 یک ادمین یک سرویس را از دیتابیس ربات حذف کرد.

- آیدی عددی ادمین :‌$from_id
- نام ادمین : $first_name
- نام کاربری سرویس :‌ {$invocie['username']}",
            'parse_mode' => "HTML"
        ]);
    }
    return;
}

if (!$rf_admin_handled && ($text == "🛒 اضافه کردن دسته بندی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 جهت اضافه کردن دسته بندی نام دسته بندی را ارسال کنید.", $backadmin, 'HTML');
    step("getremarkcategory", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getremarkcategory")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ دسته بندی با موفقیت اضافه گردید.", $shopkeyboard, 'HTML');
    step("home", $from_id);
    $stmt = $pdo->prepare("INSERT INTO category (remark) VALUES (?)");
    $stmt->bindParam(1, $text);
    $stmt->execute();
    return;
}

if (!$rf_admin_handled && ($text == "❌ حذف دسته بندی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 دسته بندی خود را جهت حذف انتخاب کنید", KeyboardCategoryadmin(), 'HTML');
    step("removecategory", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "removecategory")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ دسته بندی با موفقیت حذف گردید.", $shopkeyboard, 'HTML');
    step("home", $from_id);
    $stmt = $pdo->prepare("DELETE FROM category WHERE remark = :remark ");
    $stmt->bindParam(':remark', $text);
    $stmt->execute();
    return;
}

if (!$rf_admin_handled && ($text == "مخفی کردن پنل" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    if ($user['Processing_value_one'] != "/all") {
        sendmessage($from_id, "📌 این قابلیت فقط زمانی کاربرد دارد که شما لوکیشن محصول را /all تعریف کرده باشید.", null, 'HTML');
        return;
    }
    sendmessage($from_id, "📌 در صورتی که لوکیشن پنل را /all انتخاب کرده باشید اما نیاز داشته باشید که یک پنل را نشان ندهید از این قابلیت می توانید استفاده نمایید

جهت مخفی کردن پنل  از لیست زیر پنل های خود را اتنخاب کنید سپس دستور /end_hide را ارسال نمایید.", $json_list_marzban_panel, 'HTML');
    step('getlistpanel', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "/end_hide")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ ذخیره پنل ها با موفقیت انجام و پنل ها برای محصول انتخابی مخفی شد.", $shopkeyboard, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getlistpanel")) {
    $rf_admin_handled = true;

    $list_panel = json_decode(select("product", "hide_panel", "id", $user['Processing_value'], "select")['hide_panel'], true);
    if (in_array($text, $list_panel)) {
        sendmessage($from_id, "❌ پنل از قبل اضافه شده است", null, 'HTML');
        return;
    }
    $list_panel[] = $text;
    update("product", "hide_panel", json_encode($list_panel), "id", $user['Processing_value']);
    sendmessage($from_id, "✅ پنل انتخاب شد  پس از اتمام دستور /end_hide را ارسال نمایید تا ذخیره نهایی شود.", null, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "حذف کلی پنل های مخفی" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    update("product", "hide_panel", "{}", "name_product", $user['Processing_value']);
    sendmessage($from_id, "✅ تمامی پنل های مخفی حذف شدند", null, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🔗 وبهوک مجدد ربات های نماینده")) {
    $rf_admin_handled = true;

    $bots_agent = select("botsaz", "*", null, null, "fetchAll");
    if (count($bots_agent) == 0) {
        sendmessage($from_id, "❌ رباتی وجود ندارد", null, 'HTML');
        return;
    }
    sendmessage($from_id, "📌 در انجام وبهوک ...", null, 'HTML');
    foreach ($bots_agent as $bot) {
        file_get_contents("https://api.telegram.org/bot{$bot['bot_token']}/setwebhook?url=https://$domainhosts/vpnbot/{$bot['id_user']}{$bot['username']}/index.php");
    }
    sendmessage($from_id, "✅ وبهوک با موفقیت انجام شد.", null, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/statuscronuser-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $id_user = $dataget[1];
    $user_status = select("user", "*", "id", $id_user);
    if (intval($user_status['status_cron']) == 0) {
        update("user", "status_cron", "1", "id", $id_user);
        sendmessage($from_id, "✅ اطلاعیه های کرون برای کاربر فعال گردید.", null, 'HTML');
    } else {
        update("user", "status_cron", "0", "id", $id_user);
        sendmessage($from_id, "✅ اطلاعیه های کرون برای کاربر غیرفعال گردید.", null, 'HTML');
    }
    return;
}

if (!$rf_admin_handled && ($text == "🗂 مدیریت دسته بندی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard_Category_manage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "⬅️ بازگشت به منوی فروشگاه")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $shopkeyboard, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🛍 مدیریت محصولات" || $datain == "backproductadmin")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboard_shop_manage, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "✏️ ویرایش دسته بندی")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 دسته بندی خود را جهت ویرایش انتخاب کنید", KeyboardCategoryadmin(), 'HTML');
    step("editcategory_name", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "editcategory_name")) {
    $rf_admin_handled = true;

    savedata("clear", "category", $text);
    sendmessage($from_id, "📌  نام جدید دسته بندی را ارسال کنید", $backadmin, 'HTML');
    step("get_name_new_category", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "get_name_new_category")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    sendmessage($from_id, "✅ نام دسته بندی با موفقیت تغییر کرد.", $keyboard_Category_manage, 'HTML');
    step("home", $from_id);
    update("category", "remark", $text, "remark", $userdata['category']);
    update("product", "category", $text, "category", $userdata['category']);
    return;
}

if (!$rf_admin_handled && ($datain == "zerobalance")) {
    $rf_admin_handled = true;

    update("user", "pagenumber", "1", "id", $from_id);
    $page = 1;
    $items_per_page = 10;
    $start_index = ($page - 1) * $items_per_page;
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
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && ($datain == 'next_pageuserzero')) {
    $rf_admin_handled = true;

    $numpage = select("user", "*", null, null, "count");
    $page = $user['pagenumber'];
    $items_per_page = 10;
    $sum = $user['pagenumber'] * $items_per_page;
    if ($sum > $numpage) {
        $next_page = 1;
    } else {
        $next_page = $page + 1;
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

