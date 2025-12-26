<?php
rf_set_module('admin/routes/03_typeservice__typeusermessage__typeagent.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && (preg_match('/^typeservice-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $type = $dataget[1];
    savedata("clear", "typeservice", $type);
    if ($type == "unpinmessage") {
        deletemessage($from_id, $message_id);
        $typesend = [
            "unpinmessage" => "لغو پیام پین شده"
        ][$type];
        $textconfirm = "📌 شما در حال انجام عملیات مربوط به ارسال پیام هستید با بررسی اطلاعات زیر و تایید دکمه زیر عملیات ارسال شروع خواهد شد.
⚙️ نوع عملیات : $typesend";
        $startaction = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "تایید و شروع عملیات", 'callback_data' => 'startaction'],
                ],
            ]
        ]);
        sendmessage($from_id, $textconfirm, $startaction, 'HTML');
        sendmessage($from_id, "با تایید گزینه بالا فرآیند ارسال شروع خواهد شد", $keyboardadmin, 'HTML');
        step("home", $from_id);
        return;
    }
    $listbtn = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "همه کاربران", 'callback_data' => 'typeusermessage-all'],
            ],
            [
                ['text' => "مشتریانی که خرید داشتند", 'callback_data' => 'typeusermessage-customer'],
            ],
            [
                ['text' => "کاربرانی که خرید نداشتند", 'callback_data' => 'typeusermessage-nonecustomer'],
            ],
            [
                ['text' => "بازگشت به منوی قبل", 'callback_data' => 'systemsms'],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, "📌 سرویس برای کدام گروه کاربری اعمال شود؟", $listbtn);
    return;
}

if (!$rf_admin_handled && (preg_match('/^typeusermessage-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    if (!isset($userdata['typeservice'])) {
        deletemessage($from_id, $message_id);
        sendmessage($from_id, "❌ خطایی رخ داده لطفا مراحل ارسال پیام از اول انجام دهید", $keyboardadmin, 'HTML');
        return;
    }
    savedata("save", "typeusermessage", $dataget[1]);
    $listbtn = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "همه کاربران", 'callback_data' => 'typeagent-all'],
            ],
            [
                ['text' => "کاربران گروه f", 'callback_data' => 'typeagent-f'],
            ],
            [
                ['text' => "کاربران گروه n", 'callback_data' => 'typeagent-n'],
            ],
            [
                ['text' => "کاربران گروه n2", 'callback_data' => 'typeagent-n2'],
            ],
            [
                ['text' => "بازگشت به منوی قبل", 'callback_data' => 'typeservice-' . $userdata['typeservice']],
            ],
        ]
    ]);
    Editmessagetext($from_id, $message_id, "📌 سرویس برای چه دسته از کاربران اعمال شود؟", $listbtn);
    return;
}

if (!$rf_admin_handled && (preg_match('/^typeagent-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $type = $dataget[1];
    $userdata = json_decode($user['Processing_value'], true);
    if (!isset($userdata['typeservice'])) {
        deletemessage($from_id, $message_id);
        sendmessage($from_id, "❌ خطایی رخ داده لطفا مراحل ارسال پیام از اول انجام دهید", $keyboardadmin, 'HTML');
        return;
    }
    savedata("save", "agent", $type);
    if ($userdata['typeusermessage'] == "customer") {
        $stmt = $pdo->prepare("SELECT * FROM marzban_panel WHERE agent = :agent OR agent = 'all'");
        $stmt->bindParam(':agent', $type);
        $stmt->execute();
        $list_panel = ['inline_keyboard' => []];
        $list_panel['inline_keyboard'][] = [['text' => "تمامی پنل ها", 'callback_data' => 'locationmessage_all']];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $list_panel['inline_keyboard'][] = [
                ['text' => $result['name_panel'], 'callback_data' => "locationmessage_{$result['code_panel']}"]
            ];
        }
        $list_panel['inline_keyboard'][] = [['text' => "بازگشت به منوی قبل", 'callback_data' => 'typeusermessage-' . $userdata['typeusermessage']],];
        Editmessagetext($from_id, $message_id, "📌 پیام برای کدام کاربران موجود در پنل های زیر ارسال شود.", json_encode($list_panel));
        return;
    }
    if ($userdata['typeservice'] == "xdaynotmessage" or $userdata['typeservice'] == "sendmessage" or $userdata['typeservice'] == "forwardmessage") {
        $listbtn = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "بله", 'callback_data' => 'typepinmessage-yes'],
                    ['text' => "خیر", 'callback_data' => 'typepinmessage-no'],
                ],
                [
                    ['text' => "بازگشت به منوی قبل", 'callback_data' => 'typeusermessage-' . $userdata['typeusermessage']],
                ],
            ]
        ]);
        Editmessagetext($from_id, $message_id, "📌 آیا می خواهید پیام ارسال شده پین شود یا خیر.", $listbtn);
        return;
    }
    if ($userdata['typeservice'] == "xdaynotmessage") {
        step("gettextday", $from_id);
        sendmessage($from_id, "📌 در این قابلیت پیام به کاربرانی ارسال میشود که تعیین  میکنید چند روز از ربات استفاده نکرده اند
تعداد روز خود را ارسال نمایید.", $backadmin, 'HTML');
        return;
    }
    step("gettextSystemMessage", $from_id);
    sendmessage($from_id, "📌 متن پیام خود را ارسال نمایید.", $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/^locationmessage_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $typeoanel = $dataget[1];
    $userdata = json_decode($user['Processing_value'], true);
    if (!isset($userdata['typeservice'])) {
        deletemessage($from_id, $message_id);
        sendmessage($from_id, "❌ خطایی رخ داده لطفا مراحل ارسال پیام از اول انجام دهید", $keyboardadmin, 'HTML');
        return;
    }
    savedata("save", "selectpanel", $typeoanel);
    if ($userdata['typeservice'] == "xdaynotmessage" or $userdata['typeservice'] == "sendmessage" or $userdata['typeservice'] == "forwardmessage") {
        $listbtn = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "بله", 'callback_data' => 'typepinmessage-yes'],
                    ['text' => "خیر", 'callback_data' => 'typepinmessage-no'],
                ],
                [
                    ['text' => "بازگشت به منوی قبل", 'callback_data' => 'typeagent-' . $userdata['agent']],
                ],
            ]
        ]);
        Editmessagetext($from_id, $message_id, "📌 آیا می خواهید پیام ارسال شده پین شود یا خیر.", $listbtn);
        return;
    }
    if ($userdata['typeservice'] == "xdaynotmessage") {
        step("gettextday", $from_id);
        sendmessage($from_id, "📌 در این قابلیت پیام به کاربرانی ارسال میشود که تعیین  میکنید چند روز از ربات استفاده نکرده اند
تعداد روز خود را ارسال نمایید.", $backadmin, 'HTML');
        return;
    }
    step("gettextSystemMessage", $from_id);
    sendmessage($from_id, "📌 متن پیام خود را ارسال نمایید.", $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/^typepinmessage-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $type = $dataget[1];
    $userdata = json_decode($user['Processing_value'], true);
    if (!isset($userdata['typeservice'])) {
        deletemessage($from_id, $message_id);
        sendmessage($from_id, "❌ خطایی رخ داده لطفا مراحل ارسال پیام از اول انجام دهید", $keyboardadmin, 'HTML');
        return;
    }
    savedata("save", "typepinmessage", $type);
    $listbtn = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "دکمه استارت", 'callback_data' => 'btntypemessage-start'],
                ['text' => "دکمه آموزش", 'callback_data' => 'btntypemessage-helpbtn'],
            ],
            [
                ['text' => "دکمه خرید", 'callback_data' => 'btntypemessage-buy'],
                ['text' => "دکمه اکانت تست", 'callback_data' => 'btntypemessage-usertestbtn'],
            ],
            [
                ['text' => "دکمه زیرمجموعه گیری ", 'callback_data' => 'btntypemessage-affiliatesbtn'],
                ['text' => "شارژ حساب کاربری", 'callback_data' => 'btntypemessage-addbalance'],
            ],
            [
                ['text' => "ارسال بدون دکمه", 'callback_data' => 'btntypemessage-none'],
            ],
            [
                ['text' => "بازگشت به منوی قبل", 'callback_data' => 'typeagent-' . $userdata['agent']],
            ],
        ]
    ]);
    if ($userdata['typeservice'] == "forwardmessage") {
        step("gettextSystemMessage", $from_id);
        sendmessage($from_id, "📌 متن پیام خود را ارسال نمایید.", $backadmin, 'HTML');
        return;
    }
    Editmessagetext($from_id, $message_id, "📌 اگر می خواهید زیر پیام دکمه ای نمایش داده شود از لیست زیر گزینه ای را انتخاب کنید در غیر اینصورت دکمه  ارسال بدون دکمه را بزنید", $listbtn);
    return;
}

if (!$rf_admin_handled && (preg_match('/^btntypemessage-(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    deletemessage($from_id, $message_id);
    $type = $dataget[1];
    savedata("save", "btntypemessage", $type);
    $userdata = json_decode($user['Processing_value'], true);
    if (!isset($userdata['typeservice'])) {
        deletemessage($from_id, $message_id);
        sendmessage($from_id, "❌ خطایی رخ داده لطفا مراحل ارسال پیام از اول انجام دهید", $keyboardadmin, 'HTML');
        return;
    }
    if ($userdata['typeservice'] == "xdaynotmessage") {
        step("gettextday", $from_id);
        sendmessage($from_id, "📌 در این قابلیت پیام به کاربرانی ارسال میشود که تعیین  میکنید چند روز از ربات استفاده نکرده اند
تعداد روز خود را ارسال نمایید.", $backadmin, 'HTML');
        return;
    }
    step("gettextSystemMessage", $from_id);
    sendmessage($from_id, "📌 متن پیام خود را ارسال نمایید.", $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextday")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    if (!isset($userdata['typeservice'])) {
        deletemessage($from_id, $message_id);
        sendmessage($from_id, "❌ خطایی رخ داده لطفا مراحل ارسال پیام از اول انجام دهید", $keyboardadmin, 'HTML');
        return;
    }
    savedata("save", "daynoyuse", $text);
    step("gettextSystemMessage", $from_id);
    sendmessage($from_id, "📌 متن پیام خود را ارسال نمایید.", $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextSystemMessage")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    if (!isset($userdata['typeservice'])) {
        deletemessage($from_id, $message_id);
        sendmessage($from_id, "❌ خطایی رخ داده لطفا مراحل ارسال پیام از اول انجام دهید", $keyboardadmin, 'HTML');
        return;
    }
    if ($userdata['typeservice'] == "forwardmessage") {
        savedata("save", "message", $message_id);
    } elseif ($userdata['typeservice'] == "xdaynotmessage") {
        if ($text) {
            savedata("save", "message", $text);
        } else {
            sendmessage($from_id, "📌  در بخش کاربرانی که به تعداد روز تعیین شده استفاده نکردند فقط امکان ارسال متن وجود دارد.", $backadmin, 'HTML');
            return;
        }
    } elseif ($userdata['typeservice'] == "sendmessage") {
        if ($text) {
            savedata("save", "message", $text);
        } else {
            sendmessage($from_id, "📌  در بخش ارسال همگانی فقط امکان ارسال متن وجود دارد.", $backadmin, 'HTML');
            return;
        }
    }
    $typesend = [
        "xdaynotmessage" => "کاربرانی که به تعداد روز تعیین شده استفاده نکردند",
        "sendmessage" => "ارسال همگانی",
        "forwardmessage" => "فوروارد همگانی",
        "unpinmessage" => "لغو پیام پین شده"
    ][$userdata['typeservice']];
    $typeservice = [
        "all" => "ارسال به همه کاربران",
        "customer" => "مشتریان",
        "nonecustomer" => "کسانی که خرید نداشتند",
    ][$userdata['typeusermessage']];
    if ($userdata['typeservice'] == "xdaynotmessage") {
        $textday = "تعداد روزی که کاربر پیام نداده است : {$userdata['daynoyuse']}";
    } else {
        $textday = "";
    }
    $textconfirm = "📌 شما در حال انجام عملیات مربوط به ارسال پیام هستید با بررسی اطلاعات زیر و تایید دکمه زیر عملیات ارسال شروع خواهد شد.
⚙️ نوع عملیات : $typesend
🎛 نوع سرویس : $typeservice
🗂 نوع کاربری : {$userdata['agent']}
$textday
";
    $startaction = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "تایید و شروع عملیات", 'callback_data' => 'startaction'],
            ],
        ]
    ]);
    sendmessage($from_id, $textconfirm, $startaction, 'HTML');
    sendmessage($from_id, "با تایید گزینه بالا فرآیند ارسال شروع خواهد شد", $keyboardadmin, 'HTML');
    step("home", $from_id);
    return;
}

