<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."commission-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'commission-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_commision';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;

extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho);

if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) {
    $pageName = filtering($_REQUEST['specificAmount'], 'input');
    $id = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageName != '' && $id != '') {
        if (getTotalRows("tbl_commision", "specificAmount='" . $pageName . "' AND id != '" . $id . "' ", 'id') >= 1) {
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

    $db->update($table, $setVal, array("id" => $id));

    echo json_encode(array('type' => 'success', 'Commission ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

    exit;
} 
else if ($action == "delete" && in_array('delete', $Permission)) {
    $aWhere = array("id" => $id);
    $setVal = array('isDelete' =>'y');

     $affected_rows = $db->update($table, $setVal, $aWhere);
    // $affected_rows = $db->delete($aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Service Commission deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Service Commission"));
        exit;
    }
}
else if ($action == "undo" && in_array('undo', $Permission)) {
    $aWhere = array("id" => $id);
    $setVal = array('isDelete' =>'n');

     $affected_rows = $db->update($table, $setVal, $aWhere);
    // $affected_rows = $db->delete($aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Service Commission deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Service Commission"));
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

$mainObject = new Commision($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
