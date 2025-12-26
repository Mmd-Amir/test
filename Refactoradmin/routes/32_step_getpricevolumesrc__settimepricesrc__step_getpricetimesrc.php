<?php
rf_set_module('admin/routes/32_step_getpricevolumesrc__settimepricesrc__step_getpricetimesrc.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "getpricevolumesrc")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    step("home", $from_id);
    $userdate = json_decode($user['Processing_value'], true);
    $botinfo = json_decode(select("botsaz", "setting", "id_user", $userdate['id_user'], "select")['setting'], true);
    $botinfo['minpricevolume'] = $text;
    update("botsaz", "setting", json_encode($botinfo), "id_user", $userdate['id_user']);
    sendmessage($from_id, "✅ قیمت با موفقیت ذخیره گردید.", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/settimepricesrc_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    savedata("clear", "id_user", $id_user);
    sendmessage($from_id, "📌 کمترین قیمتی که میخواهید نماینده بابت هر روز زمان بپردازد را تعیین کنید", $backadmin, 'HTML');
    step("getpricetimesrc", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpricetimesrc")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    step("home", $from_id);
    $userdate = json_decode($user['Processing_value'], true);
    $botinfo = json_decode(select("botsaz", "setting", "id_user", $userdate['id_user'], "select")['setting'], true);
    $botinfo['minpricetime'] = $text;
    update("botsaz", "setting", json_encode($botinfo), "id_user", $userdate['id_user']);
    sendmessage($from_id, "✅ قیمت با موفقیت ذخیره گردید.", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "settimecornday" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید چند روز مانده است به پایان اشتراک به کاربر اطلاع داده شود. زمان برحسب روز است" . $setting['daywarn'] . "روز", $backadmin, 'HTML');
    step("getdaywarn", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getdaywarn")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, $textbotlang['Admin']['cronjob']['changeddata'], $keyboardadmin, 'HTML');
    step("home", $from_id);
    update("setting", "daywarn", $text);
    return;
}

if (!$rf_admin_handled && ($datain == "linkappsetting")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 یک گزینه را انتخاب نمایید.", $keyboardlinkapp, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "🔗 اضافه کردن برنامه")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 جهت اضافه کردن لینک دانلود برنامه  نام اپ یا نام دکمه را ارسال نمایید.", $backadmin, 'HTML');
    step("getnamebtnapp", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnamebtnapp")) {
    $rf_admin_handled = true;

    if (strlen($text) > 200) {
        sendmessage($from_id, "📌 نام باید کمتر از ۲۰۰ کاراکتر باشد.", $backadmin, 'HTML');
        return;
    }
    savedata("clear", "name", $text);
    sendmessage($from_id, "📌 لینک دانلود اپ را ارسال نمایید", $backadmin, 'HTML');
    step("geturlbtnapp", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "geturlbtnapp")) {
    $rf_admin_handled = true;

    if (!filter_var($text, FILTER_VALIDATE_URL)) {
        sendmessage($from_id, $textbotlang['Admin']['managepanel']['Invalid-domain'], $backadmin, 'HTML');
        return;
    }
    $userdate = json_decode($user['Processing_value'], true);
    $stmt = $pdo->prepare("INSERT INTO app (name, link) VALUES (:name, :link)");
    $stmt->bindParam(':name', $userdate['name'], PDO::PARAM_STR);
    $stmt->bindParam(':link', $text, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, "✅ لینک اپ شما با موفقیت اضافه گردید.", $keyboardlinkapp, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "❌ حذف برنامه")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 برای حذف برنامه از لیست زیر نام برنامه را انتخاب کنید", $json_list_remove_helpـlink, 'HTML');
    step("getnameappforremove", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnameappforremove")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ برنامه با موفقیت حذف گردید.", $keyboardlinkapp, 'HTML');
    step('home', $from_id);
    $stmt = $pdo->prepare("DELETE FROM app WHERE name = :name");
    $stmt->bindParam(':name', $text, PDO::PARAM_STR);
    $stmt->execute();
    return;
}

if (!$rf_admin_handled && ($text == "⚙️ وضعیت قابلیت ها پنل" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $panel = select("marzban_panel", "*", "name_panel", $user['Processing_value'], "select");
    if (!in_array($panel['subvip'], ['offsubvip', 'onsubvip'])) {
        update("marzban_panel", "subvip", "offsubvip", "code_panel", $panel['code_panel']);
        $panel = select("marzban_panel", "*", "code_panel", $panel['code_panel'], "select");
    }
    $customvlume = json_decode($panel['customvolume'], true);
    $statusconfig = [
        'onconfig' => $textbotlang['Admin']['Status']['statuson'],
        'offconfig' => $textbotlang['Admin']['Status']['statusoff']
    ][$panel['config']];
    $statussublink = [
        'onsublink' => $textbotlang['Admin']['Status']['statuson'],
        'offsublink' => $textbotlang['Admin']['Status']['statusoff']
    ][$panel['sublink']];
    $statusshowbuy = [
        'active' => $textbotlang['Admin']['Status']['statuson'],
        'disable' => $textbotlang['Admin']['Status']['statusoff']
    ][$panel['status']];
    $statusshowtest = [
        'ONTestAccount' => $textbotlang['Admin']['Status']['statuson'],
        'OFFTestAccount' => $textbotlang['Admin']['Status']['statusoff']
    ][$panel['TestAccount']];
    $statusconnecton = [
        'onconecton' => $textbotlang['Admin']['Status']['statuson'],
        'offconecton' => $textbotlang['Admin']['Status']['statusoff']
    ][$panel['conecton']];
    $status_extend = [
        'on_extend' => $textbotlang['Admin']['Status']['statuson'],
        'off_extend' => $textbotlang['Admin']['Status']['statusoff']
    ][$panel['status_extend']];
    $changeloc = [
        'onchangeloc' => $textbotlang['Admin']['Status']['statuson'],
        'offchangeloc' => $textbotlang['Admin']['Status']['statusoff']
    ][$panel['changeloc']];
    $inbocunddisable = [
        'oninbounddisable' => $textbotlang['Admin']['Status']['statuson'],
        'offinbounddisable' => $textbotlang['Admin']['Status']['statusoff']
    ][$panel['inboundstatus']];
    $subvip = [
        'onsubvip' => $textbotlang['Admin']['Status']['statuson'],
        'offsubvip' => $textbotlang['Admin']['Status']['statusoff']
    ][$panel['subvip']];
    $customstatusf = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$customvlume['f']];
    $customstatusn = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$customvlume['n']];
    $customstatusn2 = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$customvlume['n2']];
    $on_hold_test = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$panel['on_hold_test']];
    $Bot_Status = [
        'inline_keyboard' => [
            [
                ['text' => $statusshowbuy, 'callback_data' => "editpanel-statusbuy-{$panel['status']}-{$panel['code_panel']}"],
                ['text' => "🖥 نمایش پنل", 'callback_data' => "none"],
            ],
            [
                ['text' => $statusshowtest, 'callback_data' => "editpanel-statustest-{$panel['TestAccount']}-{$panel['code_panel']}"],
                ['text' => "🎁 نمایش تست", 'callback_data' => "none"],
            ],
            [
                ['text' => $status_extend, 'callback_data' => "editpanel-stautsextend-{$panel['status_extend']}-{$panel['code_panel']}"],
                ['text' => "🔋 وضعیت تمدید", 'callback_data' => "none"],
            ],
            [
                ['text' => $customstatusf, 'callback_data' => "editpanel-customstatusf-{$customvlume['f']}-{$panel['code_panel']}"],
                ['text' => "♻️ سرویس دلخواه گروه f", 'callback_data' => "none"],
            ],
            [
                ['text' => $customstatusn, 'callback_data' => "editpanel-customstatusn-{$customvlume['n']}-{$panel['code_panel']}"],
                ['text' => "♻️ سرویس دلخواه گروه n", 'callback_data' => "none"],
            ],
            [
                ['text' => $customstatusn2, 'callback_data' => "editpanel-customstatusn2-{$customvlume['n2']}-{$panel['code_panel']}"],
                ['text' => "♻️ سرویس دلخواه گروه n2", 'callback_data' => "none"],
            ]
        ]
    ];
    if (!in_array($panel['type'], ['Manualsale', "WGDashboard", 'hiddify'])) {
        $Bot_Status['inline_keyboard'][] = [
            ['text' => $statusconfig, 'callback_data' => "editpanel-stautsconfig-{$panel['config']}-{$panel['code_panel']}"],
            ['text' => "⚙️ ارسال کانفیگ", 'callback_data' => "none"],
        ];
    }
    if (!in_array($panel['type'], ['Manualsale', "WGDashboard", 'hiddify'])) {
        $Bot_Status['inline_keyboard'][] = [
            ['text' => $statussublink, 'callback_data' => "editpanel-sublink-{$panel['sublink']}-{$panel['code_panel']}"],
            ['text' => "⚙️ ارسال لینک اشتراک", 'callback_data' => "none"],
        ];
    }
    if (in_array($panel['type'], ['marzban', "x-ui_single", "marzneshin"])) {
        $Bot_Status['inline_keyboard'][] = [
            ['text' => $statusconnecton, 'callback_data' => "editpanel-connecton-{$panel['conecton']}-{$panel['code_panel']}"],
            ['text' => "📊 اولین اتصال", 'callback_data' => "none"],
        ];
        $Bot_Status['inline_keyboard'][] = [
            ['text' => $on_hold_test, 'callback_data' => "editpanel-on_hold_Test-{$panel['on_hold_test']}-{$panel['code_panel']}"],
            ['text' => "📊 اولین اتصال اکانت تست", 'callback_data' => "none"],
        ];
    }
    if (!in_array($panel['type'], ["Manualsale", "WGDashboard"])) {
        $Bot_Status['inline_keyboard'][] = [
            ['text' => $changeloc, 'callback_data' => "editpanel-changeloc-{$panel['changeloc']}-{$panel['code_panel']}"],
            ['text' => "🌍 تغییر لوکیشن", 'callback_data' => "none"],
        ];
        $Bot_Status['inline_keyboard'][] = [
            ['text' => $subvip, 'callback_data' => "editpanel-subvip-{$panel['subvip']}-{$panel['code_panel']}"],
            ['text' => "💎 لینک ساب اختصاصی", 'callback_data' => "none"],
        ];
    }
    if (in_array($panel['type'], ["marzban"])) {
        $Bot_Status['inline_keyboard'][] = [
            ['text' => $inbocunddisable, 'callback_data' => "editpanel-inbocunddisable-{$panel['inboundstatus']}-{$panel['code_panel']}"],
            ['text' => "📍 اکانت غیرفعال", 'callback_data' => "none"],
        ];
    }
    if ($panel['type'] == "ibsng" || $panel['type'] == "mikrotik") {
        unset($Bot_Status['inline_keyboard'][2]);
        unset($Bot_Status['inline_keyboard'][3]);
        unset($Bot_Status['inline_keyboard'][4]);
        unset($Bot_Status['inline_keyboard'][5]);
        unset($Bot_Status['inline_keyboard'][6]);
        unset($Bot_Status['inline_keyboard'][7]);
        unset($Bot_Status['inline_keyboard'][8]);
        unset($Bot_Status['inline_keyboard'][9]);
    }
    $Bot_Status['inline_keyboard'][] = [
        ['text' => "❌ بستن", 'callback_data' => 'close_stat']
    ];
    $Bot_Status['inline_keyboard'] = array_values($Bot_Status['inline_keyboard']);
    $Bot_Status = json_encode($Bot_Status);
    sendmessage($from_id, $textbotlang['Admin']['Status']['BotTitle'], $Bot_Status, 'HTML');
    return;
}

