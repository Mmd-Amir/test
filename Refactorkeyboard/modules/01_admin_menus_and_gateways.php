<?php
if (function_exists('rf_set_module')) { rf_set_module('Refactorkeyboard/modules/01_admin_menus_and_gateways.php'); }
$keyboardPanel = json_encode([
    'inline_keyboard' => [
        [['text' => $datatextbot['text_Discount'] ,'callback_data' => "Discount"],
        ['text' => $datatextbot['text_Add_Balance'] ,'callback_data' => "Add_Balance"]
        ],
        [['text' => $textbotlang['users']['backbtn'] ,'callback_data' => "backuser"]],
    ],
    'resize_keyboard' => true
]);
if($adminrulecheck['rule'] == "administrator"){
$keyboardadmin = json_encode([
    'keyboard' => [
        [['text' => $textbotlang['Admin']['Status']['btn']]],
        [['text' => $textbotlang['Admin']['btnkeyboardadmin']['managementpanel']],['text' => $textbotlang['Admin']['btnkeyboardadmin']['addpanel']]],
        [['text' => "⏳ تنظیم سریع قیمت زمان"],['text' => "🔋 تنظیم سریع قیمت حجم"]],
        [['text' => $textbotlang['Admin']['btnkeyboardadmin']['managruser']],['text' => "🏬 تنظیمات فروشگاه"]],
        [['text' => "💎 مالی"]],
        [['text' => "🤙 بخش پشتیبانی"],['text' => "📚 بخش آموزش"]],
        [['text' => "📑 نوع مرزبان"],['text' => "🛠 قابلیت های پنل"]],
        [['text' => "⚙️ تنظیمات عمومی"],['text' => "💵 رسید های تایید نشده"]],
        [['text' => $textbotlang['users']['backbtn']]]
    ],
    'resize_keyboard' => true
]);
}
if($adminrulecheck['rule'] == "Seller"){
$keyboardadmin = json_encode([
    'keyboard' => [
        [['text' => $textbotlang['Admin']['Status']['btn']]],
        [['text' => "👤 مدیریت کاربر"]],
        [['text' => $textbotlang['users']['backbtn']]]
    ],
    'resize_keyboard' => true
]);
}
if($adminrulecheck['rule'] == "support"){
$keyboardadmin = json_encode([
    'keyboard' => [
        [['text' => "👤 مدیریت کاربر"],['text' =>"👁‍🗨 جستجو کاربر"]],
        [['text' => $textbotlang['users']['backbtn']]]
    ],
    'resize_keyboard' => true
]);
}
$CartManage = json_encode([
    'keyboard' => [
        [['text' => "🗂 نام درگاه کارت به کارت"]],
        [['text' => "💳 تنظیم شماره کارت"],['text' => "❌ حذف شماره کارت"]],
        [['text' => "👤 آیدی پشتیبانی", ],['text' => "💳 درگاه آفلاین در پیوی"]],
        [['text' => "💰  غیرفعالسازی  نمایش شماره کارت"],['text' => "💰 فعالسازی نمایش شماره کارت"]],
        [['text' => "♻️ نمایش گروهی شماره کارت"]],
        [['text' => "📄 خروجی افراد شماره کارت فعال"]],
        [['text' => "♻️ تایید خودکار رسید"],['text' => "💰 کش بک کارت به کارت"]],
        [['text' => "🔒 نمایش کارت به کارت پس از اولین پرداخت"]],
        [['text' => "⬇️ حداقل مبلغ کارت به کارت"],['text' => "⬆️ حداکثر مبلغ کارت به کارت"]],
        [['text' => "📚 تنظیم آموزش کارت به کارت"]],
        [['text' => "🤖 تایید رسید  بدون بررسی"]],
        [['text' => "💳 استثناء کردن کاربر از تایید خودکار"]],
        [['text' => "⏳ زمان تایید خودکار بدون بررسی"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$trnado = json_encode([
    'keyboard' => [
        [['text' => "🏷️ نام نمایشی درگاه ترنادو"]],
        [['text' => "🔑 ثبت API Key ترنادو"]],
        [['text' => "💼 ثبت آدرس ولت ترون (TRC20)"]],
        [['text' => "🌐 ثبت آدرس API ترنادو"]],
        [['text' => "💰 کش بک ارزی ریالی دوم"]],
        [['text' => "⬇️ حداقل مبلغ ارزی ریالی دوم"],['text' => "⬆️ حداکثر مبلغ ارزی ریالی دوم"]],
        [['text' => "📚 تنظیم آموزش ارزی ریالی  دوم"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$keyboardzarinpal = json_encode([
    'keyboard' => [
        [['text' => "🗂 نام درگاه زرین پال"],['text' => "مرچنت زرین پال"]],
        [['text' => "💰 کش بک زرین پال"]],
        [['text' => "⬇️ حداقل مبلغ زرین پال"],['text' => "⬆️ حداکثر مبلغ زرین پال"]],
        [['text' => "📚 تنظیم آموزش زرین پال"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$keyboardzarinpey = json_encode([
    'keyboard' => [
        [['text' => "🗂 نام درگاه زرین پی"], ['text' => "🔑 توکن زرین پی"]],
        [['text' => "💰 کش بک زرین پی"]],
        [['text' => "🧑🏼‍💻 اموزش اتصال"]],
        [['text' => "⬇️ حداقل مبلغ زرین پی"], ['text' => "⬆️ حداکثر مبلغ زرین پی"]],
        [['text' => "📚 تنظیم آموزش زرین پی"]],
        [['text' => $textbotlang['Admin']['backadmin']], ['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$aqayepardakht = json_encode([
    'keyboard' => [
        [['text' => "🗂 نام درگاه آقای پرداخت"]],
        [['text' => "تنظیم مرچنت آقای پرداخت"],['text' => "💰 کش بک آقای پرداخت"]],
        [['text' => "⬇️ حداقل مبلغ آقای پرداخت"],['text' => "⬆️ حداکثر مبلغ آقای پرداخت"]],
        [['text' => "📚 تنظیم آموزش درگاه اقای پرداخت"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$NowPaymentsManage = json_encode([
    'keyboard' => [
        [['text' => "🗂 نام درگاه   plisio"]],
        [['text' => "🧩 api plisio"],['text'=> "💰 کش بک plisio"]],
        [['text' => "⬇️ حداقل مبلغ plisio"],['text' =>"⬆️ حداکثر مبلغ plisio"]],
        [['text' => "📚 تنظیم آموزش plisio"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$mainAdminId = isset($adminnumber) ? trim((string) $adminnumber) : '';
$currentUserId = isset($from_id) ? trim((string) $from_id) : '';

$settingPanelRows = [
    [['text' => "⚙️ وضعیت قابلیت ها"]],
    [['text' => "📣 گزارشات ربات"], ['text' => "📯 تنظیمات کانال"]],
    [['text' => "✅ فعالسازی پنل تحت وب"]],
    [['text' => "🗑 بهینه سازی ربات "]],
];

if ($mainAdminId === '' || $currentUserId === $mainAdminId) {
    $settingPanelRows[] = [['text' => "💀 بازنشانی ربات "]];
}

$settingPanelRows = array_merge($settingPanelRows, [
    [['text' => "📝 تنظیم متن ربات"], ['text' => "👨‍🔧 بخش ادمین"]],
    [['text' => "➕ محدودیت ساخت اکانت تست برای همه"]],
    [['text' => "💰 مبلغ عضویت نمایندگی"], ['text' => "🖼 پس زمینه کیوآرکد"]],
    [['text' => "🔗 وبهوک مجدد ربات های نماینده"]],
    [['text' => $textbotlang['Admin']['backadmin']], ['text' => $textbotlang['Admin']['backmenu']]],
]);

$setting_panel = json_encode([
    'keyboard' => $settingPanelRows,
    'resize_keyboard' => true
]);
$PaySettingcard = getPaySettingValue("Cartstatus");
$PaySettingnow = getPaySettingValue("nowpaymentstatus");
$PaySettingaqayepardakht = getPaySettingValue("statusaqayepardakht");
$PaySettingpv = getPaySettingValue("Cartstatuspv");
$usernamecart = getPaySettingValue("CartDirect");
$Swapino = getPaySettingValue("statusSwapWallet");
$trnadoo = getPaySettingValue("statustarnado");
$paymentverify = getPaySettingValue("checkpaycartfirst");
$stmt = $pdo->prepare("SELECT * FROM Payment_report WHERE id_user = '$from_id' AND payment_Status = 'paid' ");
$stmt->execute();
$paymentexits = $stmt->rowCount();
$zarinpal = getPaySettingValue("zarinpalstatus");
$zarinpey = getPaySettingValue("zarinpeystatus");
$affilnecurrency = getPaySettingValue("digistatus");
$arzireyali3 = getPaySettingValue("statusiranpay3");
$paymentstatussnotverify = getPaySettingValue("paymentstatussnotverify");
$paymentsstartelegram = getPaySettingValue("statusstar");
$payment_status_nowpayment = getPaySettingValue("statusnowpayment");
$step_payment = [
    'inline_keyboard' => []
    ];
   if($PaySettingcard == "oncard" && intval($users['cardpayment']) == 1){
        if($PaySettingpv == "oncardpv"){
        $step_payment['inline_keyboard'][] = [
            ['text' => $datatextbot['carttocart'] ,'url' => "https://t.me/$usernamecart"],
    ];
        }else{
                    $step_payment['inline_keyboard'][] = [
            ['text' => $datatextbot['carttocart'] ,'callback_data' => "cart_to_offline"],
    ];
        }
    }
    if(($paymentexits == 0 && $paymentverify == "onpayverify"))unset($step_payment['inline_keyboard']);
   if($PaySettingnow == "onnowpayment"){
        $step_payment['inline_keyboard'][] = [
    ['text' => $datatextbot['textnowpayment'], 'callback_data' => "plisio" ]
    ];
    }
    if($payment_status_nowpayment == "1"){
        $step_payment['inline_keyboard'][] = [
    ['text' => $datatextbot['textsnowpayment'], 'callback_data' => "nowpayment" ]
    ];
    }
   if($affilnecurrency == "ondigi"){
        $step_payment['inline_keyboard'][] = [
            ['text' =>  $datatextbot['textnowpaymenttron'], 'callback_data' => "digitaltron" ]
    ];
    }
   if($Swapino == "onSwapinoBot"){
        $step_payment['inline_keyboard'][] = [
            ['text' => $datatextbot['iranpay2'] , 'callback_data' => "iranpay1" ]
    ];
    }
   if($trnadoo == "onternado"){
        $step_payment['inline_keyboard'][] = [
            ['text' => $datatextbot['iranpay3'] , 'callback_data' => "iranpay2" ]
    ];
    }
     if($arzireyali3 == "oniranpay3"  && $paymentexits >= 2){
        $step_payment['inline_keyboard'][] = [
            ['text' => $datatextbot['iranpay1'] , 'callback_data' => "iranpay3" ]
    ];
    }
   if($PaySettingaqayepardakht == "onaqayepardakht"){
        $step_payment['inline_keyboard'][] = [
            ['text' => $datatextbot['aqayepardakht'] , 'callback_data' => "aqayepardakht" ]
    ];
    }
    if($zarinpal == "onzarinpal"){
        $step_payment['inline_keyboard'][] = [
            ['text' => $datatextbot['zarinpal'] , 'callback_data' => "zarinpal" ]
    ];
    }
    if($zarinpey == "onzarinpey"){
        $zarinpeyLabel = trim($datatextbot['zarinpey'] ?? '');
        if($zarinpeyLabel === ''){
            $zarinpeyLabel = '🟠 زرین پی';
        }
        if($zarinpeyLabel !== ''){
            $step_payment['inline_keyboard'][] = [
                ['text' => $zarinpeyLabel , 'callback_data' => "zarinpey" ]
        ];
        }
    }
    if($paymentstatussnotverify == "onverifypay"){
        $step_payment['inline_keyboard'][] = [
            ['text' => $datatextbot['textpaymentnotverify'] , 'callback_data' => "paymentnotverify" ]
    ];
    }
    if(intval($paymentsstartelegram) == 1){
     $step_payment['inline_keyboard'][] = [
            ['text' => $datatextbot['text_star_telegram'] , 'callback_data' => "startelegrams" ]
    ];   
    }
    $step_payment['inline_keyboard'][] = [
            ['text' => "❌ بستن لیست" , 'callback_data' => "colselist" ]
    ];
    $step_payment = json_encode($step_payment);
