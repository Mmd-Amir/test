<?php
rf_set_module('migrations/14_departman_support_and_misc_tables.php');

try {

    $tableName = 'departman';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idsupport VARCHAR(200) NOT NULL,
            name_departman VARCHAR(600) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
        $connect->query("INSERT INTO departman (idsupport,name_departman) VALUES ('$adminnumber','☎️ بخش عمومی')");
    }
} catch (PDOException $e) {
    rf_log_exception($e);
}
try {

    $tableName = 'support_message';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            Tracking VARCHAR(100) NOT NULL,
            idsupport VARCHAR(100) NOT NULL,
            iduser VARCHAR(100) NOT NULL,
            name_departman VARCHAR(600) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            result TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            time VARCHAR(200) NOT NULL,
            status ENUM('Answered','Pending','Unseen','Customerresponse','close') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
    } else {
        addFieldToTable("support_message", "result", "0", "TEXT");
    }
} catch (PDOException $e) {
    rf_log_exception($e);
}
try {
    $result = $connect->query("SHOW TABLES LIKE 'wheel_list'");
    $table_exists = ($result->num_rows > 0);
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE wheel_list (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user  varchar(200)  NOT NULL,
        time  varchar(200)  NOT NULL,
        first_name  varchar(200)  NOT NULL,
        wheel_code  varchar(200)  NOT NULL,
        price  varchar(200)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table wheel_list" . mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
try {
    $result = $connect->query("SHOW TABLES LIKE 'botsaz'");
    $table_exists = ($result->num_rows > 0);
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE botsaz (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user  varchar(200)  NOT NULL,
        bot_token  varchar(200)  NOT NULL,
        admin_ids  TEXT  NOT NULL,
        username  varchar(200)  NOT NULL,
        setting  TEXT  NULL,
        hide_panel  JSON  NOT NULL,
        time  varchar(200)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table botsaz" . mysqli_error($connect);
        }
    } else {
        addFieldToTable("botsaz", "hide_panel", "{}", "JSON");
    }
} catch (Exception $e) {
    rf_log_exception($e);
}

try {
    $result = $connect->query("SHOW TABLES LIKE 'app'");
    $table_exists = ($result->num_rows > 0);
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE app (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name  varchar(200)   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        link  varchar(200)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table app" . mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}



try {
    $result = $connect->query("SHOW TABLES LIKE 'logs_api'");
    $table_exists = ($result->num_rows > 0);
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE logs_api (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        header JSON  NULL,
        data JSON  NULL,
        ip  varchar(200)  NOT NULL,
        time  varchar(200)  NOT NULL,
        actions  varchar(200)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table logs_api" . mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
//----------------------- [ Category ] --------------------- //
try {
    $result = $connect->query("SHOW TABLES LIKE 'category'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE category (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        remark varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table category" . mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
try {
    $result = $connect->query("SHOW TABLES LIKE 'reagent_report'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE reagent_report (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNIQUE  NOT NULL,
        get_gift BOOL   NOT NULL,
        time varchar(50)  NOT NULL,
        reagent varchar(30)  NOT NULL
        )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table affiliates" . mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}



$balancemain = json_decode(select("PaySetting", "ValuePay", "NamePay", "maxbalance", "select")['ValuePay'], true);
if (!isset($balancemain['f'])) {
    $value = json_encode(array(
        "f" => "1000000",
        "n" => "1000000",
        "n2" => "1000000",
    ));
    $valuemain = json_encode(array(
        "f" => "20000",
        "n" => "20000",
        "n2" => "20000",
    ));
    update("PaySetting", "ValuePay", $value, "NamePay", "maxbalance");
    update("PaySetting", "ValuePay", $valuemain, "NamePay", "minbalance");
}
$connect->query("ALTER TABLE `invoice` CHANGE `Volume` `Volume` VARCHAR(200)");
$connect->query("ALTER TABLE `invoice` CHANGE `price_product` `price_product` VARCHAR(200)");
$connect->query("ALTER TABLE `invoice` CHANGE `name_product` `name_product` VARCHAR(200)");
$connect->query("ALTER TABLE `invoice` CHANGE `username` `username` VARCHAR(200)");
$connect->query("ALTER TABLE `invoice` CHANGE `Service_location` `Service_location` VARCHAR(200)");
$connect->query("ALTER TABLE `invoice` CHANGE `time_sell` `time_sell` VARCHAR(200)");
$connect->query("ALTER TABLE marzban_panel MODIFY name_panel VARCHAR(255) COLLATE utf8mb4_bin");
$connect->query("ALTER TABLE product MODIFY name_product VARCHAR(255) COLLATE utf8mb4_bin");
$connect->query("ALTER TABLE help MODIFY name_os VARCHAR(500) COLLATE utf8mb4_bin");
telegram('setwebhook', [
    'url' => "https://$domainhosts/index.php"
]);
