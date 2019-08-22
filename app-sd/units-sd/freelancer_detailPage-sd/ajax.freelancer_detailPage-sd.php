<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_detailPage-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_detailPage-sd';
$mainObj = new FreelancerDetailPage($module, '');

if($action == "saveFreelancer")
{
	if(isset($sessUserId) && $sessUserId>0)
	{
		$freelancer_detail = $db->pdoQuery("select id from tbl_users where userSlug=?",array($_REQUEST['slug']))->result();
		$freelancer_id = $freelancer_detail['id'];

		$saved_detail = $db->pdoQuery("select * from tbl_saved_freelancer where customerId=? and freelancerId=?",array($sessUserId,$freelancer_id))->affectedRows();

		if($saved_detail>0)
		{
			$return_array['type'] = "warning";
			$return_array['msg'] = YOU_HAVE_ALREADY_SAVED_THIS_PROFILE;
		}
		else
		{
			$return_array['type'] = "success";
			$return_array['msg'] = PROFILE_SAVED_SUCCESSFULLY;
			$db->insert("tbl_saved_freelancer",array("customerId"=>$sessUserId,"freelancerId"=>$freelancer_id,"createdDate"=>date('Y-m-d H:i:s')));
		}
		echo json_encode($return_array);
		exit;
	}
	else
	{
		$return_array['type'] = "error";
		$return_array['msg'] = PLEASE_LOGIN_FOR_SAVE_THIS_FREELANCER;
		$_SESSION['last_page'] ="f/profile/".$_REQUEST['slug'];
		echo json_encode($return_array);
		exit;
	}
}
if($action == "check_report")
{
	if(isset($sessUserId) && $sessUserId>0)
	{
		$return_array['content'] = 'modal';
	}
	else
	{
		$return_array['content'] = 'link';
		$_SESSION['last_page'] ="f/profile/".$_REQUEST['s'];
	}
	echo json_encode($return_array);
	exit;
}
if($action == "saveServices")
{
	if(isset($sessUserId) && $sessUserId>0)
	{
		$query = $db->pdoQuery("select * from tbl_favorite_services where customerId = ? and serviceId = ? ",array($sessUserId,$_REQUEST['id']))->affectedRows();
		if($query==0)
		{
			$db->insert("tbl_favorite_services",array("customerId"=>$sessUserId,"serviceId"=>$_REQUEST['id'],"createdDate"=>date('Y-m-d H:i:s')));
			$return_array['type'] = "success";
			$return_array['msg'] = ADD_TO_SAVE_LIST_SUCCESSFULLY;
		}
		else
		{
			$return_array['type'] = "warning";
			$return_array['msg'] = YOU_HAVE_ALREADY_SAVE_THIS_SERVICES;
		}
		echo json_encode($return_array);
		exit;
	}
	else
	{
		$slug = getUserDetails('userSlug',$_REQUEST['fId']);
		$return_array['type'] = "error";
		$return_array['msg'] = PLEASE_LOGIN_FOR_SAVE_THIS_SERVICE;
		$_SESSION['last_page'] = "f/profile/".$slug;
		echo json_encode($return_array);
		exit;
	}
	
}


echo json_encode($return_array);
exit;
?>
