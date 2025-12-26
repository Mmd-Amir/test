<?php
rf_set_module('admin/routes/07_step_text_roll.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($text == "⚙️ وضعیت قابلیت ها" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    if ($setting['Bot_Status'] == "✅  ربات روشن است") {
        update("setting", "Bot_Status", "botstatuson");
    } elseif ($setting['Bot_Status'] == "❌ ربات خاموش است") {
        update("setting", "Bot_Status", "botstatusoff");
    }
    if ($setting['roll_Status'] == "✅ تایید قانون روشن است") {
        update("setting", "roll_Status", "rolleon");
    } elseif ($setting['roll_Status'] == "❌ تایید قوانین خاموش است") {
        update("setting", "roll_Status", "rolleoff");
    }
    if ($setting['get_number'] == "✅ تایید شماره موبایل روشن است") {
        update("setting", "get_number", "onAuthenticationphone");
    } elseif ($setting['get_number'] == "❌ احرازهویت شماره تماس غیرفعال است") {
        update("setting", "get_number", "offAuthenticationphone");
    }
    if ($setting['iran_number'] == "✅ احرازشماره ایرانی روشن است") {
        update("setting", "iran_number", "onAuthenticationiran");
    } elseif ($setting['iran_number'] == "❌ بررسی شماره ایرانی غیرفعال است") {
        update("setting", "iran_number", "offAuthenticationiran");
    }
    $status_cron = json_decode($setting['cron_status'], true);
    $setting = select("setting", "*", null, null, "select");
    $name_status = [
        'botstatuson' => $textbotlang['Admin']['Status']['statuson'],
        'botstatusoff' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['Bot_Status']];
    $name_status_username = [
        'onnotuser' => $textbotlang['Admin']['Status']['statuson'],
        'offnotuser' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['NotUser']];
    $name_status_notifnewuser = [
        'onnewuser' => $textbotlang['Admin']['Status']['statuson'],
        'offnewuser' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['statusnewuser']];
    $name_status_showagent = [
        'onrequestagent' => $textbotlang['Admin']['Status']['statuson'],
        'offrequestagent' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['statusagentrequest']];
    $name_status_role = [
        'rolleon' => $textbotlang['Admin']['Status']['statuson'],
        'rolleoff' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['roll_Status']];
    $Authenticationphone = [
        'onAuthenticationphone' => $textbotlang['Admin']['Status']['statuson'],
        'offAuthenticationphone' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['get_number']];
    $Authenticationiran = [
        'onAuthenticationiran' => $textbotlang['Admin']['Status']['statuson'],
        'offAuthenticationiran' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['iran_number']];
    $statusinline = [
        'oninline' => $textbotlang['Admin']['Status']['statuson'],
        'offinline' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['inlinebtnmain']];
    $statusverify = [
        'onverify' => $textbotlang['Admin']['Status']['statuson'],
        'offverify' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['verifystart']];
    $statuspvsupport = [
        'onpvsupport' => $textbotlang['Admin']['Status']['statuson'],
        'offpvsupport' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['statussupportpv']];
    $statusnameconfig = [
        'onnamecustom' => $textbotlang['Admin']['Status']['statuson'],
        'offnamecustom' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['statusnamecustom']];
    $statusnamebulk = [
        'onbulk' => $textbotlang['Admin']['Status']['statuson'],
        'offbulk' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['bulkbuy']];
    $statusverifybyuser = [
        'onverify' => $textbotlang['Admin']['Status']['statuson'],
        'offverify' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['verifybucodeuser']];
    $score = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['scorestatus']];
    $wheel_luck = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['wheelـluck']];
    $refralstatus = [
        'onaffiliates' => $textbotlang['Admin']['Status']['statuson'],
        'offaffiliates' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['affiliatesstatus']];
    $btnstatuscategory = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['categoryhelp']];
    $btnstatuslinkapp = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['linkappstatus']];
    $cronteststatustext = [
        true => $textbotlang['Admin']['Status']['statuson'],
        false => $textbotlang['Admin']['Status']['statusoff']
    ][$status_cron['test']];
    $crondaystatustext = [
        true => $textbotlang['Admin']['Status']['statuson'],
        false => $textbotlang['Admin']['Status']['statusoff']
    ][$status_cron['day']];
    $cronvolumestatustext = [
        true => $textbotlang['Admin']['Status']['statuson'],
        false => $textbotlang['Admin']['Status']['statusoff']
    ][$status_cron['volume']];
    $cronremovestatustext = [
        true => $textbotlang['Admin']['Status']['statuson'],
        false => $textbotlang['Admin']['Status']['statusoff']
    ][$status_cron['remove']];
    $cronremovevolumestatustext = [
        true => $textbotlang['Admin']['Status']['statuson'],
        false => $textbotlang['Admin']['Status']['statusoff']
    ][$status_cron['remove_volume']];
    $cronuptime_nodestatustext = [
        true => $textbotlang['Admin']['Status']['statuson'],
        false => $textbotlang['Admin']['Status']['statusoff']
    ][$status_cron['uptime_node']];
    $cronuptime_panelstatustext = [
        true => $textbotlang['Admin']['Status']['statuson'],
        false => $textbotlang['Admin']['Status']['statusoff']
    ][$status_cron['uptime_panel']];
    $cronon_holdtext = [
        true => $textbotlang['Admin']['Status']['statuson'],
        false => $textbotlang['Admin']['Status']['statusoff']
    ][$status_cron['on_hold']];
    $languagestatus = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['languageen']];
    $languagestatusru = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['languageru']];
    $wheelagent = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['wheelagent']];
    $Lotteryagent = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['Lotteryagent']];
    $statusfirstwheel = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['statusfirstwheel']];
    $statuslimitchangeloc = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['statuslimitchangeloc']];
    $statusDebtsettlement = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['Debtsettlement']];
    $statusDice = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['Dice']];
    $statusnotef = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['statusnoteforf']];
    $status_copy_cart = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['statuscopycart']];
    $keyboard_config_text = [
        '1' => $textbotlang['Admin']['Status']['statuson'],
        '0' => $textbotlang['Admin']['Status']['statusoff']
    ][$setting['status_keyboard_config']];
    $Bot_Status = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['Admin']['Status']['subject'], 'callback_data' => "subject"],
                ['text' => $textbotlang['Admin']['Status']['statussubject'], 'callback_data' => "subjectde"],
            ],
            [
                ['text' => $name_status, 'callback_data' => "editstsuts-statusbot-{$setting['Bot_Status']}"],
                ['text' => $textbotlang['Admin']['Status']['stautsbot'], 'callback_data' => "statusbot"],
            ],
            [
                ['text' => $name_status_username, 'callback_data' => "editstsuts-usernamebtn-{$setting['NotUser']}"],
                ['text' => $textbotlang['Admin']['Status']['statususernamebtn'], 'callback_data' => "usernamebtn"],
            ],
            [
                ['text' => $name_status_notifnewuser, 'callback_data' => "editstsuts-notifnew-{$setting['statusnewuser']}"],
                ['text' => $textbotlang['Admin']['Status']['statusnotifnewuser'], 'callback_data' => "statusnewuser"],
            ],
            [
                ['text' => $name_status_showagent, 'callback_data' => "editstsuts-showagent-{$setting['statusagentrequest']}"],
                ['text' => $textbotlang['Admin']['Status']['statusshowagent'], 'callback_data' => "statusnewuser"],
            ],
            [
                ['text' => $name_status_role, 'callback_data' => "editstsuts-role-{$setting['roll_Status']}"],
                ['text' => $textbotlang['Admin']['Status']['stautsrolee'], 'callback_data' => "stautsrolee"],
            ],
            [
                ['text' => $Authenticationphone, 'callback_data' => "editstsuts-Authenticationphone-{$setting['get_number']}"],
                ['text' => $textbotlang['Admin']['Status']['Authenticationphone'], 'callback_data' => "Authenticationphone"],
            ],
            [
                ['text' => $Authenticationiran, 'callback_data' => "editstsuts-Authenticationiran-{$setting['iran_number']}"],
                ['text' => $textbotlang['Admin']['Status']['Authenticationiran'], 'callback_data' => "Authenticationiran"],
            ],
            [
                ['text' => $statusinline, 'callback_data' => "editstsuts-inlinebtnmain-{$setting['inlinebtnmain']}"],
                ['text' => $textbotlang['Admin']['Status']['inlinebtns'], 'callback_data' => "inlinebtnmain"],
            ],
            [
                ['text' => $statusverify, 'callback_data' => "editstsuts-verifystart-{$setting['verifystart']}"],
                ['text' => "🔒 احراز هویت", 'callback_data' => "verify"],
            ],
            [
                ['text' => $statuspvsupport, 'callback_data' => "editstsuts-statussupportpv-{$setting['statussupportpv']}"],
                ['text' => "👤 پشتیبانی در پیوی", 'callback_data' => "statussupportpv"],
            ],
            [
                ['text' => $statusnameconfig, 'callback_data' => "editstsuts-statusnamecustom-{$setting['statusnamecustom']}"],
                ['text' => "📨 یادداشت کانفیگ", 'callback_data' => "statusnamecustom"],
            ],
            [
                ['text' => $statusnotef, 'callback_data' => "editstsuts-statusnamecustomf-{$setting['statusnoteforf']}"],
                ['text' => "📨 یادداشت کاربر عادی", 'callback_data' => "statusnamecustomf"],
            ],
            [
                ['text' => $statusnamebulk, 'callback_data' => "editstsuts-bulkbuy-{$setting['bulkbuy']}"],
                ['text' => "🛍 وضعیت خرید عمده", 'callback_data' => "bulkbuy"],
            ],
            [
                ['text' => $statusverifybyuser, 'callback_data' => "editstsuts-verifybyuser-{$setting['verifybucodeuser']}"],
                ['text' => "🔑 احراز هویت با لینک", 'callback_data' => "verifybyuser"],
            ],
            [
                ['text' => $btnstatuscategory, 'callback_data' => "editstsuts-btn_status_category-{$setting['categoryhelp']}"],
                ['text' => "📗دسته بندی آموزش", 'callback_data' => "btn_status_category"],
            ],
            [
                ['text' => $wheelagent, 'callback_data' => "editstsuts-wheelagent-{$setting['wheelagent']}"],
                ['text' => "🎲 گردونه شانس  نمایندگان", 'callback_data' => "wheelagent"],
            ],
            [
                ['text' => $keyboard_config_text, 'callback_data' => "editstsuts-keyconfig-{$setting['status_keyboard_config']}"],
                ['text' => "🔗 کیبورد کانفیگی", 'callback_data' => "keyconfig"],
            ],
            [
                ['text' => $statusDice, 'callback_data' => "editstsuts-Dice-{$setting['Dice']}"],
                ['text' => "🎰 نمایش تاس", 'callback_data' => "Dice"],
            ],
            [
                ['text' => $statusfirstwheel, 'callback_data' => "editstsuts-wheelagentfirst-{$setting['statusfirstwheel']}"],
                ['text' => "🎲 گردونه شانس خرید اول", 'callback_data' => "wheelagentfirst"],
            ],
            [
                ['text' => $Lotteryagent, 'callback_data' => "editstsuts-Lotteryagent-{$setting['Lotteryagent']}"],
                ['text' => "🎁 قرعه کشی نمایندگان", 'callback_data' => "Lotteryagent"],
            ],
            [
                ['text' => $statusDebtsettlement, 'callback_data' => "editstsuts-Debtsettlement-{$setting['Debtsettlement']}"],
                ['text' => "💎 تسویه بدهی", 'callback_data' => "Debtsettlement"],
            ],
            [
                ['text' => $status_copy_cart, 'callback_data' => "editstsuts-compycart-{$setting['statuscopycart']}"],
                ['text' => "💳 کپی شماره کارت", 'callback_data' => "copycart"],
            ],
            [
                ['text' => $cronteststatustext, 'callback_data' => "editstsuts-crontest-{$status_cron['test']}"],
                ['text' => "🔓کرون تست", 'callback_data' => "none"],
            ],
            [
                ['text' => $cronuptime_nodestatustext, 'callback_data' => "editstsuts-uptime_node-{$status_cron['uptime_node']}"],
                ['text' => "🎛 آپتایم نود", 'callback_data' => "none"],
            ],
            [
                ['text' => $cronuptime_panelstatustext, 'callback_data' => "editstsuts-uptime_panel-{$status_cron['uptime_panel']}"],
                ['text' => "🎛 آپتایم پنل", 'callback_data' => "none"],
            ],
            [
                ['text' => "⚙️ زمان هشدار", 'callback_data' => "settimecornday"],
                ['text' => $crondaystatustext, 'callback_data' => "editstsuts-cronday-{$status_cron['day']}"],
                ['text' => "🕚 کرون زمان", 'callback_data' => "none"],
            ],
            [
                ['text' => "⚙️ زمان اولین اتصال", 'callback_data' => "setting_on_holdcron"],
                ['text' => $cronon_holdtext, 'callback_data' => "editstsuts-on_hold-{$status_cron['on_hold']}"],
                ['text' => "🕚 کرون اولین اتصال", 'callback_data' => "none"],
            ],
            [
                ['text' => "⚙️ حجم هشدار", 'callback_data' => "settimecornvolume"],
                ['text' => $cronvolumestatustext, 'callback_data' => "editstsuts-cronvolume-{$status_cron['volume']}"],
                ['text' => "🔋 کرون حجم", 'callback_data' => "none"],
            ],
            [
                ['text' => "⚙️ زمان حذف", 'callback_data' => "settimecornremove"],
                ['text' => $cronremovestatustext, 'callback_data' => "editstsuts-notifremove-{$status_cron['remove']}"],
                ['text' => "❌ کرون حذف", 'callback_data' => "none"],
            ],
            [
                ['text' => "⚙️ زمان حذف", 'callback_data' => "settimecornremovevolume"],
                ['text' => $cronremovevolumestatustext, 'callback_data' => "editstsuts-notifremove_volume-{$status_cron['remove_volume']}"],
                ['text' => "❌ کرون حذف حجم", 'callback_data' => "none"],
            ],
            [
                ['text' => "⚙️ مدیریت", 'callback_data' => "cronjobs_settings"],
                ['text' => "⏱ نمایش لیست", 'callback_data' => "cronjobs_settings"],
                ['text' => "زمان‌بندی کرون‌ها", 'callback_data' => "none"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "linkappsetting"],
                ['text' => $btnstatuslinkapp, 'callback_data' => "editstsuts-linkappstatus-{$setting['linkappstatus']}"],
                ['text' => "🔗لینک دانلود برنامه", 'callback_data' => "linkappstatus"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "scoresetting"],
                ['text' => $score, 'callback_data' => "editstsuts-score-{$setting['scorestatus']}"],
                ['text' => "🎁 قرعه کشی شبانه", 'callback_data' => "score"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "gradonhshans"],
                ['text' => $wheel_luck, 'callback_data' => "editstsuts-wheel_luck-{$setting['wheelـluck']}"],
                ['text' => "🎲 گردونه شانس", 'callback_data' => "wheel_luck"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "settingaffiliatesf"],
                ['text' => $refralstatus, 'callback_data' => "editstsuts-affiliatesstatus-{$setting['affiliatesstatus']}"],
                ['text' => "🎁زیرمجموعه", 'callback_data' => "affiliatesstatus"],
            ],
            [
                ['text' => "⚙️ تنظیمات", 'callback_data' => "changeloclimit"],
                ['text' => $statuslimitchangeloc, 'callback_data' => "editstsuts-changeloc-{$setting['statuslimitchangeloc']}"],
                ['text' => "🌍 محدودیت تغییر لوکیشن", 'callback_data' => "changeloc"],
            ],
            [
                ['text' => "❌ بستن", 'callback_data' => 'close_stat']
            ]
        ]
    ]);
    sendmessage($from_id, $textbotlang['Admin']['Status']['BotTitle'], $Bot_Status, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "⚖️ متن قانون" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['ChangeTextGet'] . $datatextbot['text_roll'], $backadmin, 'HTML');
    step('text_roll', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "text_roll")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SaveText'], $textbot, 'HTML');
    update("textbot", "text", $text, "id_text", "text_roll");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📣 گزارشات ربات" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    $textreports = "📣در این بخش میتوانید آیدی عددی گروه را برای ارسال اعلان ارسال نمایید
آموزش تنظیم گروه :
1 - ابتدا یک گروه  بسازید 
2 - ربات  @myidbot را عضو گروه کنید و دستور /getgroupid@myidbot داخل گروه ارسال کنید 
3 - حالت تاپیک یا انجمن گروه را از تنظیمات گروه روشن کنید
4 - ربات خودتان را ادمین گروه کنید 
5 - آیدی عددی ارسال شده را در ربات ارسال کنید.

آیدی عددی فعلی شما: {$setting['Channel_Report']}";
    sendmessage($from_id, $textreports, $backadmin, 'HTML');
    step('addchannelid', $from_id);

    return;
}

