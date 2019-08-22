
<?php

$content = '';
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."manage_skills-sd.lib.php");
//echo "<pre>";print_r($_REQUEST);exit;
$module = 'manage_skills-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_skills';
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

if (isset($_REQUEST['ajaxvalidate']) && $_REQUEST['ajaxvalidate']) 
{
    $pageName = filtering($_REQUEST['pageName'], 'input');
    $Id = filtering($_REQUEST['id'], 'input', 'int');
    if ($pageName != '' && $Id != '') 
    {
        if (getTotalRows("tbl_skills", "pageName='" . $pageName . "' AND id != '" . $Id . "' ", 'id') >= 1) 
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
else if (!empty($_GET['skill_name'])) 
{
        $skill_name = filtering($_GET['skill_name'], 'input');
        if(!empty($skill_name)) {
            $wCond = "skill_name = '$skill_name' and id != ".$_GET['id'];
            $exist = getTotalRows('tbl_skills', $wCond);
        }
        echo (!empty($exist) ? "false" : "true");
        exit;
}
else if ($action == "updateStatus" && in_array('status', $Permission)) 
{

    $qrySel = $db->select($table, "*", array("id" => $id))->result();

    if($qrySel['isDelete'] == 'y'){
        echo json_encode(array('type' => 'error', 'This Skill has already been deleted. You  must undo skill to activate skill.'));
        exit;
    }

    $setVal = array('isActive' => ($value == 'a' ? 'y' : 'n'));

    $db->update($table, $setVal, array("id" => $id));

    echo json_encode(array('type' => 'success', 'Skill has been ' . ($value == 'a' ? 'activated ' : 'deactivated ') . 'successfully.'));

    $activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);

    add_admin_activity($activity_array);

    exit;
} 
else if ($action == "approveStatus" && in_array('status', $Permission)) 
{
    $get_skill = $db->pdoQuery("SELECT category_ids FROM tbl_skills WHERE id = ? ",array($id))->result();
    if(empty($get_skill['category_ids'])){
        echo json_encode(array('type' => 'error', 'message' => 'Please select appropriate skill category to approve request'));
        exit;
    }else{
        $setVal = array('isApproved' => 'y');
        $db->update($table, $setVal, array("id" => $id));

        $activity_array = array("id" => $id, "module" => $module, "activity" => 'approv', "action" => $value);
        add_admin_activity($activity_array);
        echo json_encode(array('type' => 'success', 'message' => 'Skill has been Approved successfully'));
        exit;
    }
} 
else if ($action == "delete" && in_array('delete', $Permission)) 
{
    $aWhere = array("id" => $id);
    $affected_rows = $db->update($table,array("isDelete"=>'y','isApproved' => 'n'), $aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) 
    {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Skill has been deleted successfully."));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue deleting skill."));
        exit;
    }
} 
else if ($action == "undo" && in_array('undo', $Permission)) 
{
    $aWhere = array("id" => $id);
    $affected_rows = $db->update($table,array("isDelete"=>'n'),$aWhere)->affectedRows();

    if ($affected_rows && $affected_rows > 0) 
    {
        $activity_array = array("id" => $id, "module" => $module, "activity" => 'undo');
        add_admin_activity($activity_array);
        
        echo json_encode(array('type' => 'success', 'message' => "Skill has been undo successfully."));
        exit;
    } else {
        echo json_encode(array('type' => 'error', 'message' => "There seems to be an issue activated skill."));
        exit;
    }
} 
else if ($action == "view" && in_array('view', $Permission)) 
{
    $activity_array = array("id" => $id, "module" => $module, "activity" => 'view');
    add_admin_activity($activity_array);
}

$mainObject = new Skills($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
