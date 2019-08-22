<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."customer_profile-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id = isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();

if($action == "private_job_status"){
	$jobId = $_POST['job_id'];
	$result = array();
	$invite_res = $db->pdoQuery("SELECT id,status FROM tbl_job_invitation WHERE jobId=? AND freelancerId=?",array($jobId,$sessUserId))->result();
	if($invite_res["status"]=='a'){	
		$result["status"]="true";
	}else{
		$result["status"]="false";
	}
	echo json_encode($result);	
	exit;
}
if($action == "checkExpLevel"){
	$jobId = $_POST['job_id'];
	$result = array();
	$job_res = $db->pdoQuery("SELECT expLevel FROM tbl_jobs WHERE id=?",array($jobId))->result();
	$user_res = $db->pdoQuery("SELECT freelancerLvl FROM tbl_users WHERE id=?",array($sessUserId))->result();

	if(($job_res["expLevel"]=="b" && $user_res["freelancerLvl"]=="f") || ($job_res["expLevel"]=="i" && $user_res["freelancerLvl"]=="i") || ($job_res["expLevel"]=="p" && $user_res["freelancerLvl"]=="e")){	
		$result["status"]="true";		
	}else{		
		if($job_res["expLevel"] == 'p'){
	        $jobExpLevel='pro';
	    }else if($job_res["expLevel"] == 'b'){
	        $jobExpLevel='beginner';
	    }else if($job_res["expLevel"] == 'i'){
	        $jobExpLevel='intermediate';
	    }
	    $result["message"] = str_replace("{USER_EXP_TYPE}", $jobExpLevel, PLACE_BID_EXPERIENCE_MSG);
		$result["status"]="false";
	}
	echo json_encode($result);	
	exit;
}


if($action == "save_job"){
	$jobId = $_POST['job_id'];

	$objPost->jobId = $jobId;
	$objPost->userId = $sessUserId;
	$objPost->createdDate = date('Y-m-d H:i:s'); 
	$job_detail = $db->pdoQuery("select * from tbl_jobs where id=?",array($jobId))->result();
	if(get_time_diff($job_detail['biddingDeadline']) == "Expired")
	{
		$return_array['type'] = "error";
		$return_array['msg'] = THIS_JOB_IS_EXPIRED;
	}else if($job_detail["posterId"]==$sessUserId){
		$return_array['type'] = "error";
		$return_array['msg'] = YOU_CAN_NOT_SAVE_YOUR_OWN_JOB;
	}
	else
	{
		$query = $db->pdoQuery("select * from tbl_saved_job where jobId=? and userId=?",array($jobId,$sessUserId))->affectedRows();
		if($query>0)
		{
			$return_array['type'] = "warning";
			$return_array['msg'] = YOU_HAVE_ALREADY_SAVE_THIS_JOB;
		}
		else
		{
			$return_array['type'] = "success";
			$return_array['msg'] = JOB_HAS_BEEN_SAVED_SUCCESSFULLY;
			$db->insert("tbl_saved_job",(array)$objPost);
		}
	}
	echo json_encode($return_array);	
	exit;
}

?>
