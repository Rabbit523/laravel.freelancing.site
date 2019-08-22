<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."fees-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'fees-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_fees';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$feesId = isset($_GET["feesId"]) ? trim($_GET["feesId"]) : (isset($_POST["feesId"]) ? trim($_POST["feesId"]) : 0);
$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
$filtering_type = isset($_POST['filtering_type']) ? $_POST['filtering_type'] : '';

extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho,'filtering_type' => $filtering_type);
if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) {
    $pageName = filtering($_REQUEST['feesType'], 'input');
    $id = filtering($_REQUEST['feesId'], 'input', 'int');
    if ($pageName != '' && $feesId != '') {
        if (getTotalRows("tbl_fees", "feesType='" . $pageName . "' AND feesId != '" . $feesId . "' ", 'feesId') >= 1) {
            echo 'false';
            exit;
        } else {
            echo 'true';
            exit;
        }
    } else {
        echo 'false';
        exit;
    }
} else if ($action == "updateStatus" && in_array('status', $Permission)) {
    $setVal = array('isActive' => ($value == 'a' ? 'y' : 'n'));

    $db->update($table, $setVal, array("feesId" => $feesId));

    echo json_encode(array('type' => 'success', 'Fees ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

    $activity_array = array("id" => $feesId, "module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

    exit;
} else if ($action == "delete" && in_array('delete', $Permission)) {
    $isDeleted = array("isDeleted"=>'y');
     $aWhere = array("feesId" => $feesId);
    $affected_rows = $db->update($table,$isDeleted, $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $feesId, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Fees deleted successfully"));
        exit;
    } else {
         echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Fees"));
        exit;
    }
} 
else if ($action == "undo" && in_array('delete', $Permission)) {
    $isDeleted = array("isDeleted"=>'n');
    $aWhere = array("feesId" => $feesId);
    $affected_rows = $db->update($table,$isDeleted, $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $feesId, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Fees activated successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue activating Fees"));
        exit;
    }
}
 else if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}

$mainObject = new Fees($module, $feesId, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
