<?php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/includes/data_user/Manualsale.php');
}

    $stmt = $pdo->prepare("SELECT * FROM manualsell WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $configman = $stmt->fetch(PDO::FETCH_ASSOC);
    $service = select("invoice", "*", "username", $username, "select");
    $Output = array(
        'status' => $service['Status'],
        'username' => $service['username'],
        'data_limit' => null,
        'expire' => $service['time_sell'],
        'online_at' => null,
        'used_traffic' => null,
        'links' => [],
        'subscription_url' => $configman['contentrecord'],
        'sub_updated_at' => null,
        'sub_last_user_agent' => null,
        'uuid' => null
    );


return $Output;
