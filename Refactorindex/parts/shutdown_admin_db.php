<?php
rf_set_module('parts/shutdown_admin_db.php');
if (in_array($from_id, $admin_ids))
    require_once 'admin.php';

$pdo = null;
$connect->close();
