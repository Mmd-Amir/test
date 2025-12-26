<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/db/crud.php'); }

if (!function_exists('normaliseUpdateValue')) {
    function normaliseUpdateValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $value;
    }
}


if (!function_exists('update')) {
    function update($table, $field, $newValue, $whereField = null, $whereValue = null)
    {
        global $pdo, $user;

        $valueToStore = normaliseUpdateValue($newValue);
        $whereValueToStore = $whereField !== null ? normaliseUpdateValue($whereValue) : null;

        ensureColumnExistsForUpdate($table, $field, $valueToStore);
        if ($whereField !== null) {
            ensureColumnExistsForUpdate($table, $whereField, $whereValueToStore);
        }

        $executeUpdate = function ($value) use ($pdo, $table, $field, $whereField, $whereValueToStore) {
            if ($whereField !== null) {
                $stmt = $pdo->prepare("UPDATE $table SET $field = ? WHERE $whereField = ?");
                $stmt->execute([$value, $whereValueToStore]);
            } else {
                $stmt = $pdo->prepare("UPDATE $table SET $field = ?");
                $stmt->execute([$value]);
            }

            return isset($stmt) ? $stmt->rowCount() : 0;
        };

        $affectedRows = 0;

        try {
            $affectedRows = $executeUpdate($valueToStore);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Incorrect string value') !== false) {
                $tableConverted = ensureTableUtf8mb4($table);
                if ($tableConverted) {
                    try {
                        $affectedRows = $executeUpdate($valueToStore);
                    } catch (PDOException $retryException) {
                        error_log('Retry after charset conversion failed: ' . $retryException->getMessage());
                        throw $retryException;
                    }
                } else {
                    $fallbackValue = is_string($valueToStore) ? @iconv('UTF-8', 'UTF-8//IGNORE', $valueToStore) : $valueToStore;
                    if ($fallbackValue === false) {
                        $fallbackValue = '';
                    }
                    $affectedRows = $executeUpdate($fallbackValue);
                }
            } else {
                throw $e;
            }
        }

        if ($whereField !== null && $affectedRows === 0) {
            if ($whereValueToStore === null) {
                $existsStmt = $pdo->prepare("SELECT 1 FROM $table WHERE $whereField IS NULL LIMIT 1");
                $existsStmt->execute();
            } else {
                $existsStmt = $pdo->prepare("SELECT 1 FROM $table WHERE $whereField = ? LIMIT 1");
                $existsStmt->execute([$whereValueToStore]);
            }

            $rowExists = $existsStmt->fetchColumn();

            if ($rowExists === false) {
                $columns = [$field];
                $values = [$valueToStore];

                if ($field !== $whereField) {
                    $columns[] = $whereField;
                    $values[] = $whereValueToStore;
                }

                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $columnList = implode(', ', array_map(function ($column) {
                    return "`$column`";
                }, $columns));

                try {
                    $insertStmt = $pdo->prepare("INSERT INTO $table ($columnList) VALUES ($placeholders)");
                    $insertStmt->execute($values);
                } catch (PDOException $insertException) {
                    error_log('Failed to insert missing row during update fallback: ' . $insertException->getMessage());
                }
            }
        }

        $date = date("Y-m-d H:i:s");
        if (!isset($user['step'])) {
            $user['step'] = '';
        }
        $logValue = is_scalar($valueToStore) ? $valueToStore : json_encode($valueToStore, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $logss = "{$table}_{$field}_{$logValue}_{$whereField}_{$whereValue}_{$user['step']}_$date";
        if ($field != "message_count" && $field != "last_message_time") {
            file_put_contents('log.txt', "\n" . $logss, FILE_APPEND);
        }

        clearSelectCache($table);
    }
}


if (!function_exists('getSelectCacheStore')) {
    function &getSelectCacheStore()
    {
        static $store = [
            'results' => [],
            'tableIndex' => [],
        ];

        return $store;
    }
}


if (!function_exists('clearSelectCache')) {
    function clearSelectCache($table = null)
    {
        $store =& getSelectCacheStore();

        if ($table === null) {
            $store['results'] = [];
            $store['tableIndex'] = [];
            return;
        }

        if (!isset($store['tableIndex'][$table])) {
            return;
        }

        foreach (array_keys($store['tableIndex'][$table]) as $cacheKey) {
            unset($store['results'][$cacheKey]);
        }

        unset($store['tableIndex'][$table]);
    }
}


if (!function_exists('select')) {
    function select($table, $field, $whereField = null, $whereValue = null, $type = "select", $options = [])
    {
        $pdo = getDatabaseConnection();

        if (!($pdo instanceof PDO)) {
            error_log('select: Database connection is unavailable.');

            switch ($type) {
                case 'count':
                    return 0;
                case 'FETCH_COLUMN':
                case 'fetchAll':
                    return [];
                default:
                    return null;
            }
        }

        $useCache = true;
        if (is_array($options) && array_key_exists('cache', $options)) {
            $useCache = (bool) $options['cache'];
        }

        $cacheKey = null;
        if ($useCache) {
            $cacheKey = hash('sha256', json_encode([
                $table,
                $field,
                $whereField,
                $whereValue,
                $type,
            ], JSON_UNESCAPED_UNICODE));

            $store =& getSelectCacheStore();
            if (isset($store['results'][$cacheKey])) {
                return $store['results'][$cacheKey];
            }
        }

        $query = "SELECT $field FROM $table";

        if ($whereField !== null) {
            $query .= " WHERE $whereField = :whereValue";
        }

        try {
            $stmt = $pdo->prepare($query);
            if ($whereField !== null) {
                $stmt->bindParam(':whereValue', $whereValue, PDO::PARAM_STR);
            }

            $stmt->execute();
            if ($type == "count") {
                $result = $stmt->rowCount();
            } elseif ($type == "FETCH_COLUMN") {
                $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
                if ($table === 'admin' && $field === 'id_admin') {
                    global $adminnumber;
                    if (!is_array($results)) {
                        $results = [];
                    }

                    $results = array_values(array_unique(array_filter($results, function ($value) {
                        return $value !== null && $value !== '';
                    })));

                    if (empty($results) && isset($adminnumber) && $adminnumber !== '') {
                        $results[] = (string) $adminnumber;
                    }
                }
                $result = $results;
            } elseif ($type == "fetchAll") {
                $result = $stmt->fetchAll();
            } else {
                $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
                $result = $fetched === false ? null : $fetched;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die("Query failed: " . $e->getMessage());
        }

        if ($useCache && $cacheKey !== null) {
            $store =& getSelectCacheStore();
            $store['results'][$cacheKey] = $result;
            if (!isset($store['tableIndex'][$table])) {
                $store['tableIndex'][$table] = [];
            }
            $store['tableIndex'][$table][$cacheKey] = true;
        }

        return $result;
    }
}
