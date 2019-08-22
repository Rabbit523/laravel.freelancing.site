
<?php
	$content = '';
	require_once("../../../requires-sd/config-sd.php");
	if($adminUserId == 0) { die('Invalid request'); }
	include(DIR_ADMIN_CLASS."manage_subadmin-sd.lib.php");

	$module = 'manage_subadmin-sd';
	$table = 'tbl_admin';

	chkPermission($module);
	$Permission=chkModulePermission($module);

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

	$searchArray = array("page"=>$page, "rows"=>$rows, "sort"=>$sort, "order"=>$order, "offset"=>$page, "chr"=>$chr, 'sEcho' =>$sEcho);

	if($action == "updateStatus") 
	{
		$updated_date = date('Y-m-d H:i:s');
		$db->update($table, array('updated_date'=>$updated_date,'isActive'=>$value), array("id"=>$id));
		echo json_encode(array('type'=>'success','SubAdmin has been '.($value == 'a' ? 'activated ' : 'deactivated ').'successfully.'));
		$activity_array = array("id" => $id, "module" => $module, "activity" => 'status', "action" => $value);
    	add_admin_activity($activity_array);
		exit;
	} 
	else if($action == "delete" && in_array('delete',$Permission)) 
	{
		$aWhere=array("id"=>$id);
		$db->delete($table, $aWhere);
		$db->delete("tbl_admin_permission",array("admin_id"=>$id));
		$db->delete("tbl_admin_activity",array("admin_id"=>$id));

		$activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        echo json_encode(array('type' => 'success', 'message' => "SubAdmin has been deleted successfully."));
        exit;
	}
	else if($action == "delete_activity" && in_array('delete',$Permission)) 
	{
		$aWhere = array("admin_id"=>$id);
		$db->delete('tbl_admin_activity',$aWhere);
		$activity_array = array("id" => $id, "module" => $module, "activity" => 'delete');
        add_admin_activity($activity_array);
        echo json_encode(array('type' => 'success', 'message' => "SubAdmin has been deleted successfully."));
        exit;
	} 
	else if(!empty($_GET['txt_uname'])) 
	{
		$txt_uname = strtolower(trim($_GET['txt_uname']));
		$exist = $db->pdoQuery("SELECT COUNT(id) AS exist FROM $table WHERE LOWER(uName) = ? AND id <> ?", array($txt_uname, $adminUserId))->result();
		echo (!empty($exist['exist']) ? 'false' : 'true');
		exit;
	} 
	else if(!empty($_GET['txt_email'])) 
	{
		$txt_email = strtolower(trim($_GET['txt_email']));
		$exist = $db->pdoQuery("SELECT COUNT(id) AS exist FROM $table WHERE LOWER(uEmail) = ? AND id <> ?", array($txt_email, $adminUserId))->result();
		echo (!empty($exist['exist']) ? 'false' : 'true');
		exit;
	}

	$mainObject = new SubAdmin($id, $searchArray, $action);
	extract($mainObject->data);
	echo ($content);
	exit;