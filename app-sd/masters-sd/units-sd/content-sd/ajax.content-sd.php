<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."content-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'content-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_content';
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
    $pageTitle = filtering($_REQUEST['pageTitle'], 'input');
    $pId = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageTitle != '' && $pId != '') {
        if (getTotalRows("tbl_content", "pageTitle='" . $pageTitle . "' AND pId != '" . $pId . "' ", 'pId') >= 1) {
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

    $db->update($table, $setVal, array("pId" => $id));

    echo json_encode(array('type' => 'success', 'Content ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

    exit;
} else if ($action == "delete" && in_array('delete', $Permission)) {
    $aWhere = array("pId" => $id);
    $affected_rows = $db->delete($table, $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Content deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Content"));
        exit;
    }
}else if ($action == "view") {
    if(in_array('view', $Permission)) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
        add_admin_activity($activity_array);
    } else {
        die("You don't have sufficient permission to view record");
    }
}
else if ($action == "headerOrder") {
    $aWhere = array("pId" => $id);
    $headerData = ($_REQUEST['headerData'] == '')?0:$_REQUEST['headerData'];
    $headerOrder = array('headerOrder'=>$headerData);
    $db->update($table, $headerOrder, $aWhere);

    echo json_encode(array('type' => 'success', 'message' => 'Content order updated successfully'));
    exit;
}
else if ($action == "footerOrder") {
    $aWhere = array("pId" => $id);
    $footerData = ($_REQUEST['footerData'] == '')?0:$_REQUEST['footerData'];
    $footerOrder = array('footerOrder'=>$footerData);
    $db->update($table, $footerOrder, $aWhere);

    echo json_encode(array('type' => 'success', 'message' => 'Content order updated successfully'));
    exit;
}

$mainObject = new Content($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
