<?php
// Refactored ManagePanel class (split into traits)

if (function_exists('rf_set_module')) {
    rf_set_module('panels/ManagePanel.php');
}

require_once __DIR__ . '/traits/create_user.php';
require_once __DIR__ . '/traits/data_user.php';
require_once __DIR__ . '/traits/revoke_subscription.php';
require_once __DIR__ . '/traits/remove_user.php';
require_once __DIR__ . '/traits/modify_user.php';
require_once __DIR__ . '/traits/change_status.php';
require_once __DIR__ . '/traits/reset_usage.php';
require_once __DIR__ . '/traits/extend.php';
require_once __DIR__ . '/traits/extra_volume.php';
require_once __DIR__ . '/traits/extra_time.php';

class ManagePanel
{
    public $pdo, $domainhosts, $name_panel, $new_marzban;

    use ManagePanelCreateUserTrait;
    use ManagePanelDataUserTrait;
    use ManagePanelRevokeSubscriptionTrait;
    use ManagePanelRemoveUserTrait;
    use ManagePanelModifyUserTrait;
    use ManagePanelChangeStatusTrait;
    use ManagePanelResetUsageTrait;
    use ManagePanelExtendTrait;
    use ManagePanelExtraVolumeTrait;
    use ManagePanelExtraTimeTrait;
}
