<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/db/schema.php'); }

if (!function_exists('ensureTableUtf8mb4')) {
    function ensureTableUtf8mb4($table)
    {
        global $pdo;

        try {
            $stmt = $pdo->prepare('SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
            $stmt->execute([$table]);
            $currentCollation = $stmt->fetchColumn();

            if ($currentCollation === false) {
                error_log("Failed to detect current collation for table {$table}");
                return false;
            }

            if (stripos((string) $currentCollation, 'utf8mb4') === 0) {
                return true;
            }

            $pdo->exec("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            return true;
        } catch (PDOException $e) {
            error_log('Failed to convert table to utf8mb4: ' . $e->getMessage());
            return false;
        }
    }
}


if (!function_exists('ensureCardNumberTableSupportsUnicode')) {
    function ensureCardNumberTableSupportsUnicode()
    {
        global $connect;

        if (!isset($connect) || !($connect instanceof mysqli)) {
            return;
        }

        try {
            if (method_exists($connect, 'character_set_name') && $connect->character_set_name() !== 'utf8mb4') {
                if (!$connect->set_charset('utf8mb4')) {
                    error_log('Failed to enforce utf8mb4 charset on mysqli connection: ' . $connect->error);
                }
            }

            if (!$connect->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'")) {
                error_log('Failed to execute SET NAMES utf8mb4 for card_number table: ' . $connect->error);
            }

            $createQuery = "CREATE TABLE IF NOT EXISTS card_number (" .
                "cardnumber varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci PRIMARY KEY," .
                "namecard varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            if (!$connect->query($createQuery)) {
                error_log('Failed to create card_number table with utf8mb4 charset: ' . $connect->error);
            }

            ensureTableUtf8mb4('card_number');

            $columnInfo = $connect->query("SHOW FULL COLUMNS FROM card_number WHERE Field IN ('cardnumber', 'namecard')");
            if ($columnInfo instanceof mysqli_result) {
                while ($column = $columnInfo->fetch_assoc()) {
                    $collation = $column['Collation'] ?? '';
                    if (!is_string($collation) || stripos($collation, 'utf8mb4') === false) {
                        $field = $column['Field'];
                        $type = $field === 'cardnumber' ? 'varchar(500)' : 'varchar(1000)';
                        $alter = sprintf(
                            "ALTER TABLE card_number MODIFY %s %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci%s",
                            $field,
                            $type,
                            $field === 'cardnumber' ? ' PRIMARY KEY' : ' NOT NULL'
                        );
                        if (!$connect->query($alter)) {
                            error_log('Failed to update card_number column collation: ' . $connect->error);
                        }
                    }
                }
                $columnInfo->free();
            } else {
                error_log('Unable to inspect card_number column collations: ' . $connect->error);
            }
        } catch (\Throwable $e) {
            error_log('Unexpected error while ensuring card_number utf8mb4 compatibility: ' . $e->getMessage());
        }
    }
}


if (!function_exists('determineColumnTypeFromValue')) {
    function determineColumnTypeFromValue($value)
    {
        if (is_bool($value)) {
            return 'TINYINT(1)';
        }

        if (is_int($value)) {
            return 'INT(11)';
        }

        if (is_float($value)) {
            return 'DOUBLE';
        }

        if ($value === null) {
            return 'VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        }

        if (is_string($value)) {
            if (function_exists('mb_strlen')) {
                $length = mb_strlen($value, 'UTF-8');
            } else {
                $length = strlen($value);
            }

            if ($length <= 191) {
                return 'VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            }

            if ($length <= 500) {
                return 'VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            }

            return 'TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        }

        return 'TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    }
}


if (!function_exists('ensureColumnExistsForUpdate')) {
    function ensureColumnExistsForUpdate($tableName, $fieldName, $valueSample = null)
    {
        global $pdo;

        static $checkedColumns = [];

        $cacheKey = $tableName . '.' . $fieldName;
        if (isset($checkedColumns[$cacheKey])) {
            return;
        }

        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
            $stmt->execute([$tableName, $fieldName]);
            if ((int) $stmt->fetchColumn() > 0) {
                $checkedColumns[$cacheKey] = true;
                return;
            }

            $datatype = determineColumnTypeFromValue($valueSample);

            $defaultValue = null;
            if (is_bool($valueSample)) {
                $defaultValue = $valueSample ? '1' : '0';
            } elseif (is_scalar($valueSample) && $valueSample !== null) {
                $defaultValue = (string) $valueSample;
            }

            addFieldToTable($tableName, $fieldName, $defaultValue, $datatype);
            $checkedColumns[$cacheKey] = true;
        } catch (PDOException $e) {
            error_log('Failed to ensure column exists: ' . $e->getMessage());
            $checkedColumns[$cacheKey] = true;
        }
    }
}


if (!function_exists('addFieldToTable')) {
    function addFieldToTable($tableName, $fieldName, $defaultValue = null, $datatype = "VARCHAR(500)")
    {
        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName"
        );
        $stmt->bindParam(':tableName', $tableName);
        $stmt->execute();
        $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tableExists['count'] == 0)
            return;
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$pdo->query("SELECT DATABASE()")->fetchColumn(), $tableName, $fieldName]);
        $filedExists = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($filedExists['count'] != 0)
            return;
        $query = "ALTER TABLE $tableName ADD $fieldName $datatype";
        $statement = $pdo->prepare($query);
        $statement->execute();
        if ($defaultValue != null) {
            $stmt = $pdo->prepare("UPDATE $tableName SET $fieldName= ?");
            $stmt->bindParam(1, $defaultValue);
            $stmt->execute();
        }
        echo "The $fieldName field was added ✅";
    }
}
