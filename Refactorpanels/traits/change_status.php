<?php
// Auto-refactored from legacy panels.php
if (function_exists('rf_set_module')) {
    rf_set_module('panels/traits/change_status.php');
}

trait ManagePanelChangeStatusTrait
{
function Change_status($username, $name_panel)
{
    $ManagePanel = new ManagePanel();
    $DataUserOut = $ManagePanel->DataUser($name_panel, $username);
    $Get_Data_Panel = select("marzban_panel", "*", "name_panel", $name_panel, "select");
    if ($DataUserOut['status'] == "Unsuccessful") {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => $DataUserOut['detail']
        );
        return;
    }
    if (!in_array($DataUserOut['status'], ["active", "disabled"])) {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => "status invalid"
        );
        return;
    }
    if ($Get_Data_Panel['type'] == "marzban") {
        if ($DataUserOut['status'] == "active") {
            $status = "disabled";
        } else {
            $status = "active";
        }
        $configs = array("status" => $status);
        $ManagePanel->Modifyuser($username, $name_panel, $configs);
        $Output = array(
            'status' => 'successful',
            'msg' => null
        );
    } elseif ($Get_Data_Panel['type'] == "marzneshin") {
        if ($DataUserOut['status'] == "active") {
            disableduser($name_panel, $username);
        } else {
            enableuser($name_panel, $username);
        }
        $Output = array(
            'status' => 'successful',
            'msg' => null
        );
    } elseif ($Get_Data_Panel['type'] == "x-ui_single") {
        if ($DataUserOut['status'] == "active") {
            $status = false;
        } else {
            $status = true;
        }
        $configs = array(
            'settings' => json_encode(array(
                'clients' => array(
                    array(
                        "enable" => $status,
                    )
                ),
            )),
        );
        $ManagePanel->Modifyuser($username, $name_panel, $configs);
        $Output = array(
            'status' => 'successful',
            'msg' => null
        );
    } elseif ($Get_Data_Panel['type'] == "alireza_single") {
        if ($DataUserOut['status'] == "active") {
            $status = false;
        } else {
            $status = true;
        }
        $configs = array(
            'settings' => json_encode(array(
                'clients' => array(
                    array(
                        "enable" => $status,
                    )
                ),
            )),
        );
        $ManagePanel->Modifyuser($username, $name_panel, $configs);
        $Output = array(
            'status' => 'successful',
            'msg' => null
        );
    } elseif ($Get_Data_Panel['type'] == "hiddify") {
        $Output = array(
            'status' => 'Unsuccessful',
            'msg' => null
        );
    } elseif ($Get_Data_Panel['type'] == "s_ui") {
        if ($DataUserOut['status'] == "active") {
            $status = false;
        } else {
            $status = true;
        }
        $configs = array("enable" => $status);
        $ManagePanel->Modifyuser($username, $name_panel, $configs);
        $Output = array(
            'status' => 'successful',
            'msg' => null
        );
    }

    return $Output;
}
}
