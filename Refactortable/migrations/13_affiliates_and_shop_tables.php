<?php
rf_set_module('migrations/13_affiliates_and_shop_tables.php');

try {
    $result = $connect->query("SHOW TABLES LIKE 'affiliates'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE affiliates (
        description TEXT  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        status_commission varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        Discount varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        price_Discount varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        porsant_one_buy varchar(100),
        id_media varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table affiliates" . mysqli_error($connect);
        }
        $connect->query("INSERT INTO affiliates (description,id_media,status_commission,Discount,porsant_one_buy) VALUES ('none','none','oncommission','onDiscountaffiliates','off_buy_porsant')");
    } else {
        $Check_filde = $connect->query("SHOW COLUMNS FROM affiliates LIKE 'porsant_one_buy'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE affiliates ADD porsant_one_buy VARCHAR(100)");
            $connect->query("UPDATE affiliates SET porsant_one_buy = 'off_buy_porsant'");
            echo "The Discount field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM affiliates LIKE 'Discount'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE affiliates ADD Discount VARCHAR(100)");
            $connect->query("UPDATE affiliates SET Discount = 'onDiscountaffiliates'");
            echo "The Discount field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM affiliates LIKE 'price_Discount'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE affiliates ADD price_Discount VARCHAR(100)");
            echo "The price_Discount field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM affiliates LIKE 'status_commission'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE affiliates ADD status_commission VARCHAR(100)");
            $connect->query("UPDATE affiliates SET status_commission = 'oncommission'");
            echo "The commission field was added ✅";
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
try {
    $result = $connect->query("SHOW TABLES LIKE 'shopSetting'");
    $table_exists = ($result->num_rows > 0);
    $agent_cashback = json_encode(array(
        'n' => 0,
        'n2' => 0
    ));
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE shopSetting (
        Namevalue varchar(500) PRIMARY KEY NOT NULL,
        value TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table shopSetting" . mysqli_error($connect);
        }
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customvolmef','4000')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customvolmen','4000')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customvolmen2','4000')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statusextra','offextra')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customtimepricef','4000')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customtimepricen','4000')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('customtimepricen2','4000')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statusdirectpabuy','ondirectbuy')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('minbalancebuybulk','0')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statustimeextra','ontimeextraa')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statusdisorder','offdisorder')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statuschangeservice','onstatus')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('statusshowprice','offshowprice')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('configshow','onconfig')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('backserviecstatus','on')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('chashbackextend','0')");
        $connect->query("INSERT INTO shopSetting (Namevalue,value) VALUES ('chashbackextend_agent','$agent_cashback')");
    } else {
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customvolmef','4000')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customvolmen','4000')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customvolmen2','4000')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statusextra','offextra')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statusdirectpabuy','ondirectbuy')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('minbalancebuybulk','0')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statustimeextra','ontimeextraa')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customtimepricef','4000')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customtimepricen','4000')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('customtimepricen2','4000')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statusdisorder','offdisorder')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statuschangeservice','onstatus')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('statusshowprice','offshowprice')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('configshow','onconfig')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('backserviecstatus','on')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('chashbackextend','0')");
        $connect->query("INSERT IGNORE INTO shopSetting (Namevalue,value) VALUES ('chashbackextend_agent','$agent_cashback')");



    }
} catch (Exception $e) {
    rf_log_exception($e);
}
//----------------------- [ remove requests ] --------------------- //
try {
    $result = $connect->query("SHOW TABLES LIKE 'cancel_service'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE cancel_service (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user varchar(500)  NOT NULL,
        username varchar(1000)  NOT NULL,
        description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL,
        status varchar(1000)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table cancel_service" . mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
try {
    $result = $connect->query("SHOW TABLES LIKE 'service_other'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE service_other (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user varchar(500)  NOT NULL,
        username varchar(1000)  NOT NULL,
        value varchar(1000)  NOT NULL,
        time varchar(200)  NOT NULL,
        price varchar(200)  NOT NULL,
        type varchar(1000)  NOT NULL,
        status varchar(200)  NOT NULL,
        output TEXT  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table service_other" . mysqli_error($connect);
        }
    } else {
        $Check_filde = $connect->query("SHOW COLUMNS FROM service_other LIKE 'price'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE service_other ADD price VARCHAR(200)");
            echo "The price field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM service_other LIKE 'status'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE service_other ADD status VARCHAR(200)");
            echo "The status field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM service_other LIKE 'output'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE service_other ADD output TEXT");
            echo "The output field was added ✅";
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
try {
    $result = $connect->query("SHOW TABLES LIKE 'card_number'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE card_number (
        cardnumber varchar(500) PRIMARY KEY,
        namecard  varchar(1000)  NOT NULL)
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table x_ui" . mysqli_error($connect);
        }
    }
    $columnInfo = $connect->query("SHOW FULL COLUMNS FROM card_number LIKE 'namecard'");
    if ($columnInfo) {
        $column = $columnInfo->fetch_assoc();
        $currentCollation = $column['Collation'] ?? '';
        if (empty($currentCollation) || stripos($currentCollation, 'utf8mb4') === false) {
            $connect->query("ALTER TABLE card_number CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $connect->query("ALTER TABLE card_number MODIFY cardnumber varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci PRIMARY KEY");
            $connect->query("ALTER TABLE card_number MODIFY namecard varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        }
        $columnInfo->free();
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
try {
    $result = $connect->query("SHOW TABLES LIKE 'Requestagent'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE Requestagent (
        id varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci PRIMARY KEY,
        username  varchar(500)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        time  varchar(500)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        Description  varchar(500)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        status  varchar(500)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        type  varchar(500)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (!$result) {
            echo "table Requestagent" . mysqli_error($connect);
        }
    } else {
        ensureTableUtf8mb4('Requestagent');
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
try {
    $result = $connect->query("SHOW TABLES LIKE 'topicid'");
    $table_exists = ($result->num_rows > 0);
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE topicid (
        report varchar(500) PRIMARY KEY NOT NULL,
        idreport TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table Requestagent" . mysqli_error($connect);
        }
        $connect->query("INSERT INTO topicid (idreport,report) VALUES ('0','buyreport')");
        $connect->query("INSERT INTO topicid (idreport,report) VALUES ('0','otherservice')");
        $connect->query("INSERT INTO topicid (idreport,report) VALUES ('0','paymentreport')");
        $connect->query("INSERT INTO topicid (idreport,report) VALUES ('0','otherreport')");
        $connect->query("INSERT INTO topicid (idreport,report) VALUES ('0','reporttest')");
        $connect->query("INSERT INTO topicid (idreport,report) VALUES ('0','errorreport')");
        $connect->query("INSERT INTO topicid (idreport,report) VALUES ('0','porsantreport')");
        $connect->query("INSERT INTO topicid (idreport,report) VALUES ('0','reportnight')");
        $connect->query("INSERT INTO topicid (idreport,report) VALUES ('0','reportcron')");
        $connect->query("INSERT INTO topicid (idreport,report) VALUES ('0','backupfile')");
    } else {
        $connect->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','buyreport')");
        $connect->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','otherservice')");
        $connect->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','paymentreport')");
        $connect->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','otherreport')");
        $connect->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','reporttest')");
        $connect->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','errorreport')");
        $connect->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','porsantreport')");
        $connect->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','reportnight')");
        $connect->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','reportcron')");
        $connect->query("INSERT IGNORE INTO topicid (idreport,report) VALUES ('0','backupfile')");




    }
} catch (Exception $e) {
    rf_log_exception($e);
}
try {
    $result = $connect->query("SHOW TABLES LIKE 'manualsell'");
    $table_exists = ($result->num_rows > 0);
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE manualsell (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        codepanel  varchar(100)  NOT NULL,
        codeproduct  varchar(100)  NOT NULL,
        namerecord  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL,
        username  varchar(500)  NULL,
        contentrecord  TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL,
        status  varchar(200)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table manualsell" . mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
