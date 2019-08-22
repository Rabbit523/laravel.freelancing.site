<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."newsletter-sd.lib.php");

$module = 'newsletter-sd';
chkPermission($module);
$Permission = chkModulePermission($module);

$table = 'tbl_newsletters';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;

extract($_GET);
$searchArray = array("page" => $page, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page, "chr" => $chr, 'sEcho' => $sEcho);
if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) {
    $newsletter_name = strtolower(filtering($_REQUEST['newsletter_name'], 'input'));
    $id = filtering($_REQUEST['id'], 'input', 'int');
    if ($newsletter_name != '' && $id != '') {
        if (getTotalRows("tbl_newsletters", "LOWER(newsletter_name)='" . $newsletter_name . "' AND id != '" . $id . "' ", 'id') >= 1) {
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
}
else if ($action == "updateStatus") {
    $setVal = array('status' => ($value == 'a' ? 'a' : 'd'));
    $db->update($table, $setVal, array("id" => $id));
    echo json_encode(array('type' => 'success', 'Newsletter ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));
    exit;
} else if ($action == "delete") {
    $aWhere = array("id" => $id);
    $affected_rows = $db->delete($table, $aWhere)->affectedRows();

     if ($affected_rows && $affected_rows > 0) {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Newsletter deleted successfully"));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Newsletter"));
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

$mainObject = new NewsLetter($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
