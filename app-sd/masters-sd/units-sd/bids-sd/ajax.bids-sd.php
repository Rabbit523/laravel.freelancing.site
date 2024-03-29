<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."bids-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'bids-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_bids';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
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

$mainObject = new Bids($module, $id, NULL, $searchArray, $action);
if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) {
    $pageName = filtering($_REQUEST['userId'], 'input');
    $pId = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageName != '' && $pId != '') {
        if (getTotalRows("tbl_bids", "sellerId='" . $pageName . "' AND bidId != '" . $pId . "' ", 'bidId') >= 1) {
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
} else if ($action == "delete" && in_array('delete', $Permission)) {
    $aWhere = array("bidId" => $id);
    $affected_rows = $db->delete($table, $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Bid deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting bids"));
        exit;
    }
} else if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
        echo $mainObject->bids_details();
        exit;
    } else {
        die("You don't have sufficient permission to view record");
    }
}
extract($mainObject->data);
echo ($content);
exit;
