<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/payment/settings.php'); }

if (!function_exists('getPaySettingValue')) {
    function getPaySettingValue($name, $default = null)
    {
        $result = select("PaySetting", "ValuePay", "NamePay", $name, "select");
        if (!is_array($result) || !array_key_exists('ValuePay', $result)) {
            return $default;
        }

        return $result['ValuePay'];
    }
}


if (!function_exists('formatPaymentReportNote')) {
    function formatPaymentReportNote($rawNote)
    {
        if ($rawNote === null) {
            return '';
        }

        if (is_array($rawNote)) {
            return json_encode($rawNote, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (!is_scalar($rawNote)) {
            return '';
        }

        $rawNote = trim((string) $rawNote);
        if ($rawNote === '') {
            return '';
        }

        $decoded = json_decode($rawNote, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            if (($decoded['gateway'] ?? '') === 'zarinpay') {
                $lines = ['زرین‌پی'];
                $fieldMap = [
                    'payment_id' => 'شناسه پرداخت',
                    'reference_id' => 'شماره پیگیری',
                    'authority' => 'کد اعتبار',
                    'order_id' => 'کد سفارش',
                    'code' => 'کد تأیید',
                ];

                foreach ($fieldMap as $key => $label) {
                    $value = $decoded[$key] ?? null;
                    if ($value !== null && $value !== '') {
                        $lines[] = sprintf('%s: %s', $label, $value);
                    }
                }

                if (!empty($decoded['amount'])) {
                    $lines[] = 'مبلغ تراکنش (ریال): ' . number_format((int) $decoded['amount']);
                }

                if (!empty($decoded['card_pan'])) {
                    $lines[] = 'کارت پرداخت‌کننده: ' . $decoded['card_pan'];
                }

                if (!empty($decoded['paid_at'])) {
                    $lines[] = 'زمان پرداخت: ' . $decoded['paid_at'];
                }

                return implode("\n", array_filter($lines));
            }

            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return $rawNote;
    }
}
