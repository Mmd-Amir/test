<?php
rf_set_module('migrations/08_invoice_table.php');

try {

    $result = $connect->query("SHOW TABLES LIKE 'invoice'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE invoice (
        id_invoice varchar(200) PRIMARY KEY,
        id_user varchar(200) NULL,
        username varchar(300) NULL,
        Service_location varchar(300) NULL,
        time_sell VARCHAR(200) NULL,
        name_product varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        price_product varchar(200) NULL,
        Volume varchar(200) NULL,
        Service_time varchar(200) NULL,
        uuid TEXT NULL,
        note varchar(500) NULL,
        user_info TEXT NULL,
        bottype varchar(200) NULL,
        refral varchar(100) NULL,
        time_cron varchar(100) NULL,
        notifctions TEXT NOT NULL,
        Status varchar(200) NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$result) {
            echo "table invoice" . mysqli_error($connect);
        }
    } else {
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'note'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE invoice ADD note VARCHAR(700)");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'notifctions'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $data = json_encode(array(
                'volume' => false,
                'time' => false,
            ));
            $result = $connect->query("ALTER TABLE invoice ADD notifctions TEXT NOT NULL");
            $connect->query("UPDATE invoice SET notifctions = '$data'");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'time_cron'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE invoice ADD time_cron VARCHAR(100)");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'refral'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE invoice ADD refral VARCHAR(100)");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'bottype'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE invoice ADD bottype VARCHAR(200)");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'user_info'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE invoice ADD user_info TEXT");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'time_sell'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE invoice ADD time_sell VARCHAR(200)");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'uuid'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE invoice ADD uuid TEXT");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'Status'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE invoice ADD Status VARCHAR(100)");
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
