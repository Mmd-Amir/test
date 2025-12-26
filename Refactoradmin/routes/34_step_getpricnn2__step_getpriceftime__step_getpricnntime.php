<?php
rf_set_module('admin/routes/34_step_getpricnn2__step_getpriceftime__step_getpricnntime.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && ($user['step'] == "getpricnn2")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $pricelist = json_encode(array(
        'f' => $userdata['pricef'],
        'n' => $userdata['pricen'],
        'n2' => $text
    ));
    update("marzban_panel", "pricecustomvolume", $pricelist, null, null);
    sendmessage($from_id, "✅ قیمت با موفقیت تنظیم شد", $keyboardadmin, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "⏳ تنظیم سریع قیمت زمان")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 قبل ارسال اطلاعات متن زیر را مطالعه فرمایید . 
۱ - این قابلیت برای سرویس دلخواه می باشد.
۲ - در صورتی که تمامی پنل های شما یک قیمت هستند و بجای تنظیم تک تک قیمت ها می توانید با استفاده از این قابلیت بصورت یکجا قیمت ها را تنظیم نمایید.
۳ - با تنظیم قیمت در این بخش قابل بازگشت نیست.


جهت تنظیم قیمت ابتدا قیمت گروه f را ارسال نمایید.", $backadmin, 'HTML');
    step("getpriceftime", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpriceftime")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    savedata("clear", "pricef", $text);
    sendmessage($from_id, "📌 قیمت گروه n را ارسال نمایید.", $backadmin, 'HTML');
    step("getpricnntime", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpricnntime")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    savedata("save", "pricen", $text);
    sendmessage($from_id, "📌 قیمت گروه n2 را ارسال نمایید.", $backadmin, 'HTML');
    step("getpricnn2time", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpricnn2time")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    $userdata = json_decode($user['Processing_value'], true);
    $pricelist = json_encode(array(
        'f' => $userdata['pricef'],
        'n' => $userdata['pricen'],
        'n2' => $text
    ));
    update("marzban_panel", "pricecustomtime", $pricelist, null, null);
    sendmessage($from_id, "✅ قیمت با موفقیت تنظیم شد", $keyboardadmin, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "changeloclimit")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 یک گزینه را انتخاب نمایید.
۱ - محدودیت کلی کاربر در کل چند بار می تواند تغییر لوکیشن انجام دهد.
۲ - محدودیت رایگان  کاربر از محدودیت کلی چند بار می تواند رایگان تغییر لوکیشن دهد.", $keyboardchangelimit, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "↙️ محدودیت کلی")) {
    $rf_admin_handled = true;

    $limitnumber = json_decode($setting['limitnumber'], true);
    sendmessage($from_id, "📌  محدودیت کلی که کاربر می تواند تغییر لوکیشن انجام دهد را ارسال کنید توجه داشته باشید این محدودیت برای تمام کانفیگ ها  است
محدودیت فعلی : {$limitnumber['all']}", $backadmin, 'HTML');
    step("limitchangeall", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "limitchangeall")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ محدودیت با موفقیت تنظیم شد.", $keyboardchangelimit, 'HTML');
    step("home", $from_id);
    $value = json_decode($setting['limitnumber'], true);
    $value['all'] = intval($text);
    update("setting", "limitnumber", json_encode($value), null, null);
    return;
}

if (!$rf_admin_handled && ($text == "🆓 محدودیت رایگان")) {
    $rf_admin_handled = true;

    $limitnumber = json_decode($setting['limitnumber'], true);
    sendmessage($from_id, "📌  محدودیت رایگانی که کاربر می تواند تغییر لوکیشن انجام دهد را ارسال کنید توجه داشته باشید این محدودیت برای تمام کانفیگ ها  است
محدودیت فعلی : {$limitnumber['free']}", $backadmin, 'HTML');
    step("limitfreechangefree", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "limitfreechangefree")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ محدودیت با موفقیت تنظیم شد.", $keyboardchangelimit, 'HTML');
    step("home", $from_id);
    $value = json_decode($setting['limitnumber'], true);
    $value['free'] = intval($text);
    update("setting", "limitnumber", json_encode($value), null, null);
    return;
}

if (!$rf_admin_handled && ($text == "🔄 ریست محدودیت کل کاربران")) {
    $rf_admin_handled = true;

    $keyboarddata = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "تایید و صفر شدن", 'callback_data' => 'reasetchangeloc'],
            ],
        ]
    ]);
    sendmessage($from_id, "📌 با تأیید گزینه زیر، تمام تغییر لوکیشن هایی که توسط کاربر انجام شده است صفر خواهد شد. در صورت موافقت، روی گزینه زیر کلیک کنید.", $keyboarddata, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "reasetchangeloc")) {
    $rf_admin_handled = true;

    Editmessagetext($from_id, $message_id, "✅ تمامی محدودیت کاربران صفر شد.", null);
    update("user", "limitchangeloc", "0", null, null);
    return;
}

if (!$rf_admin_handled && (preg_match('/changeloclimitbyuser_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    savedata("clear", "id_user", $id_user);
    sendmessage($from_id, "📌 محدودیت جدیدی که میخواهید برای کاربر تنظیم کنید را ارسال کنید توجه داشته باشید این قابلیت تعداد تعییر لوکیشن انجام شده را تغییر میدهد", $backadmin, 'HTML');
    step("getlimitchangenewbyuser", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getlimitchangenewbyuser")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    step("home", $from_id);
    update("user", "limitchangeloc", $text, "id", $userdate['id_user']);
    sendmessage($from_id, "✅ تعداد استفاده کاربر با موفقیت ذخیره گردید.", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/hidepanel_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    savedata("clear", "id_user", $id_user);
    sendmessage($from_id, "❌ پنل هایی که می خواهید برای این نماینده نشان داده نشود از دکمه  زیر انتخاب نمایید بعد از انتخاب دستور /finish را ارسال کنید تا ذخیره شود.", $json_list_marzban_panel, 'HTML');
    step("getpanelhidebotsaz", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "/finish")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ ذخیره پنل ها با موفقیت انجام و پنل های برای کاربر مخفی شد.", $keyboardadmin, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpanelhidebotsaz")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    $list_panel = json_decode(select("botsaz", "hide_panel", "id_user", $userdata['id_user'], "select")['hide_panel'], true);
    if (in_array($text, $list_panel)) {
        sendmessage($from_id, "❌ پنل از قبل اضافه شده است", null, 'HTML');
        return;
    }
    $list_panel[] = $text;
    update("botsaz", "hide_panel", json_encode($list_panel), "id_user", $userdata['id_user']);
    sendmessage($from_id, "✅ پنل انتخاب شد  پس از اتمام دستور /finish را ارسال نمایید تا ذخیره نهایی شود.", null, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/removehide_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    global $list_hide_panel;
    $id_user = $datagetr[1];
    savedata("clear", "id_user", $id_user);
    $list_panel = json_decode(select("botsaz", "hide_panel", "id_user", $id_user, "select")['hide_panel'], true);
    $list_hide_panel = [
        'keyboard' => [],
        'resize_keyboard' => true,
    ];
    foreach ($list_panel as $panelname) {
        $list_hide_panel['keyboard'][] = [
            ['text' => $panelname]
        ];
    }
    $list_hide_panel['keyboard'][] = [
        ['text' => $textbotlang['Admin']['backadmin']],
    ];
    $list_hide_panel = json_encode($list_hide_panel);
    sendmessage($from_id, "❌ از لیست زیر پنل هایی که میخواهید مجددا در ربات نماینده نشان داده شود را  انتخاب نمایید بعد از انتخاب تمامی پنل ها  دستور /remove را ارسال کنید تا ذخیره شود.", $list_hide_panel, 'HTML');
    step("getremovehidepanel", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "/remove")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "✅ نمایش پنل ها با موفقیت انجام و پنل های برای کاربر فعال شد.", $keyboardadmin, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getremovehidepanel")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    $list_panel = json_decode(select("botsaz", "hide_panel", "id_user", $userdata['id_user'], "select")['hide_panel'], true);
    if (!in_array($text, $list_panel)) {
        sendmessage($from_id, "❌ پنل در لیست وجود ندارد", null, 'HTML');
        return;
    }
    $count = 0;
    foreach ($list_panel as $panel) {
        if ($panel == $text) {
            unset($list_panel[$count]);
            break;
        }
        $count += 1;
    }
    $list_panel = array_values($list_panel);
    update("botsaz", "hide_panel", json_encode($list_panel), "id_user", $userdata['id_user']);
    sendmessage($from_id, "✅ پنل انتخاب شد  پس از اتمام دستور /remove را ارسال نمایید تا ذخیره نهایی شود.", null, 'HTML');
    return;
}

if (!$rf_admin_handled && ($datain == "voloume_or_day_all")) {
    $rf_admin_handled = true;

    $userslistData = '[]';
    if (is_file('cronbot/username.json')) {
        $fileContents = file_get_contents('cronbot/username.json');
        if ($fileContents !== false && $fileContents !== '') {
            $userslistData = $fileContents;
        }
    }
    $userslist = json_decode($userslistData, true);
    if (is_array($userslist) && count($userslist) != 0) {
        sendmessage($from_id, "❌ سیستم ارسال هدیه درحال انجام عملیات است پس از پایان و اطلاع رسانی  می توانید پیام جدید را ارسال نمایید.", $keyboardadmin, 'HTML');
        return;
    }
    sendmessage($from_id, "📌 برای سرویس های کدام پنل میخواهید حجم یا زمان هدیه دهید؟", $json_list_marzban_panel, "html");
    step("getpanelgift", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getpanelgift")) {
    $rf_admin_handled = true;

    $panel = select("marzban_panel", "*", "name_panel", $text, "count");
    if ($panel == 0) {
        sendmessage($from_id, "❌ پنل وجود ندارد", null, "html");
        return;
    }
    savedata("clear", "name_panel", $text);
    $keyboardstatistics = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🔋 حجم", 'callback_data' => 'typegift_volume'],
                ['text' => "⏳ زمان", 'callback_data' => 'typegift_day'],
            ],
        ]
    ]);
    sendmessage($from_id, "📌 یکی از هدیه های زیر را انتخاب نمایید.", $keyboardstatistics, "html");
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/typegift_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $typegift = $datagetr[1];
    savedata("save", "typegift", $typegift);
    deletemessage($from_id, $message_id);
    if ($typegift == "volume") {
        sendmessage($from_id, "📌 چند گیگ حجم می خواهید به سرویس های کاربر اضافه شود", $backadmin, "html");
    } else {
        sendmessage($from_id, "📌 چند روز می خواهید به سرویس های کاربران اضافه شود", $backadmin, "html");
    }
    step("getvaluegift", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getvaluegift")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    savedata("save", "value", $text);
    sendmessage($from_id, "📌 متنی که می خواهید برای کاربر ارسال شود را ارسال کنید", $backadmin, "html");
    step("gettextgift", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettextgift")) {
    $rf_admin_handled = true;

    savedata("save", "text", $text);
    savedata("save", "id_admin", $from_id);
    $keyboardstatistics = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "✅ تایید و شروع فرآیند", 'callback_data' => 'startgift'],
            ],
        ]
    ]);
    sendmessage($from_id, "📌 ادمین عزیز با تایید بر روی گزینه زیر فرآیند اعمال هدیه ها آغاز خواهد شد توجه داشته باشید با توجه به محدودیت ها اعمال هدیه زمان بر خواهد بود.", $keyboardstatistics, "html");
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($datain == "startgift")) {
    $rf_admin_handled = true;

    $keyboardstatistics = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "❌ لفو ارسال هدیه", 'callback_data' => 'cancel_gift'],
            ],
        ]
    ]);
    $userdata = json_decode($user['Processing_value'], true);
    if (!isset($userdata['typegift'])) {
        sendmessage($from_id, "❌ خطایی رخ داده است مراحل را از اول طی کنید.", $keyboardstatistics, "html");
        return;
    }
    $message_id = Editmessagetext($from_id, $message_id, "✅ عملیات ارسال هدیه با موفقیت آغاز گردید پس از اضافه شدن و اتمام به شما اطلاع داده می شود.", $keyboardstatistics);
    $userdata['id_message'] = $message_id['result']['message_id'];
    $stmt = $pdo->prepare("SELECT username FROM invoice WHERE  (status = 'active' OR status = 'end_of_time'  OR status = 'end_of_volume' OR status = 'sendedwarn' OR Status = 'send_on_hold') AND Service_location = '{$userdata['name_panel']}' AND name_product != 'سرویس تست'");
    $stmt->execute();
    $userslist = json_encode($stmt->fetchAll());
    file_put_contents('cronbot/gift', json_encode($userdata));
    file_put_contents('cronbot/username.json', $userslist);
    return;
}

if (!$rf_admin_handled && ($datain == "cancel_gift")) {
    $rf_admin_handled = true;

    unlink('cronbot/username.json');
    unlink('cronbot/gift');
    deletemessage($from_id, $message_id);
    sendmessage($from_id, "📌 ارسال هدیه لغو گردید.", null, 'HTML');
    return;
}

if (!$rf_admin_handled && (preg_match('/expireset_(\w+)/', $datain, $datagetr))) {
    $rf_admin_handled = true;

    $id_user = $datagetr[1];
    savedata("clear", "id_user", $id_user);
    sendmessage($from_id, "🕘 زمان انقضا نمایندگی را ارسال نمایید. پس از پایان تعداد روز تعیین شده کاربر از حالت نمایندگی خارج شده و گروه کاربر f خواهد شد.
توجه داشته باشید این قابلیت ارتباطی با قابلیت ربات ساز یا ربات فروش نماینده ندارد و فقط مربوط به ربات اصلی شما است

📌 تعداد روز را ارسال نمایید", $backadmin, 'HTML');
    step("gettime_expire_agent", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "gettime_expire_agent")) {
    $rf_admin_handled = true;

    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    step("home", $from_id);
    $userdate = json_decode($user['Processing_value'], true);
    $timestamp = time() + (intval(value: $text) * 86400);
    update("user", "expire", $timestamp, "id", $userdate['id_user']);
    sendmessage($from_id, "✅ تاریخ انقضا تنظیم شد.
📌 پس از پایان زمان گروه کاربری کاربر به f تغییر داده می شود و به کاربر اطلاع داده می شود.", $keyboardadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "♻️ نمایش گروهی شماره کارت")) {
    $rf_admin_handled = true;

    sendmessage($from_id, "📌 لیست آیدی هایی که  می خواهید شماره کارت برایشان نشان داده شود را ارسال شود 
مثال : 
1234435423
23423131", $backadmin, 'HTML');
    step("getlistidcart", $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getlistidcart")) {
    $rf_admin_handled = true;

    $list = explode("\n", $text);
    foreach ($list as $id_user) {
        if (!in_array($id_user, $users_ids)) {
            sendmessage($from_id, "📌 کاربر با آیدی عددی $id_user در  دیتابیس وجود ندارد", $backadmin, 'HTML');
            continue;
        }
        update("user", "cardpayment", "1", "id", $id_user);
    }
    sendmessage($from_id, "✅ شماره کارت برای کاربران ارسال شده فعال گردید.", $CartManage, 'HTML');
    step("home", $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📄 خروجی افراد شماره کارت فعال")) {
    $rf_admin_handled = true;

    $listusers = select("user", "id", "cardpayment", "1", "fetchAll");
    if (!$listusers) {
        sendmessage($from_id, "📌 برای کاربری شماره کارت فعال نشده است", $CartManage, 'HTML');
        return;
    }
    $filename = 'cartlist.txt';
    foreach ($listusers as $id_user) {
        file_put_contents($filename, $id_user['id'] . "\n", FILE_APPEND);
    }
    sendDocument($from_id, $filename, "🪪 لیست کاربرانی که شماره کارت برای آنها فعال است");
    unlink($filename);
    return;
}

