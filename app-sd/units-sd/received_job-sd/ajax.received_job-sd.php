<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."received_job-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = "received_job-sd";
$mainObj = new ReceivedJob($module,'');
if($action == "acceptBid"){
	$id = $_REQUEST['id'];
	$return_array['status'] = $mainObj->acceptBid($id);
}
if($action == "rejectBid"){
    $aWhere = array("id" => $_REQUEST['id']);
    $up = array("isHired" => "r",'accept_reject_date' => date('Y-m-d H:i:s'));
    $job_res = $db->select("tbl_job_bids",array("userId","jobid"),$aWhere)->result();
    $job_name = $db->select("tbl_jobs",array("jobTitle"),array("id"=>$job_res['jobid']))->result();
    $msg= "Your bid on job ".$job_name['jobTitle']." has been rejected. Try with more suitable proposals.";
    $link=SITE_URL."my-bids";
    notify('f',$job_res['userId'],$msg,$link);
    $db->update('tbl_job_bids',$up,$aWhere);
    exit;
}
if($action == "hire_freelancer_act")
{
	$bid_id = $_POST['bid_id'];
	$return_array['status'] = $mainObj->hireFreelancer($bid_id);
}

echo json_encode($return_array);
exit;
?>
