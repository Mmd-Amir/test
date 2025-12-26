<?php
rf_set_module('migrations/01_user_table.php');

try {

    $tableName = 'user';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
            id VARCHAR(500) PRIMARY KEY,
            limit_usertest INT(100) NOT NULL,
            roll_Status BOOL NOT NULL,
            username VARCHAR(500) NOT NULL,
            Processing_value TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            Processing_value_one TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            Processing_value_tow TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            Processing_value_four TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            step VARCHAR(500) NOT NULL,
            description_blocking TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
            number VARCHAR(300) NOT NULL,
            Balance INT(255) NOT NULL,
            User_Status VARCHAR(500) NOT NULL,
            pagenumber INT(10) NOT NULL,
            message_count VARCHAR(100) NOT NULL,
            last_message_time VARCHAR(100) NOT NULL,
            agent VARCHAR(100) NOT NULL,
            affiliatescount VARCHAR(100) NOT NULL,
            affiliates VARCHAR(100) NOT NULL,
            namecustom VARCHAR(300) NOT NULL,
            number_username VARCHAR(300) NOT NULL,
            register VARCHAR(100) NOT NULL,
            verify VARCHAR(100) NOT NULL,
            cardpayment VARCHAR(100) NOT NULL,
            codeInvitation VARCHAR(100) NULL,
            pricediscount VARCHAR(100) NULL   DEFAULT '0',
            hide_mini_app_instruction VARCHAR(20) NULL   DEFAULT '0',
            maxbuyagent VARCHAR(100) NULL   DEFAULT '0',
            joinchannel VARCHAR(100) NULL   DEFAULT '0',
            checkstatus VARCHAR(50) NULL   DEFAULT '0',
            bottype TEXT NULL ,
            score INT(255) NULL DEFAULT '0',
            limitchangeloc VARCHAR(50) NULL   DEFAULT '0',
            status_cron VARCHAR(20)  NULL DEFAULT '1',
            expire VARCHAR(100) NULL ,
            token VARCHAR(100) NULL 
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
    } else {
        addFieldToTable($tableName, 'token', null, "VARCHAR(100)");
        addFieldToTable($tableName, 'status_cron', "1", "VARCHAR(20)");
        addFieldToTable($tableName, 'expire', NULL, "VARCHAR(100)");
        addFieldToTable($tableName, 'limitchangeloc', '0', "VARCHAR(50)");
        addFieldToTable($tableName, 'bottype', '0', "TEXT");
        addFieldToTable($tableName, 'score', '0', "INT(255)");
        addFieldToTable($tableName, 'checkstatus', '0', "VARCHAR(50)");
        addFieldToTable($tableName, 'joinchannel', '0', "VARCHAR(100)");
        addFieldToTable($tableName, 'maxbuyagent', '0');
        addFieldToTable($tableName, 'agent', 'f');
        addFieldToTable($tableName, 'verify', '1');
        addFieldToTable($tableName, 'register', 'none');
        addFieldToTable($tableName, 'namecustom', 'none');
        addFieldToTable($tableName, 'number_username', '100');
        addFieldToTable($tableName, 'cardpayment', '1');
        addFieldToTable($tableName, 'affiliatescount', '0');
        addFieldToTable($tableName, 'affiliates', '0');
        addFieldToTable($tableName, 'message_count', '0');
        addFieldToTable($tableName, 'last_message_time', '0');
        addFieldToTable($tableName, 'Processing_value_four', '');
        addFieldToTable($tableName, 'username', 'none');
        addFieldToTable($tableName, 'Processing_value', 'none');
        addFieldToTable($tableName, 'number', 'none');
        addFieldToTable($tableName, 'pagenumber', '');
        addFieldToTable($tableName, 'codeInvitation', null);
        addFieldToTable($tableName, 'pricediscount', "0");
        addFieldToTable($tableName, 'hide_mini_app_instruction', '0', "VARCHAR(20)");
    }
} catch (PDOException $e) {
    rf_log_exception($e);
}

