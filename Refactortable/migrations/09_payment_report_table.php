<?php
rf_set_module('migrations/09_payment_report_table.php');

try {

    $result = $connect->query("SHOW TABLES LIKE 'Payment_report'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE Payment_report (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        id_order varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        time varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        at_updated varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        price varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        dec_not_confirmed TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        Payment_Method varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        payment_Status varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        bottype varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        message_id INT NULL,
        id_invoice varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (!$result) {
            echo "table Payment_report" . mysqli_error($connect);
        }
    } else {
        ensureTableUtf8mb4('Payment_report');
        addFieldToTable("Payment_report", "message_id", null, "INT");
        $Check_filde = $connect->query("SHOW COLUMNS FROM Payment_report LIKE 'Payment_Method'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE Payment_report ADD Payment_Method VARCHAR(200)");
            echo "The Payment_Method field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM Payment_report LIKE 'bottype'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE Payment_report ADD bottype VARCHAR(300)");
            echo "The bottype field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM Payment_report LIKE 'at_updated'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE Payment_report ADD at_updated VARCHAR(200)");
            echo "The at_updated field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM Payment_report LIKE 'id_invoice'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE Payment_report ADD id_invoice VARCHAR(400)");
            $connect->query("UPDATE Payment_report SET id_invoice = 'none'");
            echo "The id_invoice field was added ✅";
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
