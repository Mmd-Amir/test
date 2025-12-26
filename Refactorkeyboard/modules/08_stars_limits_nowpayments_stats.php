<?php
if (function_exists('rf_set_module')) { rf_set_module('Refactorkeyboard/modules/08_stars_limits_nowpayments_stats.php'); }
$Startelegram = json_encode([
    'keyboard' => [
        [['text' => "🗂 نام درگاه استار"]],
        [['text' => "💰 کش بک استار"],['text' => "📚 تنظیم آموزش استار"]],
        [['text' => "⬇️ حداقل مبلغ استار"],['text' => "⬆️ حداکثر مبلغ استار"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$keyboardchangelimit = json_encode([
    'keyboard' => [
        [['text' => "🆓 محدودیت رایگان"],['text' => "↙️ محدودیت کلی"]],
        [['text' => "🔄 ریست محدودیت کل کاربران"]],
        [['text' => $textbotlang['Admin']['backadmin']]]
    ],
    'resize_keyboard' => true
]);
function KeyboardCategoryadmin(){
    global $pdo,$textbotlang;
    $stmt = $pdo->prepare("SELECT * FROM category");
    $stmt->execute();
    $list_category = [
        'keyboard' => [],
        'resize_keyboard' => true,
    ];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $list_category['keyboard'][] = [['text' =>$row['remark']]];
    }
    $list_category['keyboard'][] = [
        ['text' => $textbotlang['Admin']['backadmin']],
    ];
    return json_encode($list_category);
}
$nowpayment_setting_keyboard = json_encode([
    'keyboard' => [
        [['text' => "API NOWPAYMENT"],['text' => "🗂 نام درگاه nowpayment"]],
        [['text' => "💰 کش بک nowpayment"],['text' => "📚 تنظیم آموزش nowpayment"]],
        [['text' => "⬇️ حداقل مبلغ nowpayment"],['text' => "⬆️ حداکثر مبلغ nowpayment"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$Exception_auto_cart_keyboard = json_encode([
    'keyboard' => [
        [['text' => "➕ استثناء کردن کاربر"],['text' => "❌ حذف کاربر از لیست"]],
        [['text' => "👁 نمایش لیست افراد"]],
        [['text' => "▶️ بازگشت به منوی تظنیمات کارت"]]
    ],
    'resize_keyboard' => true
]);
function keyboard_config($config_split,$id_invoice,$back_active = true){
    global $textbotlang;
    $keyboard_config = ['inline_keyboard' => []];
    $keyboard_config['inline_keyboard'][] = [
        ['text' => "⚙️ کانفیگ", 'callback_data' => "none"],
        ['text' => "✏️نام کانفیگ", 'callback_data' => "none"],
        ];
    foreach (array_values($config_split) as $i => $config){
        if(!is_string($config) || $config === ''){
            error_log('Invalid configuration entry encountered while building keyboard');
            continue;
        }

        $split_config = explode("://",$config,2);
        if(count($split_config) !== 2){
            error_log('Malformed configuration string: missing scheme separator');
            continue;
        }

        $type_prtocol = $split_config[0];
        $payload = $split_config[1];
        if(isBase64($payload)){
            $decoded = base64_decode($payload, true);
            if($decoded === false){
                error_log('Failed to decode base64 configuration payload');
                continue;
            }
            $payload = $decoded;
        }

        $displayName = '';
        if($type_prtocol == "vmess"){
            $configJson = json_decode($payload, true);
            if(is_array($configJson) && isset($configJson['ps'])){
                $displayName = $configJson['ps'];
            }
        }else{
            $parts = explode("#",$payload,2);
            if(count($parts) === 2){
                $displayName = $parts[1];
            }
        }

        if($displayName === '' || $displayName === null){
            $displayName = sprintf('Config %d', $i + 1);
        }

        $keyboard_config['inline_keyboard'][] = [
            ['text' => "دریافت کانفیگ", 'callback_data' => "configget_{$id_invoice}_$i"],
            ['text' => urldecode($displayName), 'callback_data' => "none"],
        ];

    }
    $keyboard_config['inline_keyboard'][] = [['text' => "⚙️ دریافت همه کانفیگ ها", 'callback_data' => "configget_$id_invoice"."_1520"]];
    if($back_active){
    $keyboard_config['inline_keyboard'][] = [['text' => $textbotlang['users']['stateus']['backinfo'], 'callback_data' => "product_$id_invoice"]];
    }
    return json_encode($keyboard_config);
}
$keyboard_buy = json_encode([
        'inline_keyboard' => [
            [
                ['text' => "🛍خرید اشتراک", 'callback_data' => 'buy'],
            ],
        ]
    ]);
$keyboard_stat = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => "⏱️ آمار کل", 'callback_data' => 'stat_all_bot'],
                ],[
                    ['text' => "⏱️ یک ساعت اخیر", 'callback_data' => 'hoursago_stat'],
                ],
                [
                    ['text' => "⛅️ امروز", 'callback_data' => 'today_stat'],
                    ['text' => "☀️ دیروز", 'callback_data' => 'yesterday_stat'],
                ],
                [
                    ['text' => "☀️ ماه فعلی ", 'callback_data' => 'month_current_stat'],
                    ['text' => "⛅️ ماه قبل", 'callback_data' => 'month_old_stat'],
                ],
                [
                    ['text' => "🗓 مشاهده آمار در تاریخ مشخص", 'callback_data' => 'view_stat_time'],
                ],
                [
                    ['text' => "❌ بستن", 'callback_data' => 'close_stat'],
                ]
            ]
        ]);
