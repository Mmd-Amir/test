<?php
rf_set_module('migrations/06_channels_and_marzban_panel_tables.php');

try {
    $tableName = 'channels';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
            remark varchar(200) NOT NULL,
            linkjoin varchar(200) NOT NULL,
            link varchar(200) NOT NULL)");
        $stmt->execute();
    } else {
        addFieldToTable("channels", "remark", null, "VARCHAR(200)");
        addFieldToTable("channels", "linkjoin", null, "VARCHAR(200)");
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
//--------------------------------------------------------------
try {

    $result = $connect->query("SHOW TABLES LIKE 'marzban_panel'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE marzban_panel (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_panel varchar(200) NULL,
        name_panel varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        status varchar(500) NULL,
        url_panel varchar(2000) NULL,
        username_panel varchar(200) NULL,
        password_panel varchar(200) NULL,
        agent varchar(200) NULL,
        sublink varchar(500) NULL,
        config varchar(500) NULL,
        MethodUsername varchar(700) NULL,
        TestAccount varchar(100) NULL,
        limit_panel varchar(100) NULL,
        namecustom varchar(100) NULL,
        Methodextend varchar(100) NULL,
        conecton varchar(100) NULL,
        linksubx varchar(1000) NULL,
        inboundid varchar(100) NULL,
        type varchar(100) NULL,
        inboundstatus varchar(100) NULL,
        hosts  JSON  NULL,
        inbound_deactive varchar(100) NULL,
        time_usertest varchar(100) NULL,
        val_usertest varchar(100)  NULL,
        secret_code varchar(200) NULL,
        priceChangeloc varchar(200) NULL,
        priceextravolume varchar(500) NULL,
        pricecustomvolume varchar(500) NULL,
        pricecustomtime varchar(500) NULL,
        priceextratime varchar(500) NULL,
        mainvolume varchar(500) NULL,
        maxvolume varchar(500) NULL,
        maintime varchar(500) NULL,
        maxtime varchar(500) NULL,
        status_extend varchar(100) NULL,
        datelogin TEXT NULL,
        proxies TEXT NULL,
        inbounds TEXT NULL,
        subvip varchar(60) NULL,
        changeloc varchar(60) NULL,
        on_hold_test varchar(60) NOT NULL,
        customvolume TEXT NULL,
        hide_user TEXT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table marzban_panel" . mysqli_error($connect);
        }
    } else {
        $VALUE = json_encode(array(
            'f' => '0',
            'n' => '0',
            'n2' => '0'
        ));
        $valueprice = json_encode(array(
            'f' => "4000",
            'n' => "4000",
            'n2' => "4000"
        ));
        $valuemain = json_encode(array(
            'f' => "1",
            'n' => "1",
            'n2' => "1"
        ));
        $valuemax = json_encode(array(
            'f' => "1000",
            'n' => "1000",
            'n2' => "1000"
        ));
        $valuemax_time = json_encode(array(
            'f' => "365",
            'n' => "365",
            'n2' => "365"
        ));
        addFieldToTable("marzban_panel", "on_hold_test", "1", "VARCHAR(60)");
        addFieldToTable("marzban_panel", "proxies", null, "TEXT");
        addFieldToTable("marzban_panel", "inbounds", null, "TEXT");
        addFieldToTable("marzban_panel", "customvolume", $VALUE, "TEXT");
        addFieldToTable("marzban_panel", "subvip", "offsubvip", "VARCHAR(60)");
        addFieldToTable("marzban_panel", "changeloc", "offchangeloc", "VARCHAR(60)");
        addFieldToTable("marzban_panel", "hide_user", null, "TEXT");
        addFieldToTable("marzban_panel", "status_extend", "on_extend", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "code_panel", null, "VARCHAR(50)");
        addFieldToTable("marzban_panel", "priceextravolume", $valueprice, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "pricecustomvolume", $valueprice, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "pricecustomtime", $valueprice, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "priceextratime", $valueprice, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "priceChangeloc", "0", "VARCHAR(100)");
        addFieldToTable("marzban_panel", "mainvolume", $valuemain, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "maxvolume", $valuemax, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "maintime", $valuemain, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "maxtime", $valuemax_time, "VARCHAR(500)");
        addFieldToTable("marzban_panel", "MethodUsername", "آیدی عددی + حروف و عدد رندوم", "VARCHAR(100)");
        addFieldToTable("marzban_panel", "datelogin", null, "TEXT");
        addFieldToTable("marzban_panel", "val_usertest", "100", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "time_usertest", "1", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "secret_code", null, "VARCHAR(200)");
        addFieldToTable("marzban_panel", "inboundstatus", "offinbounddisable", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "inbound_deactive", "0", "VARCHAR(100)");
        addFieldToTable("marzban_panel", "agent", "all", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "inboundid", "1", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "linksubx", null, "VARCHAR(200)");
        addFieldToTable("marzban_panel", "conecton", "offconecton", "VARCHAR(100)");
        addFieldToTable("marzban_panel", "type", "marzban", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "Methodextend", "ریست حجم و زمان", "VARCHAR(100)");
        addFieldToTable("marzban_panel", "namecustom", "vpn", "VARCHAR(100)");
        addFieldToTable("marzban_panel", "limit_panel", "unlimted", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "TestAccount", "ONTestAccount", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "status", "active", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "sublink", "onsublink", "VARCHAR(50)");
        addFieldToTable("marzban_panel", "config", "offconfig", "VARCHAR(50)");
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
