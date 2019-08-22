<?php
$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."manage_category-sd.lib.php");

$module = 'manage_category-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_category';
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
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho,"filtering_type"=>$filtering_type);
if(isset($_REQUEST['cat_id'])){
    $setVal = array('isHomepage' => ($value == 'a' ? 'y' : 'n'));

    $db->update($table, $setVal, array("id" => $_REQUEST['cat_id']));

    echo json_encode(array('type' => 'success', 'Listing ' . ($value == 'a' ? 'added in homepage display ' : 'removed from homepage display ') . 'successfully'));
    exit;
}

else if($action == "updateStatus" && in_array('status', $Permission)) 
{
    $setVal = array('isActive' => ($value == 'a' ? 'y' : 'n'));
    

    $db->update($table, $setVal, array("id" => $id));
    echo json_encode(array('type' => 'success', 'Category has been ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

    exit;
}
else if ($action == "delete" && in_array('delete', $Permission)) 
{
    $aWhere = array("id" => $id);
    $affected_rows = $db->update($table,array("isDelete"=>'y',"isActive"=>'n'),$aWhere)->affectedRows();

    if($affected_rows && $affected_rows > 0) 
    {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Category has been deleted successfully"));
        exit;
    }
    else 
    {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting category"));
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
        echo json_encode(array('type' => 'success', 'message' => "Category has been activated successfully"));
        exit;
    } 
    else{
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting user"));
        exit;
    }
}
else if($action == "view" && in_array('view', $Permission)) 
{
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
    add_admin_activity($activity_array);
}

$mainObject = new Category($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;