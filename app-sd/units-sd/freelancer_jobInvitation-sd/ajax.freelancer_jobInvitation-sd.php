<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_jobInvitation-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_jobInvitation-sd';
$mainObj = new FreelancerJobInvitation($module, '');

if($action  == "load_more_data")
{
	$total_data = getTotalRows("tbl_job_invitation"," freelancerId='".$sessUserId."' ","id");
	$num_rec_per_page=5;
	$start_from = load_more_pageNo($_REQUEST['page_no'],5);
	$query = $db->pdoQuery("select * from tbl_job_invitation where freelancerId=? LIMIT ".$start_from.",".$num_rec_per_page,array($sessUserId))->affectedRows();
	$load_data = load_more_data($total_data,'5',$query,$_REQUEST['page_no']);
	$page = $load_data['page'];
    $return_array['btn'] = $load_data['btn'];
	$return_array['content'] = $mainObj->job_data($_REQUEST['page_no']);
	$return_array['pageno'] = $_REQUEST['page_no'];
	echo json_encode($return_array);
	exit;
}
if($action == "respond_invitation")
{
	$jobId = $_REQUEST['jobId'];
	$status = $_REQUEST['status'];
	$db->update("tbl_job_invitation",array("status"=>$status,"acceptRejectDate"=>date("Y-m-d H:i:s")),array("jobId"=>$jobId,"freelancerId"=>$sessUserId));
	if($status == 'a'){
		$return_array['initial'] = "Accepted";
		$return_array['msg'] = THIS_INVITATION_HAS_BEEN_SUCCESSFULLY_ACCEPTED;
	}
	else{
		$return_array['initial'] = "Rejected";
		$return_array['msg'] = THIS_INVITATION_HAS_BEEN_SUCCESSFULLY_REJECTED;
	}

	echo json_encode($return_array);
	exit;

}


echo json_encode($return_array);
exit;
?>
