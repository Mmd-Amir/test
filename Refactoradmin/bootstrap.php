<?php
rf_set_module('admin/bootstrap.php');

$textadmin = ["panel", "/panel", $textbotlang['Admin']['textpaneladmin']];
$text_panel_admin_login_template = "💎 | Version Debug Bot: 3.8
📌 | Version Debug Mini App: 1.1

<blockquote>🔹 | این ربات کاملاً رایگان است و توسط توسعه‌دهنده میرزا عرضه شده و توسط Mmd | Amir دیباگ شده است.</blockquote>

<blockquote><a href=\"https://github.com/Mmd-Amir/mirza_pro\" style=\"color:#1e88ff;\">گیت هاب دیباگ کننده</a></blockquote>

<blockquote>🔹 | هرگونه فروش یا دریافت وجه بابت این ربات تخلف محسوب می‌شود.</blockquote>

<blockquote>🔹 | در صورت مشاهدهٔ فروش یا دریافت وجه، لطفاً وجه خود را پیگیری کرده و بازپس‌گیری نمایید.</blockquote>
";

if (!in_array($from_id, $admin_ids))
    return;



function normalizeXuiSingleSubscriptionBaseUrl($rawLink)
{
    $trimmed = trim((string) $rawLink);
    if ($trimmed === '') {
        return '';
    }

    $parts = preg_split('/\s+/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
    $candidate = trim((string) ($parts[0] ?? ''));
    if ($candidate === '') {
        return '';
    }

    $candidate = rtrim($candidate, '/');
    $urlForProcessing = $candidate;
    if (!preg_match('~^https?://~i', $urlForProcessing)) {
        $urlForProcessing = 'https://' . ltrim($urlForProcessing, '/');
    }

    if (!filter_var($urlForProcessing, FILTER_VALIDATE_URL)) {
        return $candidate;
    }

    $shouldTrim = false;
    $request = new CurlRequest($urlForProcessing);
    $response = $request->get();
    if (isset($response['status']) && $response['status'] >= 200 && $response['status'] < 400 && empty($response['error'])) {
        $body = $response['body'];
        if (isBase64($body)) {
            $body = base64_decode($body);
        }
        $protocols = ['vmess', 'vless', 'trojan', 'ss'];
        $sub_check = explode('://', $body)[0];
        if (in_array($sub_check, $protocols, true)) {
            $shouldTrim = true;
        }
    }

    if (!$shouldTrim) {
        $shouldTrim = hasLikelyXuiSubscriptionId($urlForProcessing);
    }

    $normalized = buildXuiSingleBaseUrl($urlForProcessing, $shouldTrim);
    if ($normalized === '' || preg_match('~^https?:$~i', $normalized)) {
        $normalized = buildXuiSingleBaseUrl($urlForProcessing, false);
    }

    return $normalized;
}

function buildXuiSingleBaseUrl($url, $dropLastSegment)
{
    $parsed = parse_url($url);
    if ($parsed === false) {
        return rtrim($url, '/');
    }

    $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
    $host = $parsed['host'] ?? '';
    $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
    $user = $parsed['user'] ?? '';
    $pass = $parsed['pass'] ?? '';

    $auth = '';
    if ($user !== '') {
        $auth = $user;
        if ($pass !== '') {
            $auth .= ':' . $pass;
        }
        $auth .= '@';
    }

    $path = $parsed['path'] ?? '';
    $path = trim($path, '/');
    if ($dropLastSegment && $path !== '') {
        $segments = explode('/', $path);
        array_pop($segments);
        $path = implode('/', $segments);
    }

    if ($path !== '') {
        $path = '/' . $path;
    }

    $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?' . $parsed['query'] : '';
    $fragment = isset($parsed['fragment']) && $parsed['fragment'] !== '' ? '#' . $parsed['fragment'] : '';

    return rtrim($scheme . $auth . $host . $port . $path, '/') . $query . $fragment;
}

function hasLikelyXuiSubscriptionId($url)
{
    $parsed = parse_url($url);
    if ($parsed === false) {
        return false;
    }

    $candidates = [];

    $path = $parsed['path'] ?? '';
    $path = trim($path, '/');
    if ($path !== '') {
        $segments = explode('/', $path);
        if (!empty($segments)) {
            $lastSegment = $segments[count($segments) - 1];
            if ($lastSegment !== '') {
                $candidates[] = $lastSegment;
            }
        }
    }

    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $queryParams);
        foreach ($queryParams as $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (is_scalar($item)) {
                        $candidates[] = (string) $item;
                    }
                }
            } elseif (is_scalar($value)) {
                $candidates[] = (string) $value;
            }
        }
    }

    foreach ($candidates as $candidate) {
        if ($candidate === '') {
            continue;
        }
        if (preg_match('~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$~i', $candidate)) {
            return true;
        }
        if (strlen($candidate) >= 16 && preg_match('~^[A-Za-z0-9=_-]+$~', $candidate)) {
            return true;
        }
    }

    return false;
}

function getPanelStateFromConfigFile($configPath)
{
    if (!is_string($configPath) || $configPath === '' || !is_readable($configPath)) {
        return null;
    }

    $configContents = file_get_contents($configPath);
    if ($configContents === false) {
        return null;
    }

    if (preg_match('/^\s*\/\/\/\s*\$new_marzban\s*=\s*true\s*;/m', $configContents)) {
        return 'marzban';
    }

    if (preg_match('/^\s*\$new_marzban\s*=\s*true\s*;/m', $configContents)) {
        return 'pasargad';
    }

    return null;
}

function getPanelStateLabel($state)
{
    switch ($state) {
        case 'pasargad':
            return 'پاسارگارد';
        case 'marzban':
            return 'مرزبان';
        default:
            return 'نامشخص';
    }
}

function buildPanelSelectionMessage($configPath)
{
    $currentState = getPanelStateFromConfigFile($configPath);
    $currentLabel = getPanelStateLabel($currentState);

    return "💠 لطفاً نوع پنل خود را انتخاب کنید 👇\n🧾 نوع فعلی پنل: {$currentLabel}";
}

function getPanelSelectionKeyboard()
{
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '🧩 مرزبان', 'callback_data' => 'set_panel_marzban'],
                ['text' => '🏛 پاسارگارد', 'callback_data' => 'set_panel_pasargad'],
            ],
        ],
    ];

    return json_encode($keyboard, JSON_UNESCAPED_UNICODE);
}

function updatePanelStateInConfigFile($configPath, $state)
{
    if (!is_string($configPath) || $configPath === '' || !is_readable($configPath) || !is_writable($configPath)) {
        return false;
    }

    $configContents = file_get_contents($configPath);
    if ($configContents === false) {
        return false;
    }

    $activePattern = '/^\s*\$new_marzban\s*=\s*true\s*;/m';
    $commentPattern = '/^\s*\/\/\/\s*\$new_marzban\s*=\s*true\s*;/m';
    $replacementLine = $state === 'pasargad' ? '$new_marzban = true;' : '///$new_marzban = true;';

    $count = 0;
    $updatedContents = preg_replace($activePattern, $replacementLine, $configContents, 1, $count);
    if ($updatedContents === null) {
        return false;
    }

    if ($count === 0) {
        $updatedContents = preg_replace($commentPattern, $replacementLine, $updatedContents, 1, $count);
        if ($updatedContents === null) {
            return false;
        }
    }

    if ($count === 0) {
        $closingTagPattern = '/\?>\s*$/';
        if (preg_match($closingTagPattern, $updatedContents)) {
            $updatedContents = preg_replace($closingTagPattern, $replacementLine . PHP_EOL . '?>', $updatedContents, 1);
            if ($updatedContents === null) {
                return false;
            }
        } else {
            $updatedContents .= PHP_EOL . $replacementLine . PHP_EOL;
        }
    }

    $result = file_put_contents($configPath, $updatedContents);
    if ($result === false) {
        return false;
    }

    clearstatcache(true, $configPath);

    return true;
}

function buildCronJobsKeyboard(): string
{
    if (!function_exists('getCronJobDefinitions') || !function_exists('loadCronSchedules') || !function_exists('describeCronSchedule')) {
        return json_encode(['inline_keyboard' => []]);
    }

    $definitions = getCronJobDefinitions();
    $schedules = loadCronSchedules();
    $keyboard = ['inline_keyboard' => []];

    foreach ($definitions as $key => $definition) {
        if (empty($definition['admin_label']) || empty($definition['script'])) {
            continue;
        }
        $schedule = $schedules[$key] ?? $definition['default'];
        $keyboard['inline_keyboard'][] = [
            ['text' => '⚙️ تنظیمات', 'callback_data' => "cronjob_config-{$key}"],
            ['text' => describeCronSchedule($schedule), 'callback_data' => 'cronjob_display'],
            ['text' => $definition['admin_label'], 'callback_data' => 'cronjob_display'],
        ];
    }

    $keyboard['inline_keyboard'][] = [
        ['text' => '🔙 بازگشت به منوی وضعیت', 'callback_data' => 'admin'],
    ];

    return json_encode($keyboard, JSON_UNESCAPED_UNICODE);
}

function getCronUnitTitle(string $unit): string
{
    $labels = [
        'minute' => 'دقیقه',
        'hour' => 'ساعت',
        'day' => 'روز',
        'disabled' => 'غیرفعال',
    ];

    return $labels[$unit] ?? $labels['minute'];
}

if (!in_array($from_id, $admin_ids))
    return;

$users_ids = select('user', 'id', null, null, 'FETCH_COLUMN');
if (!is_array($users_ids)) {
    $users_ids = [];
}

$domainhostsEscaped = htmlspecialchars($domainhosts, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$cronInstructionBlock = '';
if (function_exists('buildCronInstructionDetails')) {
    $cronInstructionBlock = buildCronInstructionDetails($domainhostsEscaped);
} else {
    $cronInstructionBlock = <<<CRONHTML
<b>🕒 بررسی وضعیت روزانه — هر 15 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/statusday.php</code>

<b>🔔 سرویس اعلان‌ها (Notification Service) — هر 1 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/NoticationsService.php</code>

<b>💳 بررسی انقضای پرداخت‌ها — هر 5 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/payment_expire.php</code>

<b>📩 ارسال پیام‌ها — هر 1 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/sendmessage.php</code>

<b>💰 پردازش پرداخت‌های Plisio — هر 3 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/plisio.php</code>

<b>⚙️ فعال‌سازی تنظیمات جدید — هر 1 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/activeconfig.php</code>

<b>🚫 غیرفعال‌سازی تنظیمات قدیمی — هر 1 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/disableconfig.php</code>

<b>🇮🇷 بررسی وضعیت پرداخت ایران‌پی — هر 1 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/iranpay1.php</code>

<b>🗂 تهیه نسخه‌ی پشتیبان (Backup) — هر 5 ساعت</b>
<code>curl https://{$domainhostsEscaped}/cronbot/backupbot.php</code>

<b>🎁 ارسال هدایا (Gift System) — هر 2 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/gift.php</code>

<b>👥 بررسی انقضای نمایندگان — هر 30 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/expireagent.php</code>

<b>⏸ بررسی وضعیت سفارش‌های معلق — هر 15 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/on_hold.php</code>

<b>🧪 تست تنظیمات سیستم — هر 2 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/configtest.php</code>

<b>🌐 بررسی Uptime نودها — هر 15 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/uptime_node.php</code>

<b>🖥 بررسی Uptime پنل‌ها — هر 15 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/uptime_panel.php</code>

<b>💳 انجام تراکنش‌های کارت‌به‌کارت — هر 1 دقیقه</b>
<code>curl https://{$domainhostsEscaped}/cronbot/croncard.php</code>
CRONHTML;
}

$miniAppInstructionText = <<<HTML
📌 آموزش فعالسازی مینی اپ در ربات BotFather

/mybots > Select Bot > Bot Setting >  Configure Mini App > Enable Mini App  > Edit Mini App URL

مراحل بالا را طی کنید سپس آدرس زیر را ارسال نمایید :

<code>https://{$domainhostsEscaped}/app/</code>

➖➖➖➖➖➖➖➖➖➖➖➖
⚙️ تنظیم کرون‌جاب‌ها در هاست


<b>⏱ تنها کرون‌جاب موردنیاز به صورت

*/1

 یعنی هر 1 دقیقه باید تنظیم کنید
</b>

<code>curl https://{$domainhostsEscaped}/cron/cron.php</code>
HTML;
