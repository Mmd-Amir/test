<?php
rf_set_module('admin/routes/33_editpanel__startelegram__step_getmainaqstar.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && (preg_match('/^editpanel-(.*)-(.*)-(.*)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $type = $dataget[1];
    $value = $dataget[2];
    $code_panel = $dataget[3];
    if ($type == "stautsconfig") {
        if ($value == "onconfig") {
            $valuenew = "offconfig";
        } else {
            $valuenew = "onconfig";
        }
        update("marzban_panel", "config", $valuenew, "code_panel", $code_panel);
    } elseif ($type == "sublink") {
        if ($value == "onsublink") {
            $valuenew = "offsublink";
        } else {
            $valuenew = "onsublink";
        }
        update("marzban_panel", "sublink", $valuenew, "code_panel", $code_panel);
    } elseif ($type == "statusbuy") {
        if ($value == "active") {
            $valuenew = "disable";
        } else {
            $valuenew = "active";
        }
        update("marzban_panel", "status", $valuenew, "code_panel", $code_panel);
    } elseif ($type == "statustest") {
        if ($value == "ONTestAccount") {
            $valuenew = "OFFTestAccount";
        } else {
            $valuenew = "ONTestAccount";
        }
        update("marzban_panel", "TestAccount", $valuenew, "code_panel", $code_panel);
    } elseif ($type == "connecton") {
        if ($value == "onconecton") {
            $valuenew = "offconecton";
        } else {
            $valuenew = "onconecton";
        }
        update("marzban_panel", "conecton", $valuenew, "code_panel", $code_panel);
    } elseif ($type == "stautsextend") {
        if ($value == "on_extend") {
            $valuenew = "off_extend";
        } else {
            $valuenew = "on_extend";
        }
        update("marzban_panel", "status_extend", $valuenew, "code_panel", $code_panel);
    } elseif ($type == "changeloc") {
        if ($value == "onchangeloc") {
            $valuenew = "offchangeloc";
        } else {
            $valuenew = "onchangeloc";
        }
        update("marzban_panel", "changeloc", $valuenew, "code_panel", $code_panel);
    } elseif ($type == "inbocunddisable") {
        if ($value == "oninbounddisable") {
            $valuenew = "offinbounddisable";
        } else {
            $valuenew = "oninbounddisable";
        }
        update("marzban_panel", "inboundstatus", $valuenew, "code_panel", $code_panel);
    } elseif ($type == "subvip") {
        if ($value == "onsubvip") {
            $valuenew = "offsubvip";
        } else {
            $valuenew = "onsubvip";
        }
        update("marzban_panel", "subvip", $valuenew, "code_panel", $code_panel);
    } elseif ($type == "customstatusf") {
        $panel = select("marzban_panel", "*", "code_panel", $code_panel, "select");
        $customvlume = json_decode($panel['customvolume'], true);
        if ($value == "1") {
            $valuenew = "0";
        } else {
            $valuenew = "1";
        }
        $customvlume['f'] = $valuenew;
        update("marzban_panel", "customvolume", json_encode($customvlume), "code_panel", $code_panel);
    } elseif ($type == "customstatusn") {
        $panel = select("marzban_panel", "*", "code_panel", $code_panel, "select");
        $customvlume = json_decode($panel['customvolume'], true);
        if ($value == "1") {
            $valuenew = "0";
        } else {
            $valuenew = "1";
        }
        $customvlume['n'] = $valuenew;
        update("marzban_panel", "customvolume", json_encode($customvlume), "code_panel", $code_panel);
    } elseif ($type == "customstatusn2") {
        $panel = select("marzban_panel", "*", "code_panel", $code_panel, "select");
        $customvlume = json_decode($panel['customvolume'], true);
        if ($value == "1") {
            $valuenew = "0";
        } else {
            $valuenew = "1";
        }
        $customvlume['n2'] = $valuenew;
        update("marzban_panel", "customvolume", json_encode($customvlume), "code_panel", $code_panel);
    } elseif ($type == "on_hold_Test") {
        if ($value == "0") {
            $valuenew = "1";
        } else {
            $valuenew = "0";
        }
        update("marzban_panel", "on_hold_test", $valuenew, "code_panel", $code_panel);
    }
    $panel = select("marzban_panel", "*", "code_panel", $code_panel, "select");

    $customvlume = json_decode($panel['customvolume'], true);
    if (!is_array($customvlume)) {
        $customvlume = [];
    }
    $customvlume = array_merge([
        'f' => '0',
        'n' => '0',
        'n2' => '0',
    ], $customvlume);

    $statusconfig = [
        'onconfig' => $textbotlang['Admin']['Status']['statuson'],
        'offconfig' => $textbotlang['Admin']['Status']['statusoff'],
    ][$panel['config'] ?? 'offconfig'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $statussublink = [
        'onsublink' => $textbotlang['Admin']['Status']['statuson'],
        'offsublink' => $textbotlang['Admin']['Status']['statusoff'],
    ][$panel['sublink'] ?? 'offsublink'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $statusshowbuy = [
        'active' => $textbotlang['Admin']['Status']['statuson'],
        'disable' => $textbotlang['Admin']['Status']['statusoff'],
    ][$panel['status'] ?? 'disable'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $statusshowtest = [
        'ONTestAccount' => $textbotlang['Admin']['Status']['statuson'],
        'OFFTestAccount' => $textbotlang['Admin']['Status']['statusoff'],
    ][$panel['TestAccount'] ?? 'OFFTestAccount'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $statusconnecton = [
        'onconecton' => $textbotlang['Admin']['Status']['statuson'],
        'offconecton' => $textbotlang['Admin']['Status']['statusoff'],
    ][$panel['conecton'] ?? 'offconecton'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $status_extend = [
        'on_extend' => $textbotlang['Admin']['Status']['statuson'],
        'off_extend' => $textbotlang['Admin']['Status']['statusoff'],
    ][$panel['status_extend'] ?? 'off_extend'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $changeloc = [
        'onchangeloc' => $textbotlang['Admin']['Status']['statuson'],
        'offchangeloc' => $textbotlang['Admin']['Status']['statusoff'],
    ][$panel['changeloc'] ?? 'offchangeloc'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $inbocunddisable = [
        'oninbounddisable' => $textbotlang['Admin']['Status']['statuson'],
        'offinbounddisable' => $textbotlang['Admin']['Status']['statusoff'],
    ][$panel['inboundstatus'] ?? 'offinbounddisable'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $subvip = [
        'onsubvip' => $textbotlang['Admin']['Status']['statuson'],
        'offsubvip' => $textbotlang['Admin']['Status']['statusoff'],
    ][$panel['subvip'] ?? 'offsubvip'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $customstatusf = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff'],
    ][$customvlume['f'] ?? '0'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $customstatusn = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff'],
    ][$customvlume['n'] ?? '0'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $customstatusn2 = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff'],
    ][$customvlume['n2'] ?? '0'] ?? $textbotlang['Admin']['Status']['statusoff'];

    $on_hold_test = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff'],
    ][$panel['on_hold_test'] ?? '0'] ?? $textbotlang['Admin']['Status']['statusoff'];
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
    $Bot_Status['inline_keyboard'][] = [
        ['text' => "❌ بستن", 'callback_data' => 'close_stat']
    ];
    $Bot_Status = json_encode($Bot_Status);
    Editmessagetext($from_id, $message_id, $textbotlang['Admin']['Status']['BotTitle'], $Bot_Status);
    return;
}

if (!$rf_admin_handled && ($datain == "startelegram")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $Startelegram, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ استار")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmainaqstar", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmainaqstar")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $Startelegram, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalancestar");
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ استار")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("maxbalancestar", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "maxbalancestar")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $Startelegram, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalancestar");
    return;
}

if (!$rf_admin_handled && ($text == "⬇️ حداقل مبلغ nowpayment")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداقل مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("getmainaqnowpayment", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmainaqnowpayment")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداقل مبلغ واریزی تنظیم گردید.", $nowpayment_setting_keyboard, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "minbalancenowpayment");
    return;
}

if (!$rf_admin_handled && ($text == "⬆️ حداکثر مبلغ nowpayment")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 حداکثر مبلغ واریزی را ارسال نمایید", $backadmin, 'HTML');
    step("maxbalancenowpayment", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "maxbalancenowpayment")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ حداکثر مبلغ واریزی تنظیم گردید.", $nowpayment_setting_keyboard, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "maxbalancenowpayment");
    return;
}

if (!$rf_admin_handled && ($text == "📚 تنظیم آموزش استار" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌آموزش خود را ارسال نمایید .
۱ - در صورتی که میخواید اموزشی نشان داده نشود عدد 2 را ارسال کنید
۲ - شما می توانید آموزش بصورت فیلم ُ  متن ُ تصویر ارسال نمایید", $backadmin, 'HTML');
    step("gethelpstar", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gethelpstar")) {
    $rf_admin_handled = true;

    if ($text) {
        if (intval($text) == 2) {
            update("PaySetting", "ValuePay", "0", "NamePay", "helpstar");
        } else {
            $data = json_encode(array(
                'type' => "text",
                'text' => $text
            ));
            update("PaySetting", "ValuePay", $data, "NamePay", "helpstar");
        }
    } elseif ($photo) {
        $data = json_encode(array(
            'type' => "photo",
            'text' => $caption,
            'photoid' => $photoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpstar");
    } elseif ($video) {
        $data = json_encode(array(
            'type' => "video",
            'text' => $caption,
            'videoid' => $videoid
        ));
        update("PaySetting", "ValuePay", $data, "NamePay", "helpstar");
    } else {
        sendmessage($from_id, "❌ محتوای ارسال نامعتبر است.", $backadmin, 'HTML');
        return;
    }
    step('home', $from_id);
    sendmessage($from_id, "✅ آموزش با موفقیت ذخیره گردید.", $Startelegram, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "💰 کش بک استار")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 در این بخش می توانید تعیین کنید کاربر پس از پرداخت چه درصدی به عنوان هدیه به حسابش واریز شود. ( برای غیرفعال کردن این قابلیت عدد صفر ارسال کنید )", $backadmin, 'HTML');
    step("chashbackstar", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "chashbackstar")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "✅ مبلغ با موفقیت ذخیره گردید.", $Startelegram, 'HTML');
    step("home", $from_id);
    update("PaySetting", "ValuePay", $text, "NamePay", "chashbackstar");
    return;
}

if (!$rf_admin_handled && ($text == "🔋 تنظیم سریع قیمت حجم")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 قبل ارسال اطلاعات متن زیر را مطالعه فرمایید . 
۱ - این قابلیت برای سرویس دلخواه می باشد.
۲ - در صورتی که تمامی پنل های شما یک قیمت هستند و بجای تنظیم تک تک قیمت ها می توانید با استفاده از این قابلیت بصورت یکجا قیمت ها را تنظیم نمایید.
۳ - با تنظیم قیمت در این بخش قابل بازگشت نیست.


جهت تنظیم قیمت ابتدا قیمت گروه f را ارسال نمایید.", $backadmin, 'HTML');
    step("getpricef", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpricef")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    savedata("clear", "pricef", $text);
    sendmessage($from_id, "📌 قیمت گروه n را ارسال نمایید.", $backadmin, 'HTML');
    step("getpricnn", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpricnn")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    savedata("save", "pricen", $text);
    sendmessage($from_id, "📌 قیمت گروه n2 را ارسال نمایید.", $backadmin, 'HTML');
    step("getpricnn2", $from_id);
    return;
}

