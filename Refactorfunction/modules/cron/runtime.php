<?php

if (function_exists('rf_set_module')) { rf_set_module('modules/cron/runtime.php'); }

if (!function_exists('normalizeCronScheduleConfig')) {
    function normalizeCronScheduleConfig(array $config, array $default): array
    {
        $unit = isset($config['unit']) ? strtolower((string) $config['unit']) : $default['unit'];
        $validUnits = ['minute', 'hour', 'day', 'disabled'];
        if (!in_array($unit, $validUnits, true)) {
            $unit = $default['unit'];
        }

        $value = isset($config['value']) ? (int) $config['value'] : $default['value'];
        if ($unit === 'disabled') {
            $value = 0;
        } elseif ($value < 1) {
            $value = $default['value'];
        }

        return [
            'unit' => $unit,
            'value' => $value,
        ];
    }
}


if (!function_exists('ensureCronRuntimeStateTable')) {
    function ensureCronRuntimeStateTable(PDO $pdo): void
    {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS cron_runtime_state (
                job_key VARCHAR(255) PRIMARY KEY,
                last_run BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
                unit VARCHAR(20) NOT NULL DEFAULT 'minute',
                value INT(10) UNSIGNED NOT NULL DEFAULT 1,
                enabled TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            error_log('ensureCronRuntimeStateTable: ' . $e->getMessage());
        }
    }
}


if (!function_exists('loadCronRuntimeState')) {
    function loadCronRuntimeState(PDO $pdo): array
    {
        $state = [];
        try {
            $stmt = $pdo->query("SELECT job_key, last_run FROM cron_runtime_state");
            if ($stmt !== false) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $jobKey = isset($row['job_key']) ? trim((string) $row['job_key']) : '';
                    if ($jobKey === '') {
                        continue;
                    }
                    $state[$jobKey] = isset($row['last_run']) ? (int) $row['last_run'] : 0;
                }
            }
        } catch (PDOException $e) {
            error_log('loadCronRuntimeState: ' . $e->getMessage());
        }

        return $state;
    }
}


if (!function_exists('setCronJobLastRun')) {
    function setCronJobLastRun(PDO $pdo, string $jobKey, int $timestamp): void
    {
        $jobKey = trim($jobKey);
        if ($jobKey === '') {
            return;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO cron_runtime_state (job_key, last_run) VALUES (:job_key, :last_run) ON DUPLICATE KEY UPDATE last_run = VALUES(last_run)");
            $stmt->bindValue(':job_key', $jobKey, PDO::PARAM_STR);
            $stmt->bindValue(':last_run', $timestamp, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('setCronJobLastRun: ' . $e->getMessage());
        }
    }
}


if (!function_exists('loadCronSchedules')) {
    function loadCronSchedules(): array
    {
        $definitions = getCronJobDefinitions();
        $schedules = getDefaultCronSchedules();
        $pdo = getDatabaseConnection();
        if (!($pdo instanceof PDO)) {
            return $schedules;
        }

        ensureCronRuntimeStateTable($pdo);

        try {
            $stmt = $pdo->query("SELECT job_key, unit, value, enabled FROM cron_runtime_state");
            if ($stmt === false) {
                return $schedules;
            }

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $jobKey = isset($row['job_key']) ? trim((string) $row['job_key']) : '';
                if ($jobKey === '' || !isset($definitions[$jobKey])) {
                    continue;
                }

                $unit = $row['unit'] ?? $definitions[$jobKey]['default']['unit'] ?? 'minute';
                $value = isset($row['value']) ? (int) $row['value'] : ($definitions[$jobKey]['default']['value'] ?? 1);
                $enabled = isset($row['enabled']) && (int) $row['enabled'] === 0 ? false : true;
                $scheduleConfig = ['unit' => $unit, 'value' => $value];
                if (!$enabled) {
                    $scheduleConfig['unit'] = 'disabled';
                    $scheduleConfig['value'] = 1;
                }

                $schedules[$jobKey] = normalizeCronScheduleConfig($scheduleConfig, $definitions[$jobKey]['default']);
            }
        } catch (PDOException $e) {
            error_log('loadCronSchedules: ' . $e->getMessage());
        }

        return $schedules;
    }
}


if (!function_exists('updateCronSchedule')) {
    function updateCronSchedule(string $jobKey, array $config): bool
    {
        $definitions = getCronJobDefinitions();
        if (!isset($definitions[$jobKey])) {
            return false;
        }

        $normalized = normalizeCronScheduleConfig($config, $definitions[$jobKey]['default']);
        $pdo = getDatabaseConnection();
        if (!($pdo instanceof PDO)) {
            return false;
        }

        ensureCronRuntimeStateTable($pdo);
        $enabled = $normalized['unit'] === 'disabled' ? 0 : 1;

        try {
            $stmt = $pdo->prepare("INSERT INTO cron_runtime_state (job_key, unit, value, enabled) VALUES (:job_key, :unit, :value, :enabled) ON DUPLICATE KEY UPDATE unit = VALUES(unit), value = VALUES(value), enabled = VALUES(enabled)");
            $stmt->bindValue(':job_key', $jobKey, PDO::PARAM_STR);
            $stmt->bindValue(':unit', $normalized['unit'], PDO::PARAM_STR);
            $stmt->bindValue(':value', $normalized['value'], PDO::PARAM_INT);
            $stmt->bindValue(':enabled', $enabled, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('updateCronSchedule: ' . $e->getMessage());
            return false;
        }
    }
}


if (!function_exists('describeCronSchedule')) {
    function describeCronSchedule(array $config): string
    {
        $unitLabels = [
            'minute' => 'دقیقه',
            'hour' => 'ساعت',
            'day' => 'روز',
            'disabled' => 'غیرفعال',
        ];

        $unit = $config['unit'] ?? 'minute';
        $value = isset($config['value']) ? (int) $config['value'] : 1;
        if ($value < 1) {
            $value = 1;
        }

        if ($unit === 'disabled') {
            return $unitLabels['disabled'];
        }

        $unitLabel = $unitLabels[$unit] ?? $unitLabels['minute'];
        return sprintf('هر %d %s', $value, $unitLabel);
    }
}


if (!function_exists('shouldRunCronJob')) {
    function shouldRunCronJob(array $config, int $minute, int $hour, int $dayOfYear): bool
    {
        $unit = $config['unit'] ?? 'minute';
        $value = isset($config['value']) ? (int) $config['value'] : 1;
        if ($value < 1) {
            $value = 1;
        }

        if ($unit === 'disabled') {
            return false;
        }

        switch ($unit) {
            case 'minute':
                return $minute % $value === 0;
            case 'hour':
                return $minute === 0 && ($hour % $value === 0);
            case 'day':
                return $minute === 0 && $hour === 0 && ($dayOfYear % $value === 0);
            default:
                return false;
        }
    }
}


if (!function_exists('buildCronInstructionDetails')) {
    function buildCronInstructionDetails(string $domainHost): string
    {
        $definitions = getCronJobDefinitions();
        $schedules = loadCronSchedules();
        $parts = [];

        foreach ($definitions as $key => $definition) {
            if (!isset($definition['instruction'], $definition['script'])) {
                continue;
            }
            $schedule = $schedules[$key] ?? $definition['default'];
            $description = describeCronSchedule($schedule);
            $title = sprintf($definition['instruction'], $description);
            $endpoint = buildCronScriptUrlByHost($domainHost, $definition['script']);
            $parts[] = "<b>{$title}</b>\n<code>curl " . htmlspecialchars($endpoint, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</code>";
        }

        return implode("\n\n", $parts);
    }
}


if (!function_exists('addCronIfNotExists')) {
    function addCronIfNotExists($cronCommand)
    {
        $commands = is_array($cronCommand) ? $cronCommand : [$cronCommand];
        $commands = array_values(array_filter(array_map('trim', $commands), static function ($command) {
            return $command !== '';
        }));

        if (empty($commands)) {
            return true;
        }

        $logContext = implode('; ', $commands);

        if (!isShellExecAvailable()) {
            error_log('shell_exec is not available; unable to register cron job(s): ' . $logContext);
            return false;
        }

        $crontabBinary = getCrontabBinary();
        if ($crontabBinary === null) {
            error_log('crontab executable not found; unable to register cron job(s): ' . $logContext);
            return false;
        }

        $existingCronJobs = runShellCommand(sprintf('%s -l 2>/dev/null', escapeshellarg($crontabBinary)));
        $existingCronJobs = trim((string) $existingCronJobs);
        $cronLines = $existingCronJobs === '' ? [] : preg_split('/\r?\n/', $existingCronJobs);
        $cronLines = array_values(array_filter(array_map('trim', $cronLines), static function ($line) {
            return $line !== '' && strpos($line, '#') !== 0;
        }));

        $newLineAdded = false;
        foreach ($commands as $command) {
            if (!in_array($command, $cronLines, true)) {
                $cronLines[] = $command;
                $newLineAdded = true;
            }
        }

        if (!$newLineAdded) {
            return true;
        }

        $cronLines = array_values(array_unique($cronLines));
        $cronContent = implode(PHP_EOL, $cronLines) . PHP_EOL;

        $temporaryFile = tempnam(sys_get_temp_dir(), 'cron');
        if ($temporaryFile === false) {
            error_log('Unable to create temporary file for cron job registration.');
            return false;
        }

        if (file_put_contents($temporaryFile, $cronContent) === false) {
            error_log('Unable to write cron configuration to temporary file: ' . $temporaryFile);
            unlink($temporaryFile);
            return false;
        }

        runShellCommand(sprintf('%s %s', escapeshellarg($crontabBinary), escapeshellarg($temporaryFile)));
        unlink($temporaryFile);

        return true;
    }
}


if (!function_exists('activecron')) {
    function activecron()
    {
        global $domainhosts;

        $host = null;
        if (isset($domainhosts) && is_string($domainhosts) && trim($domainhosts) !== '') {
            $host = $domainhosts;
        }

        if ($host === null || trim((string) $host) === '') {
            $host = $_SERVER['HTTP_HOST'] ?? null;
        }

        if ($host === null || trim((string) $host) === '') {
            $host = 'localhost';
        }

        $normalizedHost = preg_match('~^https?://~i', $host)
            ? rtrim($host, '/')
            : 'https://' . trim($host, '/');

        $cronEndpoint = $normalizedHost . '/cron/cron.php';

        $cronCommands = ["*/1 * * * * curl {$cronEndpoint}"];

        addCronIfNotExists($cronCommands);
    }
}
