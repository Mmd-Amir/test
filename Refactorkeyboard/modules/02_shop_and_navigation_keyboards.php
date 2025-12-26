<?php
if (function_exists('rf_set_module')) { rf_set_module('Refactorkeyboard/modules/02_shop_and_navigation_keyboards.php'); }
$keyboardhelpadmin = json_encode([
    'keyboard' => [
        [['text' => "📚 اضافه کردن آموزش"], ['text' => "❌ حذف آموزش"]],
        [['text' => "✏️ ویرایش آموزش"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$shopkeyboard = json_encode([
    'keyboard' => [
        [['text' => "🛒 وضعیت قابلیت های فروشگاه"]],
        [['text' => "🗂 مدیریت دسته بندی"],['text' => "🛍 مدیریت محصولات"]],
        [['text' => "🎁 ساخت کد هدیه"],['text' => "❌ حذف کد هدیه"]],
        [['text' => "🎁 ساخت کد تخفیف"],['text' => "❌ حذف کد تخفیف"]],
        [['text' => "⬇️ حداقل موجودی خرید عمده"],['text' => "🎁 کش بک تمدید"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$keyboard_Category_manage = json_encode([
    'keyboard' => [
        [['text' => "🛒 اضافه کردن دسته بندی"],['text' => "❌ حذف دسته بندی"]],
        [['text' => "✏️ ویرایش دسته بندی"]],
        [['text' => "⬅️ بازگشت به منوی فروشگاه"]]
    ],
    'resize_keyboard' => true
    ]);
$keyboard_shop_manage = json_encode([
    'keyboard' => [
        [['text' => "🛍 اضافه کردن محصول"], ['text' => "❌ حذف محصول"]],
        [['text' => "✏️ ویرایش محصول"]],
        [['text' => "⬆️ افزایش گروهی قیمت"],['text' => "⬇️ کاهش  گروهی قیمت"]],
        [['text' => "⬅️ بازگشت به منوی فروشگاه"]]
    ],
    'resize_keyboard' => true
]);
if($setting['inlinebtnmain'] == "oninline"){
    $confrimrolls = json_encode([
    'inline_keyboard' => [
        [
            ['text' => "✅ قوانین را می پذیرم", 'callback_data' => "acceptrule"],
            ],
    ]
    ]);
}else{
$confrimrolls = json_encode([
    'keyboard' => [
        [['text' => "✅ قوانین را می پذیرم"]],
    ],
    'resize_keyboard' => true
]);
}
$request_contact = json_encode([
    'keyboard' => [
        [['text' => "☎️ ارسال شماره تلفن", 'request_contact' => true]],
        [['text' => $textbotlang['users']['backbtn']]]
    ],
    'resize_keyboard' => true
]);
$Feature_status = json_encode([
    'keyboard' => [
        [['text' => "قابلیت مشاهده اطلاعات اکانت"]],
        [['text' => "قابلیت اکانت تست"], ['text' => "قابلیت آموزش"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
$channelkeyboard = json_encode([
    'keyboard' => [
        [['text' => "اضافه کردن کانال"],['text' => "حذف کانال"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
if($setting['inlinebtnmain'] == "oninline"){
    $backuser = json_encode([
        'inline_keyboard' => [
        [['text' => $textbotlang['users']['backbtn'], 'callback_data' => "backuser"]]
    ],
]);
}else{
$backuser = json_encode([
        'keyboard' => [
        [['text' => $textbotlang['users']['backbtn']]]
    ],
    'resize_keyboard' => true,
    'input_field_placeholder' =>"برای بازگشت روی دکمه زیر کلیک کنید"
]);
}
$backadmin = json_encode([
    'keyboard' => [
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true,
    'input_field_placeholder' =>"برای بازگشت روی دکمه زیر کلیک کنید"
]);
