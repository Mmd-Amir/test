<?php
rf_set_module('migrations/10_discount_table_and_columns.php');

try {

    $result = $connect->query("SHOW TABLES LIKE 'Discount'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE Discount (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code varchar(2000) NULL,
        price varchar(200) NULL,
        limituse varchar(200) NULL,
        limitused varchar(200) NULL)
        ");
        if (!$result) {
            echo "table Discount" . mysqli_error($connect);
        }
    } else {
        $Check_filde = $connect->query("SHOW COLUMNS FROM Discount LIKE 'limituse'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE Discount ADD limituse VARCHAR(200)");
            echo "The limituse field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM Discount LIKE 'limitused'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE Discount ADD limitused VARCHAR(200)");
            echo "The limitused field was added ✅";
        }
    }
} catch (Exception $e) {
    rf_log_exception($e);
}
