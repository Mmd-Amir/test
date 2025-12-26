<?php
rf_set_module('admin/routes/13_step_getpaawordnew__step_confirmremovepanel__backlistuser.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($text == "🔐 ویرایش رمز عبور" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['managepanel']['getpasswordnew'], $backadmin, 'HTML');
    step('GetpaawordNew', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "GetpaawordNew")) {
    $rf_admin_handled = true;

    $typepanel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    outtypepanel($typepanel['type'], $textbotlang['Admin']['managepanel']['ChangedpasswordPanel']);
    update("marzban_panel", "password_panel", $text, "name_panel", $user['Processing_value']);
    update("marzban_panel", "datelogin", null, "name_panel", $user['Processing_value']);
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "❌ حذف پنل" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "در صورت تایید کلمه زیر را ارسال کنید.
<code>تایید</code>", $backadmin, 'HTML');
    step('confirmremovepanel', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "confirmremovepanel")) {
    $rf_admin_handled = true;

    if ($text == "تایید") {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['RemovedPanel'], $keyboardadmin, 'HTML');
        $marzban = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
        $stmt = $pdo->prepare("DELETE FROM marzban_panel WHERE name_panel = :name_panel");
        $stmt->bindParam(':name_panel', $user['Processing_value'], PDO::PARAM_STR);
        $stmt->execute();
    }
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == $textbotlang['Admin']['btnkeyboardadmin']['managruser'] || $datain == "backlistuser")) {
    $rf_admin_handled = true;

    $keyboardtypelistuser = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "لیست کاربرانی که موجودی دارند.", 'callback_data' => "balanceuserlist"],
            ],
            [
                ['text' => "لیست کاربرانی که زیرمجموعه دارند.", 'callback_data' => "listrefral"],
            ],
            [
                ['text' => "لیست کاربران شماره کارت فعال.", 'callback_data' => "cartuserlist"],
            ],
            [
                ['text' => "لیست کاربرانی که موجودی منفی دارند", 'callback_data' => "zerobalance"],
            ],
            [
                ['text' => "لیست نمایندگان", 'callback_data' => "agentlistusers"],
                ['text' => "لیست کل کاربران", 'callback_data' => "alllistusers"],
            ],
            [
                ['text' => "🛍 جستجو سفارش", 'callback_data' => "searchorder"],
                ['text' => "👥 شارژ همگانی", 'callback_data' => "balanceaddall"],
            ],
            [
                ['text' => "🔍 جستجو کاربر", 'callback_data' => "searchuser"],
                ['text' => "📨 بخش ارسال پیام", 'callback_data' => "systemsms"],
            ],
            [
                ['text' => "🔋 حجم یا زمان همگانی", 'callback_data' => "voloume_or_day_all"],
            ]
        ]
    ]);
    $text_list_users = "📌 از لیست زیر یک گزینه را انتخاب نمایید";
    if ($datain == "backlistuser") {
        Editmessagetext($from_id, $message_id, $text_list_users, $keyboardtypelistuser);
    } else {
        sendmessage($from_id, $text_list_users, $keyboardtypelistuser, 'html');
    }
    return;
}

if (!$rf_admin_handled && ($datain == "alllistusers")) {
    $rf_admin_handled = true;

    update("user", "pagenumber", "1", "id", $from_id);
    $page = 1;
    $items_per_page = 10;
    $start_index = ($page - 1) * $items_per_page;
    $result = mysqli_query($connect, "SELECT * FROM user  LIMIT $start_index, $items_per_page");
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
            'callback_data' => 'next_pageuser'
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_pageuser'
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
    $keyboardlists['inline_keyboard'][] = [
        [
            'text' => '❌ بستن',
            'callback_data' => 'close_listusers'
        ]
    ];
    $keyboard_json = json_encode($keyboardlists);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && ($datain == 'next_pageuser')) {
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
    $result = mysqli_query($connect, "SELECT * FROM user LIMIT $start_index, $items_per_page");
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
            'callback_data' => 'next_pageuser'
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_pageuser'
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboardlists['inline_keyboard'][] = [
        [
            'text' => '❌ بستن',
            'callback_data' => 'close_listusers'
        ]
    ];
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && ($datain == 'previous_pageuser')) {
    $rf_admin_handled = true;

    $page = $user['pagenumber'];
    $items_per_page = 10;
    if ($user['pagenumber'] <= 1) {
        $next_page = 1;
    } else {
        $next_page = $page - 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    $result = mysqli_query($connect, "SELECT * FROM user LIMIT $start_index, $items_per_page");
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
            'callback_data' => 'next_pageuser'
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => 'previous_pageuser'
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboardlists['inline_keyboard'][] = [
        [
            'text' => '❌ بستن',
            'callback_data' => 'close_listusers'
        ]
    ];
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && ($datain == 'close_listusers')) {
    $rf_admin_handled = true;

    deletemessage($from_id, $message_id);
    return;
}

if (!$rf_admin_handled && ($datain == "agentlistusers")) {
    $rf_admin_handled = true;

    $keyboardtypelistuser = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "n", 'callback_data' => "agenttypshowlist_n"],
                ['text' => "n2", 'callback_data' => "agenttypshowlist_n2"],
            ],
            [
                ['text' => "تمام نمایندگان", 'callback_data' => "agenttypshowlist_all"],
            ]
        ]
    ]);
    Editmessagetext($from_id, $message_id, "📌 کدام گروه از نمایندگان می خواهید مشاهده کنید ؟", $keyboardtypelistuser);
    return;
}

if (!$rf_admin_handled && (preg_match('/agenttypshowlist_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $typeagent = $datagetr[1];
    update("user", "pagenumber", "1", "id", $from_id);
    $page = 1;
    $items_per_page = 10;
    $start_index = ($page - 1) * $items_per_page;
    if ($typeagent == "all") {
        $result = mysqli_query($connect, "SELECT * FROM user WHERE agent != 'f'  LIMIT $start_index, $items_per_page");
    } else {
        $result = mysqli_query($connect, "SELECT * FROM user WHERE agent = '$typeagent'  LIMIT $start_index, $items_per_page");
    }
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
            'callback_data' => "next_pageuseragent_$typeagent"
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

if (!$rf_admin_handled && (preg_match('/next_pageuseragent_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $typeagent = $datagetr[1];
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
    if ($typeagent == "all") {
        $result = mysqli_query($connect, "SELECT * FROM user WHERE agent != 'f'  LIMIT $start_index, $items_per_page");
    } else {
        $result = mysqli_query($connect, "SELECT * FROM user WHERE agent = '$typeagent'  LIMIT $start_index, $items_per_page");
    }
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
            'callback_data' => "next_pageuseragent_$typeagent"
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => "previous_pageuseragent_$typeagent"
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

if (!$rf_admin_handled && (preg_match('/previous_pageuseragent_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $typeagent = $datagetr[1];
    $page = $user['pagenumber'];
    $items_per_page = 10;
    if ($user['pagenumber'] <= 1) {
        $next_page = 1;
    } else {
        $next_page = $page - 1;
    }
    $start_index = ($next_page - 1) * $items_per_page;
    if ($typeagent == "all") {
        $result = mysqli_query($connect, "SELECT * FROM user WHERE agent != 'f'  LIMIT $start_index, $items_per_page");
    } else {
        $result = mysqli_query($connect, "SELECT * FROM user WHERE agent = '$typeagent'  LIMIT $start_index, $items_per_page");
    }
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
            'callback_data' => "next_pageuseragent_$typeagent"
        ],
        [
            'text' => $textbotlang['users']['page']['previous'],
            'callback_data' => "previous_pageuseragent_$typeagent"
        ]
    ];
    $keyboardlists['inline_keyboard'][] = $pagination_buttons;
    $keyboard_json = json_encode($keyboardlists);
    update("user", "pagenumber", $next_page, "id", $from_id);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['ManageUser']['mangebtnuserdec'], $keyboard_json);
    return;
}

