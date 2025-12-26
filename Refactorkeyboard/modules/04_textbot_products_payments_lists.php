<?php
if (function_exists('rf_set_module')) { rf_set_module('Refactorkeyboard/modules/04_textbot_products_payments_lists.php'); }
$textbot = json_encode([
    'keyboard' => [
        [['text' => "تنظیم متن شروع"], ['text' => "دکمه سرویس خریداری شده"]],
        [['text' => "دکمه اکانت تست"], ['text' => "دکمه سوالات متداول"]],
        [['text' => "متن دکمه 📚 آموزش"], ['text' => "متن دکمه ☎️ پشتیبانی"]],
        [['text' => "دکمه افزایش موجودی"],['text' => "متن دکمه زیرمجموعه گیری"]],
        [['text' => "متن دکمه خرید اشتراک"], ['text' => "متن دکمه لیست تعرفه"]],
        [['text' => "متن توضیحات لیست تعرفه"]],
        [['text' => "متن دکمه کیف پول"],['text' => "متن پیش فاکتور"]],
        [['text' => "📝 تنظیم متن توضیحات عضویت اجباری"]],
        [['text' => "📝 تنظیم متن توضیحات سوالات متداول"]],
        [['text' => "⚖️ متن قانون"],['text' => "متن بعد خرید"]],
        [['text' => "متن بعد خرید ibsng"],['text' => "دکمه تمدید"]],
        [['text' => "متن بعد گرفتن اکانت تست"],['text' =>"متن کرون تست"]],
        [['text' => "متن بعد گرفتن اکانت دستی"]],
        [['text' => "متن بعد گرفتن اکانت WGDashboard"]],
        [['text' => "متن انتخاب لوکیشن"],['text' => "متن دکمه کد هدیه"]],
        [['text' => "متن درخواست نمایندگی"],['text' => "متن دکمه  نمایندگی"]],
        [['text' => "متن دکمه گردونه شانس"],['text' => "متن کارت به کارت"]],
        [['text' => "تنظیم متن کارت به کارت خودکار"]],
        [['text' => "متن توضیحات درخواست نمایندگی"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
//--------------------------------------------------
$stmt = $pdo->prepare("SHOW TABLES LIKE 'protocol'");
$stmt->execute();
$result = $stmt->fetchAll();
$table_exists = count($result) > 0;
if ($table_exists) {
    $getdataprotocol = select("protocol","*",null,null,"fetchAll");
    $protocol = [];
    foreach($getdataprotocol as $result)
    {
        $protocol[] = [['text'=>$result['NameProtocol']]];
    }
    $protocol[] = [['text'=>$textbotlang['Admin']['backadmin']]];
    $keyboardprotocollist = json_encode(['resize_keyboard'=>true,'keyboard'=> $protocol]);
 }
//--------------------------------------------------
$stmt = $pdo->prepare("SHOW TABLES LIKE 'product'");
$stmt->execute();
$result = $stmt->fetchAll();
$table_exists = count($result) > 0;
if ($table_exists) {
    $product = [];
    $stmt = $pdo->prepare("SELECT * FROM product WHERE Location = :text or Location = '/all' ");
    $stmt->bindParam(':text', $text  , PDO::PARAM_STR);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $product[] = [$row['name_product']];
    }
    $list_product = [
        'keyboard' => [],
        'resize_keyboard' => true,
    ];
    $list_product['keyboard'][] = [
        ['text' => $textbotlang['Admin']['backadmin']],
    ];
    foreach ($product as $button) {
        $list_product['keyboard'][] = [
            ['text' => $button[0]]
        ];
    }
    $json_list_product_list_admin = json_encode($list_product);
}
//--------------------------------------------------
$stmt = $pdo->prepare("SHOW TABLES LIKE 'Discount'");
$stmt->execute();
$result = $stmt->fetchAll();
$table_exists = count($result) > 0;
if ($table_exists) {
    $Discount = [];
    $stmt = $pdo->prepare("SELECT * FROM Discount");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $Discount[] = [$row['code']];
    }
    $list_Discount = [
        'keyboard' => [],
        'resize_keyboard' => true,
    ];
    $list_Discount['keyboard'][] = [
        ['text' => $textbotlang['Admin']['backadmin']],
    ];
    foreach ($Discount as $button) {
        $list_Discount['keyboard'][] = [
            ['text' => $button[0]]
        ];
    }
    $json_list_Discount_list_admin = json_encode($list_Discount);
}
//--------------------------------------------------
$stmt = $pdo->prepare("SHOW TABLES LIKE 'Inbound'");
$stmt->execute();
$result = $stmt->fetchAll();
$table_exists = count($result) > 0;
if ($table_exists) {
    $Inboundkeyboard = [];
    $stmt = $pdo->prepare("SELECT * FROM Inbound WHERE location = :Processing_value AND protocol = :text");
    $stmt->bindParam(':text', $text  , PDO::PARAM_STR);
    $stmt->bindParam(':Processing_value', $users['Processing_value']  , PDO::PARAM_STR);
    $stmt->execute();
if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $Inboundkeyboard[] = [$row['NameInbound']];
}
    
}
    $list_Inbound = [
        'keyboard' => [],
        'resize_keyboard' => true,
    ];
    foreach ($Inboundkeyboard as $button) {
        $list_Inbound['keyboard'][] = [
            ['text' => $button[0]]
        ];
    }
        $list_Inbound['keyboard'][] = [
        ['text' => $textbotlang['Admin']['backadmin']],
    ];
    $json_list_Inbound_list_admin = json_encode($list_Inbound);
}
//--------------------------------------------------
$stmt = $pdo->prepare("SHOW TABLES LIKE 'DiscountSell'");
$stmt->execute();
$result = $stmt->fetchAll();
$table_exists = count($result) > 0;
if ($table_exists) {
    $DiscountSell = [];
    $stmt = $pdo->prepare("SELECT * FROM DiscountSell");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $DiscountSell[] = [$row['codeDiscount']];
    }
    $list_Discountsell = [
        'keyboard' => [],
        'resize_keyboard' => true,
    ];
    $list_Discountsell['keyboard'][] = [
        ['text' => $textbotlang['Admin']['backadmin']],
    ];
    foreach ($DiscountSell as $button) {
        $list_Discountsell['keyboard'][] = [
            ['text' => $button[0]]
        ];
    }
    $json_list_Discount_list_admin_sell = json_encode($list_Discountsell);
}
$payment = json_encode([
    'inline_keyboard' => [
        [['text' => "💰 پرداخت و دریافت سرویس", 'callback_data' => "confirmandgetservice"]],
        [['text' => "🎁 ثبت کد تخفیف", 'callback_data' => "aptdc"]],
        [['text' => $textbotlang['users']['backbtn'] ,  'callback_data' => "backuser"]]
    ]
]);
$paymentom = json_encode([
    'inline_keyboard' => [
        [['text' => "💰 پرداخت و دریافت سرویس", 'callback_data' => "confirmandgetservice"]],
        [['text' => $textbotlang['users']['backbtn'] ,  'callback_data' => "backuser"]]
    ]
]);
$change_product = json_encode([
    'keyboard' => [
        [['text' => "قیمت"], ['text' => "حجم"], ['text' => "زمان"]],
        [['text' => "نام محصول"],['text' => "نوع کاربری"]],
        [['text' => "نوع ریست حجم"],['text' => "یادداشت"]],
        [['text' => "موقعیت محصول"],['text' => "دسته بندی"]],
        [['text' => "🎛 تنظیم اینباند"],['text' => "نمایش برای خرید اول"]],
        [['text' => "مخفی کردن پنل"],['text' => "حذف کلی پنل های مخفی"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);

$keyboardprotocol = json_encode([
    'keyboard' => [
        [['text' => "vless"],['text' => "vmess"],['text' => "trojan"]],
        [['text' => "shadowsocks"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$MethodUsername = json_encode([
    'keyboard' => [
        [['text' => "نام کاربری + عدد به ترتیب"]],
        [['text' => "آیدی عددی + حروف و عدد رندوم"]],
        [['text' => "نام کاربری دلخواه"]],
        [['text' => "نام کاربری دلخواه + عدد رندوم"]],
        [['text' => "متن دلخواه + عدد رندوم"]],
        [['text' => "متن دلخواه + عدد ترتیبی"]],
        [['text' => "آیدی عددی+عدد ترتیبی"]],
        [['text' => "متن دلخواه نماینده + عدد ترتیبی"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$optionMarzban = json_encode([
    'keyboard' => [
        [['text' => "⚙️ وضعیت قابلیت ها پنل"]],
        [['text' => "✍️ نام پنل"],['text' => "❌ حذف پنل"]],
        [['text' => "🔐 ویرایش رمز عبور"],['text' => "👤 ویرایش نام کاربری"]],
        [['text'=>"🔗 ویرایش آدرس پنل"],['text' => "⚙️ تنظیم پروتکل و اینباند"]],
        [['text' => "🔋 روش تمدید سرویس"],['text' =>"💡 روش ساخت نام کاربری"]],
        [['text' => "🚨 محدودیت ساخت اکانت"],['text'=> "📍 تغییر گروه کاربری"]],
        [['text' => "⏳ زمان سرویس تست"], ['text' => "💾 حجم اکانت تست"]],
        [['text' => "⚙️ قیمت حجم سرویس دلخواه"],['text' => "➕ قیمت حجم اضافه"]],
        [['text' => "⏳ قیمت زمان اضافه"],['text' => "⏳ قیمت زمان دلخواه"]],
        [['text' => "🌍 قیمت تغییر لوکیشن"]],
        [['text' => "📍 حداقل حجم دلخواه"],['text' => "📍 حداکثر حجم دلخواه"]],
        [['text' => "📍 حداقل زمان دلخواه"],['text' => "📍 حداکثر زمان دلخواه"]],
        [['text' => "⚙️  اینباند اکانت غیرفعال"]],
        [['text' => "🫣 مخفی کردن پنل برای یک کاربر"]],
        [['text' => "❌  حذف کاربر از لیست مخفی شدگان"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$optionibsng = json_encode([
    'keyboard' => [
        [['text' => "⚙️ وضعیت قابلیت ها پنل"]],
        [['text' => "✍️ نام پنل"],['text' => "❌ حذف پنل"]],
        [['text' => "🔐 ویرایش رمز عبور"],['text' => "👤 ویرایش نام کاربری"]],
        [['text'=>"🔗 ویرایش آدرس پنل"],['text' => '🎛 تنظیم نام گروه']],
        [['text' => "🔋 روش تمدید سرویس"],['text' =>"💡 روش ساخت نام کاربری"]],
        [['text' => "🚨 محدودیت ساخت اکانت"],['text'=> "📍 تغییر گروه کاربری"]],
        [['text' => "⚙️ قیمت حجم سرویس دلخواه"],['text' => "➕ قیمت حجم اضافه"]],
        [['text' => "⏳ قیمت زمان اضافه"],['text' => "⏳ قیمت زمان دلخواه"]],
        [['text' => "📍 حداقل حجم دلخواه"],['text' => "📍 حداکثر حجم دلخواه"]],
        [['text' => "📍 حداقل زمان دلخواه"],['text' => "📍 حداکثر زمان دلخواه"]],
        [['text' => "🫣 مخفی کردن پنل برای یک کاربر"]],
        [['text' => "❌  حذف کاربر از لیست مخفی شدگان"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$option_mikrotik = json_encode([
    'keyboard' => [
        [['text' => "⚙️ وضعیت قابلیت ها پنل"]],
        [['text' => "✍️ نام پنل"],['text' => "❌ حذف پنل"]],
        [['text' => "🔐 ویرایش رمز عبور"],['text' => "👤 ویرایش نام کاربری"]],
        [['text'=>"🔗 ویرایش آدرس پنل"],['text' => '🎛 تنظیم نام گروه']],
        [['text' => "🔋 روش تمدید سرویس"],['text' =>"💡 روش ساخت نام کاربری"]],
        [['text' => "🚨 محدودیت ساخت اکانت"],['text'=> "📍 تغییر گروه کاربری"]],
        [['text' => "⚙️ قیمت حجم سرویس دلخواه"],['text' => "➕ قیمت حجم اضافه"]],
        [['text' => "⏳ قیمت زمان اضافه"],['text' => "⏳ قیمت زمان دلخواه"]],
        [['text' => "📍 حداقل حجم دلخواه"],['text' => "📍 حداکثر حجم دلخواه"]],
        [['text' => "📍 حداقل زمان دلخواه"],['text' => "📍 حداکثر زمان دلخواه"]],
        [['text' => "🫣 مخفی کردن پنل برای یک کاربر"]],
        [['text' => "❌  حذف کاربر از لیست مخفی شدگان"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$options_ui = json_encode([
    'keyboard' => [
        [['text' => "⚙️ وضعیت قابلیت ها پنل"]],
        [['text' => "✍️ نام پنل"],['text' => "❌ حذف پنل"]],
        [['text' => "🔐 ویرایش رمز عبور"],['text' => "👤 ویرایش نام کاربری"]],
        [['text'=>"🔗 ویرایش آدرس پنل"],['text' => "⚙️ تنظیم پروتکل و اینباند"]],
        [['text' => "🔋 روش تمدید سرویس"],['text' =>"💡 روش ساخت نام کاربری"]],
        [['text' => "🚨 محدودیت ساخت اکانت"],['text'=> "📍 تغییر گروه کاربری"]],
        [['text' => "⏳ زمان سرویس تست"], ['text' => "💾 حجم اکانت تست"]],
        [['text' => "⚙️ قیمت حجم سرویس دلخواه"],['text' => "➕ قیمت حجم اضافه"]],
        [['text' => "⏳ قیمت زمان اضافه"],['text' => "⏳ قیمت زمان دلخواه"]],
        [['text' => "🌍 قیمت تغییر لوکیشن"]],
        [['text' => "📍 حداقل حجم دلخواه"],['text' => "📍 حداکثر حجم دلخواه"]],
        [['text' => "📍 حداقل زمان دلخواه"],['text' => "📍 حداکثر زمان دلخواه"]],
        [['text' => "⚙️  اینباند اکانت غیرفعال"]],
        [['text' => "🫣 مخفی کردن پنل برای یک کاربر"]],
        [['text' => "❌  حذف کاربر از لیست مخفی شدگان"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$optionwg = json_encode([
    'keyboard' => [
        [['text' => "⚙️ وضعیت قابلیت ها پنل"]],
        [['text' => "✍️ نام پنل"],['text' => "❌ حذف پنل"]],
        [['text' => "🔐 ویرایش رمز عبور"]],
        [['text'=>"🔗 ویرایش آدرس پنل"],['text' => "💎 تنظیم شناسه اینباند"]],
        [['text' => "🔋 روش تمدید سرویس"],['text' =>"💡 روش ساخت نام کاربری"]],
        [['text' => "🚨 محدودیت ساخت اکانت"],['text'=> "📍 تغییر گروه کاربری"]],
        [['text' => "⏳ زمان سرویس تست"], ['text' => "💾 حجم اکانت تست"]],
        [['text' => "⚙️ قیمت حجم سرویس دلخواه"],['text' => "➕ قیمت حجم اضافه"]],
        [['text' => "⏳ قیمت زمان اضافه"],['text' => "⏳ قیمت زمان دلخواه"]],
        [['text' => "🌍 قیمت تغییر لوکیشن"]],
        [['text' => "📍 حداقل حجم دلخواه"],['text' => "📍 حداکثر حجم دلخواه"]],
        [['text' => "📍 حداقل زمان دلخواه"],['text' => "📍 حداکثر زمان دلخواه"]],
        [['text' => "⚙️  اینباند اکانت غیرفعال"]],
        [['text' => "🫣 مخفی کردن پنل برای یک کاربر"]],
        [['text' => "❌  حذف کاربر از لیست مخفی شدگان"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
