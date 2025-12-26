<?php
// Auto-refactored from legacy panels.php (DataUser split by panel type)
if (function_exists('rf_set_module')) {
    rf_set_module('panels/traits/data_user.php');
}

trait ManagePanelDataUserTrait
{
function DataUser($name_panel, $username)
{
    $Output = array();
    global $pdo, $domainhosts, $new_marzban;

    $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
    if (!$Get_Data_Panel || !is_array($Get_Data_Panel)) {
        return array(
            'status' => 'Unsuccessful',
            'msg' => 'Panel Not Found'
        );
    }

    if (isset($Get_Data_Panel['subvip']) && $Get_Data_Panel['subvip'] == "onsubvip") {
        $inoice = select("invoice", "*", "username", $username, "select");
    } else {
        $inoice = false;
    }

    $type = (string)($Get_Data_Panel['type'] ?? '');

    if ($type === 'marzban') {
        return require __DIR__ . '/../includes/data_user/marzban.php';
    } elseif ($type === 'marzneshin') {
        return require __DIR__ . '/../includes/data_user/marzneshin.php';
    } elseif ($type === 'x-ui_single') {
        return require __DIR__ . '/../includes/data_user/x-ui_single.php';
    } elseif ($type === 'hiddify') {
        return require __DIR__ . '/../includes/data_user/hiddify.php';
    } elseif ($type === 'Manualsale') {
        return require __DIR__ . '/../includes/data_user/Manualsale.php';
    } elseif ($type === 'alireza_single') {
        return require __DIR__ . '/../includes/data_user/alireza_single.php';
    } elseif ($type === 'WGDashboard') {
        return require __DIR__ . '/../includes/data_user/WGDashboard.php';
    } elseif ($type === 's_ui') {
        return require __DIR__ . '/../includes/data_user/s_ui.php';
    } elseif ($type === 'ibsng') {
        return require __DIR__ . '/../includes/data_user/ibsng.php';
    } elseif ($type === 'mikrotik') {
        return require __DIR__ . '/../includes/data_user/mikrotik.php';
    }

    return array(
        'status' => 'Unsuccessful',
        'msg' => 'Panel Not Found'
    );
}

}
