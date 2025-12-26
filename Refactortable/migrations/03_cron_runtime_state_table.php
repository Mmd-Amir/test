<?php
rf_set_module('migrations/03_cron_runtime_state_table.php');

try {

    $tableName = 'cron_runtime_state';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
            job_key VARCHAR(255) PRIMARY KEY,
            last_run BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            unit VARCHAR(20) NOT NULL DEFAULT 'minute',
            value INT(10) UNSIGNED NOT NULL DEFAULT 1,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
    } else {
        addFieldToTable($tableName, 'unit', 'minute', "VARCHAR(20)");
        addFieldToTable($tableName, 'value', '1', "INT(10) UNSIGNED");
        addFieldToTable($tableName, 'enabled', '1', "TINYINT(1)");
    }
    if (function_exists('getCronJobDefinitions')) {
        $definitions = getCronJobDefinitions();
        $insertStmt = $pdo->prepare("INSERT INTO cron_runtime_state (job_key, unit, value, enabled) VALUES (:job_key, :unit, :value, :enabled) ON DUPLICATE KEY UPDATE unit = VALUES(unit), value = VALUES(value), enabled = VALUES(enabled)");
        foreach ($definitions as $key => $definition) {
            $default = $definition['default'] ?? ['unit' => 'minute', 'value' => 1];
            $unit = strtolower($default['unit'] ?? 'minute');
            $value = max(1, intval($default['value'] ?? 1));
            $enabled = $unit === 'disabled' ? 0 : 1;
            if ($enabled === 0) {
                $unit = 'disabled';
                $value = 1;
            }
            $insertStmt->bindValue(':job_key', $key, PDO::PARAM_STR);
            $insertStmt->bindValue(':unit', $unit, PDO::PARAM_STR);
            $insertStmt->bindValue(':value', $value, PDO::PARAM_INT);
            $insertStmt->bindValue(':enabled', $enabled, PDO::PARAM_INT);
            $insertStmt->execute();
        }
    }
} catch (PDOException $e) {
    rf_log_exception($e);
}
