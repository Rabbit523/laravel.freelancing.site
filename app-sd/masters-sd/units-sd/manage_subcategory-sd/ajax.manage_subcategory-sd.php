<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) 
{
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."manage_subcategory-sd.lib.php");
$module = 'manage_subcategory-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_subcategory';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$value = isset($_POST["value"]) ? trim($_POST["value"]) : isset($_GET["value"]) ? trim($_GET["value"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;
$filterCategory = isset($_POST['filterCategory']) ? $_POST['filterCategory'] : '';
extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho,'filterCategory' => $filterCategory);

if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) 
{
    $pageName = filtering($_REQUEST['pageName'], 'input');
    $id = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageName != '' && $id != '') 
    {
        if (getTotalRows("tbl_category", "pageName='" . $pageName . "' AND id != '" . $id . "' ", 'id') >= 1) 
        {
            echo 'false';
            exit;
        } 
        else 
        {
            echo 'true';
            exit;
        }
    } 
    else 
    {
        echo 'false';
        exit;
    }
}
 else if (!empty($_GET['subcategory_name'])) {
    // print_r($_GET['tag']);exit();
        $exist = 1;
        $subcategory_name = filtering($_GET['subcategory_name'], 'input');
        $id = filtering($_GET['id'], 'input', 'int');
        if(!empty($subcategory_name)) {
            $wCond = "subcategory_name = '$subcategory_name' and maincat_id='".$_GET['maincat']."' ";
            if(!empty($id)) { $wCond .= " AND id <> $id"; }
            $exist = getTotalRows('tbl_subcategory', $wCond);
        }
        echo (!empty($exist) ? "false" : "true");
        exit;
    }
else if ($action == "updateStatus" && in_array('status', $Permission)) 
{
    $setVal = array('isActive' => ($value == 'a' ? 'y' : 'n'));

    $db->update($table, $setVal, array("id" => $id));

    echo json_encode(array('type' => 'success', 'Subcategory has been ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

    exit;
} 
else if ($action == "delete" && in_array('delete', $Permission)) 
{
    $aWhere = array("id" => $id);
     $affected_rows = $db->update($table,array("isDelete"=>'y',"isActive"=>'n'),$aWhere)->affectedRows();
      $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
    add_admin_activity($activity_array);

    if ($affected_rows && $affected_rows > 0) 
    {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Subcategory has been deleted successfully"));
        exit;
    } 
    else 
    {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting Ad"));
        exit;
    }
}
else if($action == "undo")
{
    $aWhere = array("id" => $id);
    $affected_rows = $db->update($table,array("isDelete"=>'n',"isActive"=>'y'),$aWhere)->affectedRows();
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
    add_admin_activity($activity_array);
    if ($affected_rows && $affected_rows > 0) {
        echo json_encode(array('type' => 'success', 'message' => "Subcategory has been activated sucessfully"));
        exit;
    } 
    else{
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting user"));
        exit;
    }
}
else if ($action == "view" && in_array('view', $Permission)) 
{
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
    add_admin_activity($activity_array);
}

$mainObject = new SubCategory($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
