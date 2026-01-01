<?php
rf_set_module('parts/init_guard_settings_user.php');
if (isset($update['chat_member'])) {
    $status = $update['chat_member']['new_chat_member']['status'];
    $from_id = $update['chat_member']['new_chat_member']['user']['id'];
    $user = select("user", "id", $from_id);
    $keyboard_channel_left = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "📌 عضویت مجدد", 'url' => "https://t.me/{$update['chat_member']['chat']['username']}"],
            ],
        ]
    ]);
    if (in_array($status, ['left', 'kicked', 'restricted'])) {
        sendmessage($from_id, $textbotlang['users']['channel']['left_channel'], $keyboard_channel_left, 'html');
        rf_stop();
    }
}
if (!in_array($Chat_type, ["private", "supergroup"]))
    rf_stop();
if (isset($chat_member))
    rf_stop();
$first_name = sanitizeUserName($first_name);
$setting = select("setting", "*");
if (!is_array($setting)) {
    error_log('Settings data is unavailable. Ensure the `setting` table exists and contains records.');
    rf_stop();
}
$ManagePanel = new ManagePanel();
$keyboard_check = json_decode($setting['keyboardmain'], true);
if (is_array($keyboard_check) && preg_match('/[\x{600}-\x{6FF}\x{FB50}-\x{FDFF}]/u', $keyboard_check['keyboard'][0][0]['text'])) {
    $keyboardmain = '{"keyboard":[[{"text":"text_sell"},{"text":"text_extend"}],[{"text":"text_usertest"},{"text":"text_wheel_luck"}],[{"text":"text_Purchased_services"},{"text":"accountwallet"}],[{"text":"text_affiliates"},{"text":"text_Tariff_list"}],[{"text":"text_support"},{"text":"text_help"}]]}';
    update("setting", "keyboardmain", $keyboardmain, null, null);
}

#-----------telegram_ip_ranges------------#
if (!checktelegramip())
    die("Unauthorized access");
#-----------end telegram_ip_ranges------------#
if (intval($from_id) == 0)
    rf_stop();
#-------------Variable----------#
$user = select("user", "*", "id", $from_id, "select", ['cache' => false]);
$isNewUser = !is_array($user);
$otherreport = select("topicid", "idreport", "report", "otherreport", "select")['idreport'];
$tronadoOldDomain = 'tronseller.storeddownloader.fun';
$tronadoRecommendedUrl = (defined('TRONADO_ORDER_TOKEN_ENDPOINTS') && isset(TRONADO_ORDER_TOKEN_ENDPOINTS[0]))
    ? TRONADO_ORDER_TOKEN_ENDPOINTS[0]
    : 'https://bot.tronado.cloud/api/v1/Order/GetOrderToken';
$tronadoWarningFlag = RF_APP_ROOT . '/urlpaymenttron_warning.flag';
if (!file_exists($tronadoWarningFlag)) {
    $storedUrl = getPaySettingValue('urlpaymenttron');
    if (is_string($storedUrl) && stripos($storedUrl, $tronadoOldDomain) !== false) {
        $warningText = "⚠️ دامنه قدیمی ترنادو هنوز در تنظیمات استفاده می‌شود. لطفاً آدرس جدید را جایگزین کنید:\n{$tronadoRecommendedUrl}";
        if (!empty($setting['Channel_Report'])) {
            $payload = [
                'chat_id' => $setting['Channel_Report'],
                'text' => $warningText,
                'parse_mode' => 'HTML'
            ];
            if (!empty($otherreport)) {
                $payload['message_thread_id'] = $otherreport;
            }
            telegram('sendmessage', $payload);
        } else {
            error_log($warningText);
        }
        file_put_contents($tronadoWarningFlag, (string) time());
    }
}
if ($isNewUser && $setting['statusnewuser'] == "onnewuser") {
    $Response = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $textbotlang['Admin']['ManageUser']['mangebtnuser'], 'callback_data' => 'manageuser_' . $from_id],
            ],
        ]
    ]);
    $newuser = sprintf($textbotlang['Admin']['ManageUser']['newuser'], $first_name, $username, "<a href = \"tg://user?id=$from_id\">$from_id</a>");
    if (strlen($setting['Channel_Report'] ?? '') > 0) {
        telegram('sendmessage', [
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherreport,
            'text' => $newuser,
            'reply_markup' => $Response,
            'parse_mode' => "HTML"
        ]);
    }
}
$date = time();
if ($from_id != 0 && $isNewUser) {
    if ($setting['verifystart'] != "onverify") {
        $valueverify = 1;
    } else {
        $valueverify = 0;
    }
    $randomString = bin2hex(random_bytes(6));
    $initialProcessingValue = '0';
    $initialProcessingValueOne = 'none';
    $initialProcessingValueTow = 'none';
    $initialProcessingValueFour = '0';
    $initialRollStatus = '0';
    $stmt = $pdo->prepare("INSERT IGNORE INTO user (id , step,limit_usertest,User_Status,number,Balance,pagenumber,username,agent,message_count,last_message_time,affiliates,affiliatescount,cardpayment,number_username,namecustom,register,verify,codeInvitation,pricediscount,maxbuyagent,joinchannel,score,status_cron,roll_Status,Processing_value,Processing_value_one,Processing_value_tow,Processing_value_four) VALUES (:from_id, 'none',:limit_usertest_all,'Active','none','0','1',:username,'f','0','0','0','0',:showcard,'100','none',:date,:verifycode,:codeInvitation,'0','0','0','0','1',:roll_status,:processing_value,:processing_value_one,:processing_value_tow,:processing_value_four)");
    $stmt->bindParam(':from_id', $from_id);
    $stmt->bindParam(':limit_usertest_all', $setting['limit_usertest_all']);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':showcard', $setting['showcard']);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':verifycode', $valueverify);
    $stmt->bindParam(':codeInvitation', $randomString);
    $stmt->bindParam(':roll_status', $initialRollStatus);
    $stmt->bindParam(':processing_value', $initialProcessingValue);
    $stmt->bindParam(':processing_value_one', $initialProcessingValueOne);
    $stmt->bindParam(':processing_value_tow', $initialProcessingValueTow);
    $stmt->bindParam(':processing_value_four', $initialProcessingValueFour);
    $stmt->execute();
    clearSelectCache('user');
    $user = select("user", "*", "id", $from_id, "select", ['cache' => false]);
    $isNewUser = !is_array($user);
}
if (!is_array($user)) {
    $user = array();
    $user = array(
        'step' => '',
        'Processing_value' => '',
        'User_Status' => '',
        'agent' => '',
        'username' => '',
        'limit_usertest' => '',
        'message_count' => '',
        'affiliates' => '',
        'last_message_time' => '',
        'cardpayment' => '',
        'roll_Status' => '',
        'number_username' => '',
        'number' => '',
        'register' => '',
        'codeInvitation' => '',
        'pricediscount' => '',
        'joinchannel' => '',
        'score' => "",
        'limitchangeloc' => ''
    );
} else {
    $user['codeInvitation'] = ensureUserInvitationCode($from_id, $user['codeInvitation'] ?? null);
}
$admin_ids = select("admin", "id_admin", null, null, "FETCH_COLUMN", ['cache' => true]);
if (!is_array($admin_ids)) {
    $admin_ids = [];
}

// Optimization: Removed heavy selects for all invoice IDs, usernames, etc.
// These are only needed in specific admin routes, not on every bot start.

$marzban_list = select("marzban_panel", "name_panel", null, null, "FETCH_COLUMN", ['cache' => true]);
$name_product = select("product", "name_product", null, null, "FETCH_COLUMN", ['cache' => true]);
$channels_id = select("channels", "link", null, null, "FETCH_COLUMN", ['cache' => true]);

$topic_id = select("topicid", "*", null, null, "fetchAll", ['cache' => true]);
$statusnote = false;
foreach ($topic_id as $topic) {
    if ($topic['report'] == "reportnight") $reportnight = $topic['idreport'];
    if ($topic['report'] == 'reporttest') $reporttest = $topic['idreport'];
    if ($topic['report'] == 'errorreport') $errorreport = $topic['idreport'];
    if ($topic['report'] == 'porsantreport') $porsantreport = $topic['idreport'];
    if ($topic['report'] == 'reportcron') $reportcron = $topic['idreport'];
    if ($topic['report'] == 'backupfile') $reportbackup = $topic['idreport'];
    if ($topic['report'] == 'buyreport') $buyreport = $topic['idreport'];
    if ($topic['report'] == 'otherservice') $otherservice = $topic['idreport'];
    if ($topic['report'] == 'paymentreport') $paymentreports = $topic['idreport'];
}
if ($setting['statusnamecustom'] == 'onnamecustom') $statusnote = true;
if ($setting['statusnoteforf'] == "0" && $user['agent'] == "f") $statusnote = false;

if (!empty($setting['Channel_Report'])) {
    createForumTopicIfMissing($porsantreport, 'porsantreport', $textbotlang['Admin']['affiliates']['titletopic'], $setting['Channel_Report']);
    createForumTopicIfMissing($reportnight, 'reportnight', $textbotlang['Admin']['report']['reportnight'], $setting['Channel_Report']);
    createForumTopicIfMissing($reportcron, 'reportcron', $textbotlang['Admin']['report']['reportcron'], $setting['Channel_Report']);
    createForumTopicIfMissing($reportbackup, 'backupfile', "🤖 بکاپ ربات نماینده", $setting['Channel_Report']);
}

// Optimization: Datatextbot keys are initialized, values will be loaded in precheck.php if needed or mapped efficiently.
$datatextbot = array(
    'text_usertest' => '', 'text_Purchased_services' => '', 'text_support' => '', 'text_help' => '',
    'text_start' => '', 'text_bot_off' => '', 'text_dec_info' => '', 'text_roll' => '',
    'text_fq' => '', 'text_dec_fq' => '', 'text_sell' => '', 'text_Add_Balance' => '',
    'text_channel' => '', 'text_Tariff_list' => '', 'text_dec_Tariff_list' => '', 'text_affiliates' => '',
    'text_pishinvoice' => '', 'accountwallet' => '', 'textafterpay' => '', 'textaftertext' => '',
    'textmanual' => '', 'textselectlocation' => '', 'crontest' => '', 'textrequestagent' => '',
    'textpanelagent' => '', 'text_wheel_luck' => '', 'text_cart' => '', 'text_cart_auto' => '',
    'textafterpayibsng' => '', 'text_request_agent_dec' => '', 'carttocart' => '', 'textnowpayment' => '',
    'textnowpaymenttron' => '', 'iranpay1' => '', 'iranpay2' => '', 'iranpay3' => '',
    'aqayepardakht' => '', 'zarinpey' => '', 'zarinpal' => '', 'textpaymentnotverify' => "",
    'text_star_telegram' => '', 'text_extend' => '', 'text_wgdashboard' => '', 'text_Discount' => '',
);
