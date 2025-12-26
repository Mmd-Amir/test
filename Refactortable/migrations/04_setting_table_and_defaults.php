<?php
rf_set_module('migrations/04_setting_table_and_defaults.php');

try {

    $tableName = 'setting';
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName");
    $stmt->bindParam(':tableName', $tableName);
    $stmt->execute();
    $DATAAWARD = json_encode(array(
        'one' => "0",
        "tow" => "0",
        "theree" => "0"
    ));
    $limitlist = json_encode(array(
        'free' => 100,
        'all' => 100,
    ));
    $status_cron = json_encode(array(
        'day' => true,
        'volume' => true,
        'remove' => false,
        'remove_volume' => false,
        'test' => false,
        'on_hold' => false,
        'uptime_node' => false,
        'uptime_panel' => false,
    ));
    $keyboardmain = '{"keyboard":[[{"text":"text_sell"},{"text":"text_extend"}],[{"text":"text_usertest"},{"text":"text_wheel_luck"}],[{"text":"text_Purchased_services"},{"text":"accountwallet"}],[{"text":"text_affiliates"},{"text":"text_Tariff_list"}],[{"text":"text_support"},{"text":"text_help"}]]}';
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tableExists) {
        $stmt = $pdo->prepare("CREATE TABLE $tableName (
        Bot_Status varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        roll_Status varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        get_number varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        iran_number varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        NotUser varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NULL,
        Channel_Report varchar(600)  NULL,
        limit_usertest_all varchar(600)  NULL,
        affiliatesstatus varchar(600)  NULL,
        affiliatespercentage varchar(600)  NULL,
        removedayc varchar(600)  NULL,
        showcard varchar(200)  NULL,
        numbercount varchar(600)  NULL,
        statusnewuser varchar(600)  NULL,
        statusagentrequest varchar(600)  NULL,
        statuscategory varchar(200)  NULL,
        statusterffh varchar(200)  NULL,
        volumewarn varchar(200)  NULL,
        inlinebtnmain varchar(200)  NULL,
        verifystart varchar(200)  NULL,
        id_support varchar(200)  NULL,
        statusnamecustom varchar(100)  NULL,
        statuscategorygenral varchar(100)  NULL,
        statussupportpv varchar(100)  NULL,
        agentreqprice varchar(100)  NULL,
        bulkbuy varchar(100)  NULL,
        on_hold_day varchar(100)  NULL,
        cronvolumere varchar(100)  NULL,
        verifybucodeuser varchar(100)  NULL,
        scorestatus varchar(100)  NULL,
        Lottery_prize TEXT  NULL,
        wheelـluck varchar(45)  NULL,
        wheelـluck_price varchar(45)  NULL,
        btn_status_extned varchar(45)  NULL,
        daywarn varchar(45)  NULL,
        categoryhelp varchar(45)  NULL,
        linkappstatus varchar(45)  NULL,
        iplogin varchar(45)  NULL,
        wheelagent varchar(45)  NULL,
        Lotteryagent varchar(45)  NULL,
        languageen varchar(45)  NULL,
        languageru varchar(45)  NULL,
        statusfirstwheel varchar(45)  NULL,
        statuslimitchangeloc varchar(45)  NULL,
        Debtsettlement varchar(45)  NULL,
        Dice varchar(45) NULL,
        keyboardmain TEXT NOT NULL,
        statusnoteforf varchar(45) NOT NULL,
        statuscopycart varchar(45) NOT NULL,
        timeauto_not_verify varchar(20) NOT NULL,
        status_keyboard_config varchar(20)  NULL,
        cron_status TEXT NOT NULL,
        limitnumber varchar(200)  NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
        $stmt = $pdo->prepare("INSERT INTO setting (Bot_Status,roll_Status,get_number,limit_usertest_all,iran_number,NotUser,affiliatesstatus,affiliatespercentage,removedayc,showcard,statuscategory,numbercount,statusnewuser,statusagentrequest,volumewarn,inlinebtnmain,verifystart,statussupportpv,statusnamecustom,statuscategorygenral,agentreqprice,cronvolumere,bulkbuy,on_hold_day,verifybucodeuser,scorestatus,Lottery_prize,wheelـluck,wheelـluck_price,iplogin,daywarn,categoryhelp,linkappstatus,languageen,languageru,wheelagent,Lotteryagent,statusfirstwheel,statuslimitchangeloc,limitnumber,Debtsettlement,Dice,keyboardmain,statusnoteforf,statuscopycart,timeauto_not_verify,status_keyboard_config,cron_status) VALUES ('botstatuson','rolleon','offAuthenticationphone','1','offAuthenticationiran','offnotuser','offaffiliates','0','0','1','offcategory','0','onnewuser','onrequestagent','2','offinline','offverify','offpvsupport','offnamecustom','offcategorys','0','5','onbulk','4','offverify','0','$DATAAWARD','0','0','0','2','0','0','0','0','1','1','0','0','$limitlist','1','0','$keyboardmain','1','0','4','1','$status_cron')");
        $stmt->execute();
    } else {
        addFieldToTable("setting", "cron_status", $status_cron, "TEXT");
        addFieldToTable("setting", "status_keyboard_config", "1", "varchar(20)");
        addFieldToTable("setting", "statusnoteforf", "1", "varchar(20)");
        addFieldToTable("setting", "timeauto_not_verify", "4", "varchar(20)");
        addFieldToTable("setting", "statuscopycart", "0", "varchar(20)");
        addFieldToTable("setting", "keyboardmain", $keyboardmain, "TEXT");
        addFieldToTable("setting", "Dice", '0', "varchar(45)");
        addFieldToTable("setting", "Debtsettlement", '1', "varchar(45)");
        addFieldToTable("setting", "limitnumber", $limitlist, "varchar(200)");
        addFieldToTable("setting", "statuslimitchangeloc", "0", "varchar(45)");
        addFieldToTable("setting", "statusfirstwheel", "0", "varchar(45)");
        addFieldToTable("setting", "Lotteryagent", "1", "varchar(45)");
        addFieldToTable("setting", "wheelagent", "1", "varchar(45)");
        addFieldToTable("setting", "languageru", "0", "varchar(45)");
        addFieldToTable("setting", "languageen", "0", "varchar(45)");
        addFieldToTable("setting", "linkappstatus", "0", "varchar(45)");
        addFieldToTable("setting", "categoryhelp", "0", "varchar(45)");
        addFieldToTable("setting", "daywarn", "2", "varchar(45)");
        addFieldToTable("setting", "btn_status_extned", "0", "varchar(45)");
        addFieldToTable("setting", "iplogin", "0", "varchar(45)");
        addFieldToTable("setting", "wheelـluck_price", "0", "varchar(45)");
        addFieldToTable("setting", "wheelـluck", "0", "varchar(45)");
        addFieldToTable("setting", "Lottery_prize", $DATAAWARD, "TEXT");
        addFieldToTable("setting", "scorestatus", "0", "VARCHAR(100)");
        addFieldToTable("setting", "verifybucodeuser", "offverify", "VARCHAR(100)");
        addFieldToTable("setting", "on_hold_day", "4", "VARCHAR(100)");
        addFieldToTable("setting", "bulkbuy", "onbulk", "VARCHAR(100)");
        addFieldToTable("setting", "statuscategorygenral", "offcategorys", "VARCHAR(100)");
        addFieldToTable("setting", "cronvolumere", "5", "VARCHAR(100)");
        addFieldToTable("setting", "agentreqprice", "0", "VARCHAR(100)");
        addFieldToTable("setting", "statusnamecustom", "offnamecustom", "VARCHAR(100)");
        addFieldToTable("setting", "id_support", "0", "VARCHAR(100)");
        addFieldToTable("setting", "statussupportpv", "offpvsupport", "VARCHAR(100)");
        addFieldToTable("setting", "affiliatespercentage", "0", "VARCHAR(600)");
        addFieldToTable("setting", "inlinebtnmain", "offinline", "VARCHAR(200)");
        addFieldToTable("setting", "volumewarn", "2", "VARCHAR(200)");
        addFieldToTable("setting", "statusagentrequest", "onrequestagent", "VARCHAR(600)");
        addFieldToTable("setting", "statusnewuser", "onnewuser", "VARCHAR(600)");
        addFieldToTable("setting", "numbercount", "0", "VARCHAR(600)");
        addFieldToTable("setting", "statuscategory", "offcategory", "VARCHAR(600)");
        addFieldToTable("setting", "showcard", "1", "VARCHAR(200)");
        addFieldToTable("setting", "removedayc", "1", "VARCHAR(200)");
        addFieldToTable("setting", "affiliatesstatus", "offaffiliates", "VARCHAR(600)");
        addFieldToTable("setting", "NotUser", "offnotuser", "VARCHAR(200)");
        addFieldToTable("setting", "iran_number", "offAuthenticationiran", "VARCHAR(200)");
        addFieldToTable("setting", "get_number", "onAuthenticationphone", "VARCHAR(200)");
        addFieldToTable("setting", "limit_usertest_all", "1", "VARCHAR(200)");
        addFieldToTable("setting", "Channel_Report", "0", "VARCHAR(200)");
        addFieldToTable("setting", "Bot_Status", "botstatuson", "VARCHAR(200)");
        addFieldToTable("setting", "roll_Status", "rolleon", "VARCHAR(200)");
        addFieldToTable("setting", "verifystart", "offverify", "VARCHAR(200)");
    }
} catch (Exception $e) {
    rf_log_exception($e);
}

