<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_review-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_review-sd';
$mainObj = new FreelancerReview($module, '');

if($action  == "load_more_data")
{
	$total_data = getTotalRows("tbl_reviews"," freelancerId='".$sessUserId."' ","id");
	$num_rec_per_page = 1;
    $start_from = load_more_pageNo($_REQUEST['page_no'],1);

	$query = $db->pdoQuery("select * from tbl_reviews where freelancerId=? LIMIT ".$start_from.",".$num_rec_per_page,array($sessUserId))->affectedRows();
	$load_data = load_more_data($total_data,'1',$query,$_REQUEST['page_no']);

	$page = $load_data['page'];
	$return_array['btn'] = $load_data['btn'];
	$return_array['content'] = $mainObj->review_loop($_REQUEST['page_no']);
	
	$return_array['pageno'] = $_REQUEST['page_no'];
	echo json_encode($return_array);
	exit;
}
if($action=="all_review"){
	$rid = $_REQUEST["rid"];
	$return_array["content"] = $mainObj->getAllReview($rid);
}

echo json_encode($return_array);
exit;
?>
