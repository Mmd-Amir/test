<?php
rf_set_module('admin/routes/23_set_panel_pasargad__step_getlocoption__bakcnode.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($text == "📑 نوع مرزبان" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $configPath = RF_APP_ROOT . '/config.php';
    $selectionMessage = buildPanelSelectionMessage($configPath);
    $selectionKeyboard = getPanelSelectionKeyboard();
    sendmessage($from_id, $selectionMessage, $selectionKeyboard, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "set_panel_pasargad" || $datain == "set_panel_marzban")) {
    $rf_admin_handled = true;

    if ($adminrulecheck['rule'] != "administrator") {
        telegram('answerCallbackQuery', [
            'callback_query_id' => $callback_query_id,
            'text' => '❌ شما به این بخش دسترسی ندارید.',
            'show_alert' => true,
            'cache_time' => 5,
        ]);
        return;
    }

    $configPath = RF_APP_ROOT . '/config.php';
    $desiredState = $datain == "set_panel_pasargad" ? 'pasargad' : 'marzban';

    deletemessage($from_id, $message_id);

    $updateResult = updatePanelStateInConfigFile($configPath, $desiredState);

    if ($updateResult) {
        $confirmationText = $desiredState === 'pasargad'
            ? "✅ نوع پنل با موفقیت روی «پاسارگارد» تنظیم شد.\n🔹 نوع فعلی پنل: پاسارگارد"
            : "✅ نوع پنل با موفقیت روی «مرزبان» تنظیم شد.\n🔹 نوع فعلی پنل: مرزبان";

        sendmessage($from_id, $confirmationText, null, 'HTML');

        telegram('answerCallbackQuery', [
            'callback_query_id' => $callback_query_id,
            'text' => '✅ تغییر با موفقیت انجام شد.',
            'show_alert' => false,
            'cache_time' => 5,
        ]);
    } else {
        telegram('answerCallbackQuery', [
            'callback_query_id' => $callback_query_id,
            'text' => '❌ در ذخیره تغییرات مشکلی رخ داد.',
            'show_alert' => true,
            'cache_time' => 5,
        ]);

        $errorMessage = "❌ ذخیره تغییرات با مشکل مواجه شد. لطفاً دوباره تلاش کنید.";
        sendmessage($from_id, $errorMessage, null, 'HTML');

        $selectionKeyboard = getPanelSelectionKeyboard();
        $selectionMessage = buildPanelSelectionMessage($configPath);
        sendmessage($from_id, $selectionMessage, $selectionKeyboard, 'HTML');
    }
    return;
}

if (!$rf_admin_handled && ($text == "🛠 قابلیت های پنل")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "🪚 برای استفاده از این قابلیت یکی از پنل های زیر را انتخاب نمایید", $json_list_marzban_panel, 'HTML');
    step('getlocoption', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getlocoption")) {
    $rf_admin_handled = true;

    update("user", "Processing_value", $text, "id", $from_id);
    $typepanel = select("marzban_panel", "*", "name_panel", $text, "select")['type'];
    if ($typepanel == "marzban") {
        sendmessage($from_id, $textbotlang['users']['selectoption'], $optionathmarzban, 'HTML');
    } elseif ($typepanel == "x-ui_single") {
        sendmessage($from_id, $textbotlang['users']['selectoption'], $optionathx_ui, 'HTML');
    } elseif ($typepanel == "hiddify") {
        sendmessage($from_id, $textbotlang['users']['selectoption'], $optionathx_ui, 'HTML');
    } elseif ($typepanel == "alireza") {
        sendmessage($from_id, $textbotlang['users']['selectoption'], $optionathx_ui, 'HTML');
    } elseif ($typepanel == "alireza_single") {
        sendmessage($from_id, $textbotlang['users']['selectoption'], $optionathx_ui, 'HTML');
    } elseif ($typepanel == "marzneshin") {
        sendmessage($from_id, $textbotlang['users']['selectoption'], $optionathx_ui, 'HTML');
    } elseif ($typepanel == "WGDashboard") {
        sendmessage($from_id, $textbotlang['users']['selectoption'], $optionathx_ui, 'HTML');
    }
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "🖥 مدیریت نود ها" || $datain == "bakcnode")) {
    $rf_admin_handled = true;

    if ($adminnumber != $from_id) {
        sendmessage($from_id, "❌ این بخش فقط در دسترس ادمین اصلی است", null, 'HTML');
        return;
    }
    $nodes = Get_Nodes($user['Processing_value']);
    if (!empty($nodes['error'])) {
        sendmessage($from_id, $nodes['error'], null, 'HTML');
        return;
    }
    if (!empty($nodes['status']) && $nodes['status'] != 200) {
        sendmessage($from_id, "❌  خطایی رخ داده است کد خطا :  {$nodes['status']}", null, 'HTML');
        return;
    }
    $nodes = json_decode($nodes['body'], true);
    if (count($nodes) == 0) {
        sendmessage($from_id, "❌  امکان مشاهده تنظیمات نود ها وجود ندارد", null, 'HTML');
        return;
    }
    $keyboardlistsnode['inline_keyboard'][] = [
        ['text' => "عملیات", 'callback_data' => "actionnode"],
        ['text' => "نام", 'callback_data' => "namenode"]
    ];
    foreach ($nodes as $result) {
        if (!isset($result['id']))
            continue;
        $keyboardlistsnode['inline_keyboard'][] = [
            ['text' => "مدیریت", 'callback_data' => "node_{$result['id']}"],
            ['text' => $result['name'], 'callback_data' => "node_{$result['id']}"],
        ];
    }
    $keyboardlistsnode = json_encode($keyboardlistsnode);
    if ($datain == "bakcnode") {
        Editmessagetext($from_id, $message_id, "📌 در این بخش می توانید نود های پنل مرزبان مدیریت کنید.", $keyboardlistsnode);
    } else {
        sendmessage($from_id, "📌 در این بخش می توانید نود های پنل مرزبان مدیریت کنید.", $keyboardlistsnode, 'HTML');
    }
    return;
}

if (!$rf_admin_handled && (preg_match('/^node_(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $nodeid = $dataget[1];
    update("user", "Processing_value_one", $nodeid, "id", $from_id);
    $node = Get_Node($user['Processing_value'], $nodeid);
    if (!empty($node['error'])) {
        sendmessage($from_id, $node['error'], null, 'HTML');
        return;
    }
    if (!empty($node['status']) && $node['status'] != 200) {
        sendmessage($from_id, "❌  خطایی رخ داده است کد خطا :  {$node['status']}", null, 'HTML');
        return;
    }
    $nodeusage = Get_usage_Nodes($user['Processing_value']);
    if (!empty($nodeusage['error'])) {
        sendmessage($from_id, $nodeusage['error'], null, 'HTML');
        return;
    }
    if (!empty($nodeusage['status']) && $nodeusage['status'] != 200) {
        sendmessage($from_id, "❌  خطایی رخ داده است کد خطا :  {$nodeusage['status']}", null, 'HTML');
        return;
    }
    $node = json_decode($node['body'], true);
    $nodeusage = json_decode($nodeusage['body'], true);
    foreach ($nodeusage['usages'] as $nodeusages) {
        if ($nodeusages['node_id'] == $nodeid) {
            $nodeusage = $nodeusages;
            break;
        }
    }
    $sumvolume = formatBytes($nodeusage['downlink'] + $nodeusage['uplink']);
    $textnode = "📌 اطلاعات نود 

🖥 نام نود :  {$node['name']}
🌍 آیپی نود : {$node['address']}
🔻 پورت نود : {$node['port']}
🔺 پورت api نود : {$node['api_port']}
🔋جمع مصرف نود  : $sumvolume
🔄 ضریب مصرف نود : {$node['usage_coefficient']}
🔵 نسخه xray نود : {$node['xray_version']}
🟢 وضعیت نود : {$node['status']}
    ";
    $backinfoss = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🗂 تغییر نام نود", 'callback_data' => "changenamenode"],
                ['text' => "🔄 تغییر ضریب مصرف نود", 'callback_data' => "changecoefficient"],
            ],
            [
                ['text' => "🌍 تغییر آدرس ایپی نود", 'callback_data' => "changeipnode"],
                ['text' => "♻️ اتصال مجدد نود", 'callback_data' => "reconnectnode"],
            ],
            [
                ['text' => "❌ حذف نود", 'callback_data' => "removenode"],
            ],
            [
                ['text' => "🔙 بازگشت به لیست نود ها", 'callback_data' => "bakcnode"],
            ]
        ]
    ]);
    Editmessagetext($from_id, $message_id, $textnode, $backinfoss);
    return;
}

if (!$rf_admin_handled && ($datain == "changecoefficient")) {
    $rf_admin_handled = true;

    $backinfoss = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🔙 بازگشت به نود ", 'callback_data' => "node_" . $user['Processing_value_one']],
            ]
        ]
    ]);
    $textnode = "📌 ضریب مصرف نودتان را ارسال نمایید.";
    Editmessagetext($from_id, $message_id, $textnode, $backinfoss);
    step("getusage_coefficient", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getusage_coefficient")) {
    $rf_admin_handled = true;

    $config = array(
        'usage_coefficient' => $text
    );
    Modifyuser_node($user['Processing_value'], $user['Processing_value_one'], $config);
    $backinfoss = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🔙 بازگشت به نود ", 'callback_data' => "node_" . $user['Processing_value_one']],
            ]
        ]
    ]);
    sendmessage($from_id, "✅ ضریب مصرف نود با موفقیت ذخیره گردید.", $backinfoss, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "changenamenode")) {
    $rf_admin_handled = true;

    $backinfoss = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🔙 بازگشت به نود ", 'callback_data' => "node_" . $user['Processing_value_one']],
            ]
        ]
    ]);
    $textnode = "📌 نام نودتان را ارسال نمانیید.";
    Editmessagetext($from_id, $message_id, $textnode, $backinfoss);
    step("getnamenode", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getnamenode")) {
    $rf_admin_handled = true;

    $config = array(
        'name' => $text
    );
    Modifyuser_node($user['Processing_value'], $user['Processing_value_one'], $config);
    $backinfoss = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🔙 بازگشت به نود ", 'callback_data' => "node_" . $user['Processing_value_one']],
            ]
        ]
    ]);
    sendmessage($from_id, "✅  نام نود با موفقیت ذخیره گردید.", $backinfoss, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "changeipnode")) {
    $rf_admin_handled = true;

    $backinfoss = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🔙 بازگشت به نود ", 'callback_data' => "node_" . $user['Processing_value_one']],
            ]
        ]
    ]);
    $textnode = "📌 آیپی نود را ارسال نمانیید.";
    Editmessagetext($from_id, $message_id, $textnode, $backinfoss);
    step("getipnodeset", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getipnodeset")) {
    $rf_admin_handled = true;

    $config = array(
        'address' => $text
    );
    Modifyuser_node($user['Processing_value'], $user['Processing_value_one'], $config);
    $backinfoss = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🔙 بازگشت به نود ", 'callback_data' => "node_" . $user['Processing_value_one']],
            ]
        ]
    ]);
    sendmessage($from_id, "✅  آدرس نود با موفقیت ذخیره گردید.", $backinfoss, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "reconnectnode")) {
    $rf_admin_handled = true;

    reconnect_node($user['Processing_value'], $user['Processing_value_one']);
    $backinfoss = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🔙 بازگشت به نود ", 'callback_data' => "node_" . $user['Processing_value_one']],
            ]
        ]
    ]);
    $textnode = "✅ اتصال مجدد نود انجام گردید.";
    Editmessagetext($from_id, $message_id, $textnode, $backinfoss);
    return;
}

if (!$rf_admin_handled && ($datain == "removenode")) {
    $rf_admin_handled = true;

    removenode($user['Processing_value'], $user['Processing_value_one']);
    $backinfoss = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🔙 بازگشت به نود ", 'callback_data' => "bakcnode"],
            ]
        ]
    ]);
    $textnode = "✅ نود با موفقیت حذف گردید";
    Editmessagetext($from_id, $message_id, $textnode, $backinfoss);
    return;
}

