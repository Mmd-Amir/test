<?php
rf_set_module('routes/user/03_subscription_qrcode.php');
if (!$rf_chain1_handled && (preg_match('/subscriptionurl_(\w+)/', $datain, $dataget) || (is_string($text) && strpos($text, "/sub ") !== false))) {
    $rf_chain1_handled = true;
    if (is_string($text) && $text !== '' && $text[0] == "/") {
        $id_invoice = explode(' ', $text)[1];
        $nameloc = select("invoice", "*", "username", $id_invoice, "select");
        if ($nameloc['id_user'] != $from_id) {
            $nameloc = false;
        }
    } else {
        $id_invoice = $dataget[1];
        $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    }
    if ($nameloc == false)
        rf_stop();
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $Check_token = token_panel($marzban_list_get['url_panel'], $marzban_list_get['username_panel'], $marzban_list_get['password_panel']);
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $nameloc['username']);
    if ($DataUserOut['status'] == "Unsuccessful") {
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], null, 'html');
        rf_stop();
    }
    $subscriptionurl = $DataUserOut['subscription_url'];
    if ($marzban_list_get['type'] == "WGDashboard") {
        $textsub = "qrcode اشتراک شما";
    } else {
        $textsub = "
{$textbotlang['users']['stateus']['linksub']}
            
<code>$subscriptionurl</code>";
    }
    $bakinfos = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "productcheckdata"],
            ]
        ]
    ]);
    update("user", "Processing_value", $nameloc['username'], "id", $from_id);
    $subscriptionurl = $DataUserOut['subscription_url'];
    $randomString = bin2hex(random_bytes(3));
    $urlimage = "$from_id$randomString.png";
    $qrCode = createqrcode($subscriptionurl);
    file_put_contents($urlimage, $qrCode->getString());
    if (!addBackgroundImage($urlimage, $qrCode, 'images.jpg')) {
        error_log("Unable to apply background image for QR code using path 'images.jpg'");
    }
    telegram('sendphoto', [
        'chat_id' => $from_id,
        'photo' => new CURLFile($urlimage),
        'reply_markup' => $bakinfos,
        'caption' => $textsub,
        'parse_mode' => "HTML",
    ]);
    unlink($urlimage);
    if ($marzban_list_get['type'] == "WGDashboard") {
        $urlimage = "{$marzban_list_get['inboundid']}_{$nameloc['username']}.conf";
        file_put_contents($urlimage, $DataUserOut['subscription_url']);
        sendDocument($from_id, $urlimage, "⚙️ کانفیگ شما");
        unlink($urlimage);
    }
}
if (!$rf_chain1_handled && (preg_match('/removeauto-(\w+)/', $datain, $dataget))) {
    $rf_chain1_handled = true;
    $id_invoice = $dataget[1];
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $ManagePanel->RemoveUser($nameloc['Service_location'], $nameloc['username']);
    update('invoice', 'status', 'removebyuser', 'id_invoice', $id_invoice);
    $tetremove = "ادمین عزیز یک کاربر سرویس خود را پس از پایان حجم یا زمان حدف کرده است
نام کاربری کانفیک : {$nameloc['username']}";
    if (strlen($setting['Channel_Report'] ?? '') > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherreport,
            'text' => $tetremove,
            'parse_mode' => "HTML"
        ]);
    }
        sendmessage($from_id, "📌 سرویس با موفقیت حذف شد", null, 'html');
}
if (!$rf_chain1_handled && (preg_match('/deletelist-(\w+)/', $datain, $dataget))) {
    $rf_chain1_handled = true;
    $id_invoice = $dataget[1];
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    if (is_array($nameloc) && $nameloc['id_user'] == $from_id) {
        if (function_exists('deleteInvoiceFromList')) {
            deleteInvoiceFromList($id_invoice, $from_id);
        } else {
            $stmtDelete = $pdo->prepare("DELETE FROM invoice WHERE id_invoice = :invoice_id AND id_user = :user_id");
            $stmtDelete->bindParam(':invoice_id', $id_invoice);
            $stmtDelete->bindParam(':user_id', $from_id);
            $stmtDelete->execute();
        }
        sendmessage($from_id, "📌 سرویس از لیست شما حذف شد", null, 'html');
    } else {
        sendmessage($from_id, "❌ امکان حذف سرویس وجود ندارد.", null, 'html');
    }
}
if (!$rf_chain1_handled && (preg_match('/config_(\w+)/', $datain, $dataget) || (is_string($text) && strpos($text, "/link ") !== false))) {
    $rf_chain1_handled = true;
    $textCommand = is_string($text) ? $text : '';
    if ($textCommand !== '' && $textCommand[0] === "/") {
        $parts = explode(' ', $textCommand, 2);
        $id_invoice = $parts[1] ?? null;
        if ($id_invoice === null) {
            sendmessage($from_id, $textbotlang['users']['stateus']['UserNotFound'], null, 'html');
            rf_stop();
        }
        $nameloc = select("invoice", "*", "username", $id_invoice, "select");
        if ($nameloc['id_user'] != $from_id) {
            $nameloc = false;
        }
    } else {
        $id_invoice = $dataget[1];
        $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    }
    if ($nameloc == false) {
        sendmessage($from_id, $textbotlang['users']['stateus']['UserNotFound'], null, 'html');
        rf_stop();
    }
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $nameloc['username']);
    if ($DataUserOut['status'] == "Unsuccessful") {
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], null, 'html');
        rf_stop();
    }
    if (!is_array($DataUserOut['links'])) {
        sendmessage($from_id, "❌  خطا در خواندن اطلاعات کانفیگ با پشتیبانی در ارتباط باشید.", null, 'html');
        rf_stop();
    }
    Editmessagetext($from_id, $message_id, "📌 از لیست زیر یک کانفیگ را انتخاب استفاده نمایید.", keyboard_config($DataUserOut['links'], $nameloc['id_invoice']));
}
if (!$rf_chain1_handled && (preg_match('/configget_(.*)_(.*)/', $datain, $dataget))) {
    $rf_chain1_handled = true;
    $id_invoice = $dataget[1];
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    if ($nameloc == false) {
        sendmessage($from_id, $textbotlang['users']['stateus']['UserNotFound'], null, 'html');
        rf_stop();
    }
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $nameloc['username']);
    $bakinfos = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "productcheckdata"],
            ]
        ]
    ]);
    if ($DataUserOut['status'] == "Unsuccessful") {
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], null, 'html');
        rf_stop();
    }
    $config = "";
    if ($dataget[2] == "1520") {
        for ($i = 0; $i < count($DataUserOut['links']); ++$i) {
            $randomString = bin2hex(random_bytes(3));
            $urlimage = "$from_id$randomString.png";
            $qrCode = createqrcode($DataUserOut['links'][$i]);
            file_put_contents($urlimage, $qrCode->getString());
            if (!addBackgroundImage($urlimage, $qrCode, 'images.jpg')) {
                error_log("Unable to apply background image for QR code using path 'images.jpg'");
            }
            telegram('sendphoto', [
                'chat_id' => $from_id,
                'photo' => new CURLFile($urlimage),
                'caption' => "<code>{$DataUserOut['links'][$i]}</code>",
                'parse_mode' => "HTML",
            ]);
            unlink($urlimage);
        }
        rf_stop();
    }
    $randomString = bin2hex(random_bytes(3));
    $urlimage = "$from_id$randomString.png";
    $qrCode = createqrcode($DataUserOut['links'][$dataget[2]]);
    file_put_contents($urlimage, $qrCode->getString());
    if (!addBackgroundImage($urlimage, $qrCode, 'images.jpg')) {
        error_log("Unable to apply background image for QR code using path 'images.jpg'");
    }
    telegram('sendphoto', [
        'chat_id' => $from_id,
        'photo' => new CURLFile($urlimage),
        'caption' => "<code>{$DataUserOut['links'][$dataget[2]]}</code>",
        'parse_mode' => "HTML",
    ]);
    unlink($urlimage);
}
if (!$rf_chain1_handled && (preg_match('/changestatus_(\w+)/', $datain, $dataget))) {
    $rf_chain1_handled = true;
    $statuschangeservice = select("shopSetting", "*", "Namevalue", "statuschangeservice", "select")['value'];
    if ($statuschangeservice == "offstatus") {
        sendmessage($from_id, "❌ این قابلیت درحال حاضر در دسترس نیست", null, 'html');
        rf_stop();
    }
    $id_invoice = $dataget[1];
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    if ($nameloc['Status'] == "disablebyadmin") {
        sendmessage($from_id, "❌ این قابلیت درحال حاضر در دسترس نیست", null, 'html');
        rf_stop();
    }
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $nameloc['username']);
    if ($DataUserOut['status'] == "on_hold") {
        sendmessage($from_id, "❌ هنوز به کانفیگ متصل نشده اید و امکان تغییر وضعیت سرویس وجود ندارد. بعد از متصل شدن به کانفیگ می توانید از این قابلیت استفاده نمایید.", null, 'html');
        rf_stop();
    }
    if ($DataUserOut['status'] == "Unsuccessful") {
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], null, 'html');
        rf_stop();
    }
    if ($DataUserOut['status'] == "active") {
        $confirmdisableaccount = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '✅ تایید و غیرفعال کردن کانفیگ', 'callback_data' => "confirmaccountdisable_" . $id_invoice],
                ],
                [
                    ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "product_" . $nameloc['id_invoice']],
                ]
            ]
        ]);
        Editmessagetext($from_id, $message_id, "📌 با تایید گزینه زیر کانفیگ شما خاموش و دیگر امکان اتصال به کانفیگ وجود ندارد.
⚠️ در صورتی که میخواهید مجدد کانفیگ فعال شود باید از بخش مدیریت سرویس دکمه <u>💡 روشن کردن اکانت</u> را کلیک کنید", $confirmdisableaccount);
    } else {
        $confirmdisableaccount = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => '✅ تایید و فعال کردن کانفیگ', 'callback_data' => "confirmaccountdisable_" . $id_invoice],
                ],
                [
                    ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "product_" . $nameloc['id_invoice']],
                ]
            ]
        ]);
        Editmessagetext($from_id, $message_id, "📌 با تایید گزینه زیر کانفیگ شما روشن خواهد شد. و می توانید به کانفیگ خود متصل شوید
⚠️ در صورتی که میخواهید مجدد کانفیگ غیرفعال شود باید از بخش مدیریت سرویس دکمه <u>❌ خاموش کردن اکانت</u>را کلیک کنید", $confirmdisableaccount);
    }
}
if (!$rf_chain1_handled && (preg_match('/confirmaccountdisable_(\w+)/', $datain, $dataget))) {
    $rf_chain1_handled = true;
    $id_invoice = $dataget[1];
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $bakinfos = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "product_" . $nameloc['id_invoice']],
            ]
        ]
    ]);
    $dataoutput = $ManagePanel->Change_status($nameloc['username'], $nameloc['Service_location']);
    if ($dataoutput['status'] == "Unsuccessful") {
        Editmessagetext($from_id, $message_id, $textbotlang['users']['stateus']['notchanged'], $bakinfos);
        rf_stop();
    }
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $nameloc['username']);
    if ($DataUserOut['status'] == "active") {
        Editmessagetext($from_id, $message_id, $textbotlang['users']['stateus']['activedconfig'], $bakinfos);
    } else {
        Editmessagetext($from_id, $message_id, $textbotlang['users']['stateus']['disabledconfig'], $bakinfos);
    }
}
if (!$rf_chain1_handled && (preg_match('/extend_(\w+)/', $datain, $dataget))) {
    $rf_chain1_handled = true;
    $id_invoice = $dataget[1];
    $nameloc = select("invoice", "*", "id_invoice", $id_invoice, "select");
    if ($nameloc == false) {
        sendmessage($from_id, "❌ تمدید با خطا مواجه گردید مراحل تمدید را مجددا انجام دهید.", null, 'HTML');
        rf_stop();
    }
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    if ($marzban_list_get['status_extend'] == "off_extend") {
        sendmessage($from_id, "❌ امکان تمدید در این پنل وجود ندارد", null, 'html');
        rf_stop();
    }
    $DataUserOut = $ManagePanel->DataUser($nameloc['Service_location'], $nameloc['username']);
    if ($DataUserOut['status'] == "Unsuccessful") {
        sendmessage($from_id, $textbotlang['users']['stateus']['error'], null, 'html');
        rf_stop();
    }
    if ($DataUserOut['status'] == "on_hold") {
        sendmessage($from_id, "❌ هنوز به سرویس متصل نشده اید برای تمدید سرویس ابتدا به سرویس متصل شوید سپس اقدام به تمدید کنید", null, 'html');
        rf_stop();
    }
    $eextraprice = json_decode($marzban_list_get['pricecustomvolume'], true);
    $custompricevalue = $eextraprice[$user['agent']];
    $mainvolume = json_decode($marzban_list_get['mainvolume'], true);
    $mainvolume = $mainvolume[$user['agent']];
    $maxvolume = json_decode($marzban_list_get['maxvolume'], true);
    $maxvolume = $maxvolume[$user['agent']];
$stmt = $pdo->prepare("SELECT * FROM product WHERE (Location = :service_location OR Location = '/all') AND agent = :agent AND one_buy_status = '0'");
$stmt->execute([
    ':service_location' => $marzban_list_get['name_panel'],
    ':agent' => $user['agent'],
]);

    $product = $stmt->rowCount();
    savedata("clear", "id_invoice", $nameloc['id_invoice']);
    if ($product == 0) {
        $textcustom = "📌 حجم درخواستی خود را ارسال کنید.
🔔قیمت هر گیگ حجم $custompricevalue تومان می باشد.
🔔 حداقل حجم $mainvolume گیگابایت و حداکثر $maxvolume گیگابایت می باشد.";
        sendmessage($from_id, $textcustom, $backuser, 'html');
        deletemessage($from_id, $message_id);
        step('gettimecustomvolomforextend', $from_id);
        rf_stop();
    }
    if ($nameloc['name_product'] == "🛍 حجم دلخواه" || $nameloc['name_product'] == "⚙️ سرویس دلخواه") {
        $textcustom = "📌 حجم درخواستی خود را ارسال کنید.
🔔قیمت هر گیگ حجم $custompricevalue تومان می باشد.
🔔 حداقل حجم $mainvolume گیگابایت و حداکثر $maxvolume گیگابایت می باشد.";
        sendmessage($from_id, $textcustom, $backuser, 'html');
        deletemessage($from_id, $message_id);
        step('gettimecustomvolomforextend', $from_id);
        rf_stop();
    }
    if ($setting['statuscategory'] == "offcategory") {
        if ($setting['statuscategorygenral'] == "oncategorys") {
            $categoryRows = buildExtendCategoryKeyboard($pdo, $nameloc['Service_location'], $user['agent'], $nameloc['id_invoice']);
            if (!empty($categoryRows)) {
                $categoryRows[] = [
                    ['text' => "♻️ تمدید پلن فعلی", 'callback_data' => "exntedagei"]
                ];
                $categoryRows[] = [
                    ['text' => "🏠 بازگشت به اطلاعات سرویس", 'callback_data' => "product_" . $nameloc['id_invoice']]
                ];
                Editmessagetext($from_id, $message_id, "📌 دسته بندی خود را انتخاب نمایید!", json_encode([
                    'inline_keyboard' => $categoryRows
                ]));
                rf_stop();
            }
        }
$stmt = $pdo->prepare("SELECT * FROM product WHERE (Location = :servicelocation OR Location = 'all') AND (agent = :agent OR agent = 'all')");
$stmt->execute([
    ':servicelocation' => $nameloc['Service_location'],
    ':agent' => $user['agent']
]);
        $productextend = ['inline_keyboard' => []];
        $statusshowprice = select("shopSetting", "*", "Namevalue", "statusshowprice", "select")['value'];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hide_panel = json_decode($result['hide_panel'], true);
            if (is_array($hide_panel) && in_array($nameloc['Service_location'], $hide_panel)) {
                error_log("Product {$result['code_product']} is marked hidden for {$nameloc['Service_location']} but was kept visible for extend.");
            }
            if (intval($user['pricediscount']) != 0) {
                $resultper = ($result['price_product'] * $user['pricediscount']) / 100;
                $result['price_product'] = $result['price_product'] - $resultper;
            }
            if ($statusshowprice == "offshowprice") {
                $namekeyboard = $result['name_product'];
            } else {
                $result['price_product'] = number_format($result['price_product']);
                $namekeyboard = $result['name_product'] . " - " . $result['price_product'] . "تومان";
            }
            $productextend['inline_keyboard'][] = [
                ['text' => $namekeyboard, 'callback_data' => "serviceextendselect_" . $result['code_product']]
            ];
        }
        $productextend['inline_keyboard'][] = [
            ['text' => "♻️ تمدید پلن فعلی", 'callback_data' => "exntedagei"]
        ];
        $productextend['inline_keyboard'][] = [
            ['text' => "🏠 بازگشت به اطلاعات سرویس", 'callback_data' => "product_" . $nameloc['id_invoice']]
        ];

        $json_list_product_lists = json_encode($productextend);
        Editmessagetext($from_id, $message_id, $textbotlang['users']['extend']['selectservice'], $json_list_product_lists);
    } else {
        $monthkeyboard = keyboardTimeCategory($nameloc['Service_location'], $user['agent'], "productextendmonths_", "product_$id_invoice", false, true);
        Editmessagetext($from_id, $message_id, $textbotlang['Admin']['month']['title'], $monthkeyboard);
    }
}
if (!$rf_chain1_handled && ($user['step'] == "gettimecustomvolomforextend")) {
    $rf_chain1_handled = true;
    $userdate = json_decode($user['Processing_value'], true);
    $nameloc = select("invoice", "*", "id_invoice", $userdate['id_invoice'], "select");
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $mainvolume = json_decode($marzban_list_get['mainvolume'], true);
    $mainvolume = $mainvolume[$user['agent']];
    $maxvolume = json_decode($marzban_list_get['maxvolume'], true);
    $maxvolume = $maxvolume[$user['agent']];
    $maintime = json_decode($marzban_list_get['maintime'], true);
    $maintime = $maintime[$user['agent']];
    $maxtime = json_decode($marzban_list_get['maxtime'], true);
    $maxtime = $maxtime[$user['agent']];
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['Product']['Invalidvolume'], $backuser, 'HTML');
        rf_stop();
    }
    if ($text > intval($maxvolume) || $text < intval($mainvolume)) {
        $texttime = "❌ حجم نامعتبر است.\n🔔 حداقل حجم $mainvolume گیگابایت و حداکثر $maxvolume گیگابایت می باشد";
        sendmessage($from_id, $texttime, $backuser, 'HTML');
        rf_stop();
    }
    $eextraprice = json_decode($marzban_list_get['pricecustomtime'], true);
    $customtimevalueprice = $eextraprice[$user['agent']];
    savedata("save", "volume", $text);
    $textcustom = "⌛️ زمان سرویس خود را انتخاب نمایید 
📌 تعرفه هر روز  : $customtimevalueprice  تومان
⚠️ حداقل زمان $maintime روز  و حداکثر $maxtime روز  می توانید تهیه کنید";
    sendmessage($from_id, $textcustom, $backuser, 'html');
    step('getvolumecustomuserforextend', $from_id);
}
if (!$rf_chain1_handled && (preg_match('/productextendmonths_(\w+)/', $datain, $dataget))) {
    $rf_chain1_handled = true;
    $monthenumber = $dataget[1];
    $userdate = json_decode($user['Processing_value'], true);
    $nameloc = select("invoice", "*", "id_invoice", $userdate['id_invoice'], "select");
$categoryRows = [];
    if ($setting['statuscategorygenral'] == "oncategorys") {
        $categoryRows = buildExtendCategoryKeyboard($pdo, $nameloc['Service_location'], $user['agent'], $nameloc['id_invoice'], $monthenumber);
        if (!empty($categoryRows)) {
            $categoryRows[] = [
                ['text' => "♻️ تمدید پلن فعلی", 'callback_data' => "exntedagei"]
            ];
            $categoryRows[] = [
                ['text' => "🏠 بازگشت به اطلاعات سرویس", 'callback_data' => "product_" . $nameloc['id_invoice']]
            ];
            Editmessagetext($from_id, $message_id, "📌 دسته بندی خود را انتخاب نمایید!", json_encode([
                'inline_keyboard' => $categoryRows
            ]));
            rf_stop();
        }
    }
$stmt = $pdo->prepare("SELECT * FROM product WHERE (Location = :service_location OR Location = '/all') AND agent = :agent AND Service_time = :monthe AND one_buy_status = '0'");
$stmt->execute([
    ':service_location' => $nameloc['Service_location'],
    ':agent' => $user['agent'],
    'monthe' => $monthenumber
]);

    $productextend = ['inline_keyboard' => []];
    $marzban_list_get = select("marzban_panel", "*", "name_panel", $nameloc['Service_location'], "select");
    $statusshowprice = select("shopSetting", "*", "Namevalue", "statusshowprice", "select")['value'];
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (intval($user['pricediscount']) != 0) {
            $resultper = ($result['price_product'] * $user['pricediscount']) / 100;
            $result['price_product'] = $result['price_product'] - $resultper;
        }
        if ($statusshowprice == "offshowprice") {
            $namekeyboard = $result['name_product'];
        } else {
            $result['price_product'] = number_format($result['price_product']);
            $namekeyboard = $result['name_product'] . " - " . $result['price_product'] . "تومان";
        }
        $productextend['inline_keyboard'][] = [
            ['text' => $namekeyboard, 'callback_data' => "serviceextendselect_" . $result['code_product']]
        ];
    }
    if ($nameloc['name_product'] == "🛍 حجم دلخواه" || $nameloc['name_product'] == "⚙️ سرویس دلخواه") {
        $productextend['inline_keyboard'][] = [
            ['text' => "📍 انتخاب سرویس فعلی", 'callback_data' => "serviceextendselect_pre"]
        ];
    }
    $productextend['inline_keyboard'][] = [
        ['text' => "🏠 بازگشت به اطلاعات سرویس", 'callback_data' => "product_" . $nameloc['id_invoice']]
    ];

    $json_list_product_lists = json_encode($productextend);
    Editmessagetext($from_id, $message_id, $textbotlang['users']['extend']['selectservice'], $json_list_product_lists);
}
