<?php
if (function_exists('rf_set_module')) { rf_set_module('Refactorkeyboard/modules/06_departments_lottery_wheel_links.php'); }
//------------------  [ list departeman ]----------------//
$stmt = $pdo->prepare("SHOW TABLES LIKE 'departman'");
$stmt->execute();
$result = $stmt->fetchAll();
$table_exists = count($result) > 0;
$departeman = [];

$departemans = [
    'keyboard' => [],
    'resize_keyboard' => true,
];

if ($table_exists) {
    $stmt = $pdo->prepare("SELECT * FROM departman");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $departeman[] = [$row['name_departman']];
    }
    foreach ($departeman as $button) {
        $departemans['keyboard'][] = [
            ['text' => $button[0]]
        ];
    }
}

$departemans['keyboard'][] = [
    ['text' => $textbotlang['Admin']['backadmin']],
    ['text' => $textbotlang['Admin']['backmenu']]
];

$departemanslist = json_encode($departemans);

// list departeman
$list_departman = ['inline_keyboard' => []];

if ($table_exists) {
    $stmt = $pdo->prepare("SELECT * FROM departman");
    $stmt->execute();
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $list_departman['inline_keyboard'][] = [[
            'text' => $result['name_departman'],
            'callback_data' => "departman_{$result['id']}"
        ]];
    }
}

$list_departman['inline_keyboard'][] = [
    ['text' => $textbotlang['users']['backbtn'], 'callback_data' => "backuser"],
];
$list_departman = json_encode($list_departman);
$active_panell =  json_encode([
    'keyboard' => [
        [['text' => "📣 گزارشات ربات"]],
    ],
    'resize_keyboard' => true
]);
$lottery =  json_encode([
    'keyboard' => [
        [['text' => "1️⃣ تنظیم جایزه نفر اول"],['text' => "2️⃣ تنظیم جایزه نفر دوم"]],
        [['text' => "3️⃣ تنظیم جایزه نفر سوم"]],
        [['text' => $textbotlang['Admin']['backadmin']]]
    ],
    'resize_keyboard' => true
]);
$wheelkeyboard =  json_encode([
    'keyboard' => [
        [['text' => "🎲 مبلغ برنده شدن کاربر"]],
        [['text' => $textbotlang['Admin']['backadmin']]]
    ],
    'resize_keyboard' => true
]);
$keyboardlinkapp = json_encode([
    'keyboard' => [
        [['text' => "🔗 اضافه کردن برنامه"],['text' => "❌ حذف برنامه"]],
        [['text' => "✏️ ویرایش برنامه"]],
        [['text' => $textbotlang['Admin']['backadmin']],['text' => $textbotlang['Admin']['backmenu']]]
    ],
    'resize_keyboard' => true
]);
