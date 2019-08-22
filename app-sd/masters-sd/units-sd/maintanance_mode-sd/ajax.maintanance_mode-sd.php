<?php
    $content = '';
    require_once("../../../requires-sd/config-sd.php");
    if ($adminUserId == 0) { die('Invalid request'); }
    include(DIR_ADMIN_CLASS."maintanance_mode-sd.lib.php");
    $module = 'maintanance_mode-sd';
    chkPermission($module);

    $Permission = chkModulePermission($module);
    $table = 'tbl_variables';
    $action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');

    extract($_GET);

    if ($action == "updateStatus" && in_array('status', $Permission)) {
        $db->update($table, array('value' => ($value == 'a' ? 'y' : 'n')), array("field_name" => 'maintanance_mode'));
        echo json_encode(array('type' => 'success', 'Maintanance Mode has been ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

         $activity_array = array("module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

        exit;
    }
        
        
