<?php
rf_set_module('admin/routes/31_vieworderuser__next_pageinvoice__previous_pageinvoice.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && (preg_match('/vieworderuser_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    update("user", "pagenumber", "1", "id", $from_id);
    $page = 1;
    $items_per_page = 10;
    $start_index = ($page - 1) * $items_per_page;
    $result = mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$id_user'  LIMIT $start_index, $items_per_page");
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    $keyboardlists['inline_keyboard'][] = [
        ['text' => "عملیات", 'callback_data' => "action"],
        ['text' => "وضعیت سرویس", 'callback_data' => "Status"],
        ['text' => "نام کاربری", 'callback_data' => "username"],
    ];
    while ($row = mysqli_fetch_assoc($result)) {
        $keyboardlists['inline_keyboard'][] = [
            [
                'text' => "مشاهده اطلاعات",
                'callback_data' => "manageinvoice_" . $row['id_invoice']
            ],
            [
                'text' => $row['Status'],
                'callback_data' => "username"
            ],
            [
                'text' => $row['username'],
                'callback_data' => $row['username']
            ],
        ];
    }
    $pagination_buttons = [
        [
            'text' => $textbotlang['users']['page']['next'],
            'callback_data' => 'next_pageinvoice_' . $id_user
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_pageinvoice_' . $id_user
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json, 'html');
    return;
}

if (!$rf_admin_handled && (preg_match('/next_pageinvoice_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    $numpage = select("invoice", "*", "id_user", $id_user, "count");
    $page = $user['pagenumber'];
    $items_per_page = 10;
    $sum = $user['pagenumber'] * $items_per_page;
    if ($sum > $numpage) {
        $next_page = 1;
    } else {
        $next_page = $page + 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    $result = mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$id_user'  LIMIT $start_index, $items_per_page");
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    $keyboardlists['inline_keyboard'][] = [
        ['text' => "عملیات", 'callback_data' => "action"],
        ['text' => "وضعیت سرویس", 'callback_data' => "Status"],
        ['text' => "نام کاربری", 'callback_data' => "username"],
    ];
    while ($row = mysqli_fetch_assoc($result)) {
        $keyboardlists['inline_keyboard'][] = [
            [
                'text' => "مشاهده اطلاعات",
                'callback_data' => "manageinvoice_" . $row['id_invoice']
            ],
            [
                'text' => $row['Status'],
                'callback_data' => "username"
            ],
            [
                'text' => $row['username'],
                'callback_data' => $row['username']
            ],
        ];
    }
    $pagination_buttons = [
        [
            'text' => $textbotlang['users']['page']['next'],
            'callback_data' => 'next_pageinvoice_' . $id_user
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_pageinvoice_' . $id_user
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && (preg_match('/previous_pageinvoice_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    $numpage = select("invoice", "*", "id_user", $id_user, "count");
    $page = $user['pagenumber'];
    $items_per_page = 10;
    if ($user['pagenumber'] <= 1) {
        $next_page = 1;
    } else {
        $next_page = $page - 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    $result = mysqli_query($connect, "SELECT * FROM invoice WHERE id_user = '$id_user'  LIMIT $start_index, $items_per_page");
    $keyboardlists = [
        'inline_keyboard' => [],
    ];
    $keyboardlists['inline_keyboard'][] = [
        ['text' => "عملیات", 'callback_data' => "action"],
        ['text' => "وضعیت سرویس", 'callback_data' => "Status"],
        ['text' => "نام کاربری", 'callback_data' => "username"],
    ];
    while ($row = mysqli_fetch_assoc($result)) {
        $keyboardlists['inline_keyboard'][] = [
            [
                'text' => "مشاهده اطلاعات",
                'callback_data' => "manageinvoice_" . $row['id_invoice']
            ],
            [
                'text' => $row['Status'],
                'callback_data' => "username"
            ],
            [
                'text' => $row['username'],
                'callback_data' => $row['username']
            ],
        ];
    }
    $pagination_buttons = [
        [
            'text' => $textbotlang['users']['page']['next'],
            'callback_data' => 'next_pageinvoice_' . $id_user
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_pageinvoice_' . $id_user
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && ($text == "متن دکمه گردونه شانس" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_wheel_luck'], $backadmin, 'HTML');
    step('text_wheel_luck', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_wheel_luck")) {
    $rf_admin_handled = true;

    if (!$text) {
        sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ErrorText'], $textbot, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_wheel_luck");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "cartuserlist")) {
    $rf_admin_handled = true;

    update("user", "pagenumber", "1", "id", $from_id);
    $page = 1;
    $items_per_page = 10;
    $start_index = ($page - 1) * $items_per_page;
    $result = mysqli_query($connect, "SELECT * FROM user WHERE cardpayment = '1'  LIMIT $start_index, $items_per_page");
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
            'callback_data' => 'next_pageusercart'
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_pageusercart'
        ]
    ];
    $backbtn = [
        [
            'text' => "بازگشت به منوی قبل",
            'callback_data' => 'backlistuser'
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $backbtn;
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && ($datain == 'next_pageusercart')) {
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
    $result = mysqli_query($connect, "SELECT * FROM user WHERE cardpayment = '1'  LIMIT $start_index, $items_per_page");
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
            'callback_data' => 'next_pageusercart'
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_pageusercart'
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && ($datain == 'previous_pageusercart')) {
    $rf_admin_handled = true;

    $page = $user['pagenumber'];
    $items_per_page = 10;
    if ($user['pagenumber'] <= 1) {
        $next_page = 1;
    } else {
        $next_page = $page - 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    $result = mysqli_query($connect, "SELECT * FROM user WHERE cardpayment = '1'  LIMIT $start_index, $items_per_page");
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
            'callback_data' => 'next_pageusercart'
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_pageusercart'
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && (preg_match('/createbot_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    $checkbot = select("botsaz", "*", "id_user", $id_user, "count");
    $checkbots = select("botsaz", "*", null, null, "count");
    if ($checkbots >= 15) {
        sendmessage($from_id, "❌  درحال حاضر فقط محدود به ساختن 15 ربات برای نماینده های خود هستید.", $keyboardadmin, 'HTML');
        return;
    }
    if ($checkbot != 0) {
        $textexitsbot = "❌ این ربات از قبل نصب شده است امکان نصب مجدد وجود ندارد.";
        sendmessage($from_id, $textexitsbot, $keyboardadmin, 'HTML');
        return;
    }
    savedata("clear", "id_user", $id_user);
    $texbot = "📌  از طریق این بخش شما می توانید برای نماینده خود یک ربات فروش بسازید تا نماینده با ربات اختصاصی خودش فروش داشته باشد

- جهت ساخت ربات توکن ربات را ارسال نمایید.";
    sendmessage($from_id, $texbot, $backadmin, 'HTML');
    step("gettokenbot", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettokenbot")) {
    $rf_admin_handled = true;

    $getInfoToken = json_decode(file_get_contents("https://api.telegram.org/bot$text/getme"), true);
    if ($getInfoToken == false or !$getInfoToken['ok']) {
        sendmessage($from_id, "❌ توکن نامعتبر است", $backadmin, 'HTML');
        return;
    }
    $checkbot = select("botsaz", "*", "bot_token", $text, "count");
    if ($checkbot != 0) {
        sendmessage($from_id, "📌 این توکن از قبل ثبت شده است", null, 'HTML');
        return;
    }
    savedata("save", "token", $text);
    savedata("save", "username", $getInfoToken['result']['username']);
    $texbot = "📌 آیدی عددی ادمین را ارسال نمایید";
    sendmessage($from_id, $texbot, $backadmin, 'HTML');
    step("getadminidbot", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getadminidbot")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    $userdate = json_decode($user['Processing_value'], true);
    step("home", $from_id);
    $admin_ids = json_encode(array(
        $userdate['id_user']
    ));
    $destination = getcwd();
    $dirsource = "$destination/vpnbot/{$userdate['id_user']}{$userdate['username']}";
    if (is_dir($dirsource) && !deleteDirectory($dirsource)) {
        error_log('Failed to remove existing bot directory: ' . $dirsource);
    }
    if (!copyDirectoryContents($destination . '/vpnbot/Default', $dirsource)) {
        error_log('Failed to copy default bot files into: ' . $dirsource);
    }
    $contentconfig = file_get_contents($dirsource . "/config.php");
    $new_code = str_replace('BotTokenNew', $userdate['token'], $contentconfig);
    file_put_contents($dirsource . "/config.php", $new_code);
    file_get_contents("https://api.telegram.org/bot{$userdate['token']}/setwebhook?url=https://$domainhosts/vpnbot/{$userdate['id_user']}{$userdate['username']}/index.php");
    file_get_contents("https://api.telegram.org/bot{$userdate['token']}/sendmessage?chat_id={$userdate['id_user']}&text=✅ کاربر عزیز ربات شما با موفقیت نصب گردید.");
    $datasetting = json_encode(array(
        "minpricetime" => 4000,
        "pricetime" => 4000,
        "minpricevolume" => 4000,
        "pricevolume" => 4000,
        "support_username" => "@support",
        "Channel_Report" => 0,
        "cart_info" => "جهت پرداخت مبلغ را به شماره کارت زیر واریز نمایید",
        'show_product' => true,
    ));
    $value = "{}";
    $stmt = $pdo->prepare("INSERT INTO botsaz (id_user,bot_token,admin_ids,username,time,setting,hide_panel) VALUES (:id_user,:bot_token,:admin_ids,:username,:time,:setting,:hide_panel)");
    $stmt->bindParam(':id_user', $userdate['id_user'], PDO::PARAM_STR);
    $stmt->bindParam(':bot_token', $userdate['token'], PDO::PARAM_STR);
    $stmt->bindParam(':admin_ids', $admin_ids);
    $stmt->bindParam(':username', $userdate['username'], PDO::PARAM_STR);
    $time = date('Y/m/d H:i:s');
    $stmt->bindParam(':time', $time, PDO::PARAM_STR);
    $stmt->bindParam(':setting', $datasetting, PDO::PARAM_STR);
    $stmt->bindParam(':hide_panel', $value, PDO::PARAM_STR);
    $stmt->execute();
    $texbot = "✅ ربات نماینده با موفقیت ساخته شد.
⚙️ نام کاربری ربات  : @{$userdate['username']}
🤠 توکن ربات : <code>{$userdate['token']}</code>";
    sendmessage($from_id, $texbot, $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/removebotsell_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    $contentbto = select("botsaz", "*", "id_user", $id_user, "select");
    $destination = getcwd();
    $dirsource = "$destination/vpnbot/$id_user{$contentbto['username']}";
    if (is_dir($dirsource) && !deleteDirectory($dirsource)) {
        error_log('Failed to remove bot directory: ' . $dirsource);
    }
    if (!empty($contentbto['bot_token'])) {
        file_get_contents("https://api.telegram.org/bot{$contentbto['bot_token']}/deletewebhook");
    }
    $stmt = $pdo->prepare("DELETE FROM botsaz WHERE id_user = :id_user");
    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, "❌ ربات فروش نماینده با موفقیت حذف گردید.", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/setvolumesrc_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    savedata("clear", "id_user", $id_user);
    sendmessage($from_id, "📌 کمترین قیمتی که میخواهید نماینده بابت هر گیگ حجم بپردازد را تعیین کنید", $backadmin, 'HTML');
    step("getpricevolumesrc", $from_id);
    return;
}

