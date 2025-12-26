<?php
rf_set_module('admin/routes/06_sendmessageuser__step_sendmessagetext__step_sendmessagetid.php');

if (!isset($rf_admin_handled)) $rf_admin_handled = false;
if ($rf_admin_handled) return;
if (!$rf_admin_handled && (preg_match('/sendmessageuser_(\w+)/', $datain, $dataget))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    savedata("clear", "iduser", $iduser);
    sendmessage($from_id, "📌 متن یا تصویر خود را ارسال نمایید", $backadmin, 'HTML');
    step('sendmessagetext', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "sendmessagetext")) {
    $rf_admin_handled = true;

    if ($photo) {
        savedata("save", "type", "photo");
        savedata("save", "photoid", $photoid);
        savedata("save", "text", $caption);
    } else {
        savedata("save", "text", $text);
        savedata("save", "type", "text");
    }
    $textb = "📌 کاربر بتواند پاسخ دهد یاخیر ؟
1 - بله  پاسخ دهد 
2 - خیر پاسخ ندهد
پاسخ را به عدد ارسال کنید";
    sendmessage($from_id, $textb, $backadmin, 'HTML');
    step('sendmessagetid', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "sendmessagetid")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    $textsendadmin = "
👤 یک پیام از طرف ادمین ارسال شده است  
متن پیام:

{$userdata['text']}";
    if (intval($text) == "1") {
        $Response = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $textbotlang['users']['support']['answermessage'], 'callback_data' => 'Responseuser'],
                ],
            ]
        ]);
        if ($userdata['type'] == "photo") {
            telegram('sendphoto', [
                'chat_id' => $userdata['iduser'],
                'photo' => $userdata['photoid'],
                'caption' => $textsendadmin,
                'reply_markup' => $Response,
                'parse_mode' => "HTML",
            ]);
        } else {
            sendmessage($userdata['iduser'], $textsendadmin, $Response, 'HTML');
        }
    } else {
        if ($userdata['type'] == "photo") {
            telegram('sendphoto', [
                'chat_id' => $userdata['iduser'],
                'photo' => $userdata['photoid'],
                'caption' => $textsendadmin,
                'parse_mode' => "HTML",
            ]);
        } else {
            sendmessage($userdata['iduser'], $textsendadmin, null, 'HTML');
        }
    }
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['MessageSent'], $keyboardadmin, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📤 فوروارد پیام برای یک کاربر")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetText'], $backadmin, 'HTML');
    step('getmessageforward', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmessageforward")) {
    $rf_admin_handled = true;

    savedata("clear", "messageid", $message_id);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetIDMessage'], $backadmin, 'HTML');
    step('getbtnresponseforward', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getbtnresponseforward")) {
    $rf_admin_handled = true;

    $userdata = json_decode($user['Processing_value'], true);
    if (!ctype_digit($text)) {
        sendmessage($from_id, $textbotlang['Admin']['agent']['invalidvlue'], $backadmin, 'HTML');
        return;
    }
    forwardMessage($from_id, $userdata['messageid'], $text);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['MessageSent'], $keyboardadmin, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "📚 بخش آموزش" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['users']['selectoption'], $keyboardhelpadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($text == "📚 اضافه کردن آموزش" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Help']['GetAddNameHelp'], $backadmin, 'HTML');
    step('add_name_help', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "add_name_help")) {
    $rf_admin_handled = true;

    if (strlen($text) >= 150) {
        sendmessage($from_id, "❌ نام آموزش باید کمتر از 150 کاراکتر باشد", null, 'HTML');
        return;
    }
    $helpexits = select("help", "*", "name_os", $text, "count");
    if ($helpexits != 0) {
        sendmessage($from_id, "❌ نام آموزش وجود دارد از نام دیگری استفاده نمایید.", null, 'HTML');
        return;
    }
    $stmt = $connect->prepare("INSERT IGNORE INTO help (name_os) VALUES (?)");
    $stmt->bind_param("s", $text);
    $stmt->execute();
    update("user", "Processing_value", $text, "id", $from_id);
    if ($setting['categoryhelp'] == "0") {
        update("help", "category", "0", "name_os", $user['Processing_value']);
        sendmessage($from_id, $textbotlang['Admin']['Help']['GetAddDecHelp'], $backadmin, 'HTML');
        step('add_dec', $from_id);
        return;
    }
    sendmessage($from_id, "📌 نام دسته بندی برای آموزش را ارسال نمایید", $backadmin, 'HTML');
    step('getcatgoryhelp', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getcatgoryhelp")) {
    $rf_admin_handled = true;

    update("help", "category", $text, "name_os", $user['Processing_value']);
    sendmessage($from_id, $textbotlang['Admin']['Help']['GetAddDecHelp'], $backadmin, 'HTML');
    step('add_dec', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "add_dec")) {
    $rf_admin_handled = true;

    if ($photo) {
        if (isset($photoid))
            update("help", "Media_os", $photoid, "name_os", $user['Processing_value']);
        if (isset($caption))
            update("help", "Description_os", $caption, "name_os", $user['Processing_value']);
        update("help", "type_Media_os", "photo", "name_os", $user['Processing_value']);
    } elseif ($text) {
        update("help", "Description_os", $text, "name_os", $user['Processing_value']);
    } elseif ($video) {
        if (isset($videoid))
            update("help", "Media_os", $videoid, "name_os", $user['Processing_value']);
        if (isset($caption))
            update("help", "Description_os", $caption, "name_os", $user['Processing_value']);
        update("help", "type_Media_os", "video", "name_os", $user['Processing_value']);
    } elseif ($document) {
        if (isset($fileid))
            update("help", "Media_os", $fileid, "name_os", $user['Processing_value']);
        if (isset($caption))
            update("help", "Description_os", $caption, "name_os", $user['Processing_value']);
        update("help", "type_Media_os", "document", "name_os", $user['Processing_value']);
    }
    sendmessage($from_id, $textbotlang['Admin']['Help']['SaveHelp'], $keyboardadmin, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && ($text == "❌ حذف آموزش" && $adminrulecheck['rule'] == "administrator")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['Help']['SelectName'], $json_list_helpkey, 'HTML');
    step('remove_help', $from_id);
    return;
}

if (!$rf_admin_handled && ($user['step'] == "remove_help")) {
    $rf_admin_handled = true;

    $stmt = $pdo->prepare("DELETE FROM help WHERE name_os = :name_os");
    $stmt->bindParam(':name_os', $text, PDO::PARAM_STR);
    $stmt->execute();
    sendmessage($from_id, $textbotlang['Admin']['Help']['RemoveHelp'], $keyboardhelpadmin, 'HTML');
    step('home', $from_id);
    return;
}

if (!$rf_admin_handled && (preg_match('/Response_(\w+)/', $datain, $dataget) && ($adminrulecheck['rule'] == "administrator" || $adminrulecheck['rule'] == "support"))) {
    $rf_admin_handled = true;

    $iduser = $dataget[1];
    update("user", "Processing_value", $iduser, "id", $from_id);
    step('getmessageAsAdmin', $from_id);
    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['GetTextResponse'], $backadmin, 'HTML');
    return;
}

if (!$rf_admin_handled && ($user['step'] == "getmessageAsAdmin")) {
    $rf_admin_handled = true;

    sendmessage($from_id, $textbotlang['Admin']['ManageUser']['SendMessageuser'], null, 'HTML');
    $Respuseronse = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['users']['support']['answermessage'], 'callback_data' => 'Responseuser'],
            ],
        ]
    ]);
    if ($text) {
        $textSendAdminToUser = "
📩 یک پیام از سمت مدیریت برای شما ارسال گردید.
                    
متن پیام : 
$text";
        sendmessage($user['Processing_value'], $textSendAdminToUser, $Respuseronse, 'HTML');
    }
    if ($photo) {
        $textSendAdminToUser = "
📩 یک پیام از سمت مدیریت برای شما ارسال گردید.
                    
متن پیام : 
$caption";
        telegram('sendphoto', [
            'chat_id' => $user['Processing_value'],
            'photo' => $photoid,
            'reply_markup' => $Respuseronse,
            'caption' => $textSendAdminToUser,
            'parse_mode' => "HTML",
        ]);
    }
    step('home', $from_id);
    return;
}

