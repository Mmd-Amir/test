<?php
rf_set_module('helpers/compat_functions.php');
if (!function_exists('createForumTopicIfMissing')) {
    function createForumTopicIfMissing($currentId, $reportKey, $topicName, $channelId)
    {
        $numericId = intval($currentId);
        if ($numericId !== 0) {
            return;
        }

        $channelId = trim((string)$channelId);
        if ($channelId === '' || $channelId === '0') {
            return;
        }

        $response = telegram('createForumTopic', [
            'chat_id' => $channelId,
            'name' => $topicName
        ]);

        if (!is_array($response) || empty($response['ok'])) {
            $context = is_array($response) ? json_encode($response) : 'empty response';
            error_log("Failed to create forum topic {$reportKey}: {$context}");

            if (is_array($response) && isset($response['error_code']) && in_array($response['error_code'], [400, 403], true)) {
                update("topicid", "idreport", -1, "report", $reportKey);
            }

            return;
        }

        $threadId = $response['result']['message_thread_id'] ?? null;
        if ($threadId !== null) {
            update("topicid", "idreport", $threadId, "report", $reportKey);
        }
    }
}

if (!function_exists('buildExtendCategoryKeyboard')) {
    function buildExtendCategoryKeyboard(PDO $pdo, $panelName, $agent, $invoiceId, $serviceTimeFilter = null)
    {
        $stmt = $pdo->prepare("SELECT id, remark FROM category");
        $stmt->execute();
        $keyboardRows = [];
        while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $query = "SELECT 1 FROM product WHERE (Location = :location OR Location = '/all') AND agent = :agent AND category = :category AND one_buy_status = '0'";
            if ($serviceTimeFilter !== null) {
                $query .= " AND Service_time = :service_time";
            }
            $checkStmt = $pdo->prepare($query . " LIMIT 1");
            $checkStmt->bindValue(':location', $panelName, PDO::PARAM_STR);
            $checkStmt->bindValue(':agent', $agent, PDO::PARAM_STR);
            $checkStmt->bindValue(':category', $category['remark'], PDO::PARAM_STR);
            if ($serviceTimeFilter !== null) {
                $checkStmt->bindValue(':service_time', $serviceTimeFilter, PDO::PARAM_INT);
            }
            $checkStmt->execute();
            if (!$checkStmt->fetchColumn()) {
                continue;
            }
            $serviceTimeValue = $serviceTimeFilter === null ? '0' : (string)$serviceTimeFilter;
            $keyboardRows[] = [
                [
                    'text' => $category['remark'],
                    'callback_data' => sprintf('extendcategory_%s_%s_%s', $invoiceId, $serviceTimeValue, $category['id'])
                ]
            ];
        }
        return $keyboardRows;
    }
}
// ---- Compatibility wrapper: some codebases call update_fields(...) instead of update(...).
// It supports both signatures:
//   update_fields($table, ['col'=>val, ...], $whereField, $whereValue)
//   update_fields($table, $field, $value, $whereField, $whereValue)
if (!function_exists('update_fields')) {
    function update_fields(...$args)
    {
        if (!function_exists('update')) {
            // No underlying update() available.
            return false;
        }

        $argc = count($args);
        if ($argc === 0) {
            return false;
        }

        $table = $args[0] ?? null;

        // Signature A: (table, array fields, whereField?, whereValue?)
        if ($argc >= 2 && is_array($args[1])) {
            $fields = $args[1];
            $whereField = $args[2] ?? null;
            $whereValue = $args[3] ?? null;

            foreach ($fields as $field => $value) {
                update($table, $field, $value, $whereField, $whereValue);
            }
            return true;
        }

        // Signature B: (table, field, value, whereField?, whereValue?)
        if ($argc >= 3) {
            $field = $args[1];
            $value = $args[2];
            $whereField = $args[3] ?? null;
            $whereValue = $args[4] ?? null;

            update($table, $field, $value, $whereField, $whereValue);
            return true;
        }

        return false;
    }
}
