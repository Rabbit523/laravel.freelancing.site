<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."service_detail-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$slug=isset($_GET["slug"]) ? $_GET["slug"] : (isset($_POST["slug"]) ? $_POST["slug"] : '');
$affected_rows = array();
$return_array = array();
$module = 'service_detail-sd';
$mainObj = new ServiceDetail($module);

if($action == "saveService")
{
	if(isset($sessUserId) && $sessUserId>0)
	{
		$service_id = $mainObj->serviceId($_REQUEST['slug']);
		$service_record = $db->pdoQuery("select * from tbl_saved_services where serviceId=? and userId=?",array($service_id,$sessUserId))->affectedRows();
		if($service_record==0)
		{
			$db->insert("tbl_saved_services",array("serviceId"=>$service_id,"userId"=>$sessUserId,"createdDate"=>date('Y-m-d H:i:s')));
			$return_array['type'] = 'success';
			$return_array['msg'] = SAVED_SUCCESSFULLY;
			
		}
		else
		{
			$return_array['type'] = 'warning';
			$return_array['msg'] = YOU_HAVE_ALREADY_SAVED_THIS_SERVICE;
		}
	}
	else
	{
		$return_array['type'] = "error";
		$return_array['msg'] = PLEASE_LOGIN_FOR_SAVE_THIS_SERVICE;
		$_SESSION['last_page'] = "service/".$_REQUEST['slug'];
		echo json_encode($return_array);
		exit;
	}
	echo json_encode($return_array);
	exit;
}
if($action == "favService")
{
	if(isset($sessUserId) && $sessUserId>0)
	{
		$service_id = $mainObj->serviceId($_REQUEST['slug']);
		$fav_service_record = $db->pdoQuery("select * from tbl_favorite_services where serviceId=? and customerId=?",array($service_id,$sessUserId))->affectedRows();
		if($fav_service_record==0)
		{
			$db->insert("tbl_favorite_services",array("serviceId"=>$service_id,"customerId"=>$sessUserId,"createdDate"=>date('Y-m-d H:i:s')));
			$fav_services = $db->pdoQuery("select * from tbl_favorite_services where serviceId=?",array($service_id))->affectedRows();
			$return_array['count'] = $fav_services;
			$return_array['type'] = 'success';
			$return_array['msg'] = ADD_TO_FAVOURITE_SUCCESSFULLY;
			
		}
		else
		{
			$return_array['type'] = 'warning';
			$return_array['msg'] = YOU_HAVE_ALREADY_ADD_THIS_SERVICE_INTO_FAVOURITE_LIST;
		}
		echo json_encode($return_array);
		exit;
	}
	else
	{
		$return_array['type'] = "error";
		$return_array['msg'] = PLEASE_LOGIN_FOR_ADD_SERVICE_IN_FAVOURITE_LIST;
		$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => PLEASE_LOGIN_FOR_ADD_SERVICE_IN_FAVOURITE_LIST));
		$_SESSION['last_page'] = "service/".$_REQUEST['slug'];
		echo json_encode($return_array);
		exit;
	}
}

echo json_encode($return_array);
exit;
?>
