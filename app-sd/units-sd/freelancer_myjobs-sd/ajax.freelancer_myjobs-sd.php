<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_myjobs-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_myjobs-sd';
$mainObj = new FreelancerMyJobs($module, '');


if($action  == "load_more_data")
{
	$total_data = getTotalRows("tbl_job_bids"," userId='".$sessUserId."' ","id");

	$pageNo = $_REQUEST['page_no'];	
	$num_rec_per_page=10;
	$start_from = ($pageNo-1) * $num_rec_per_page;
	$where = "jb.userId ='".$sessUserId."' and (jb.isHired='y' OR jb.isHired='a') and j.id IS NOT NULL";
	
	$query = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location from tbl_job_bids As jb
	    	LEFT JOIN tbl_jobs As j ON j.id = jb.jobId
	    	LEFT JOIN tbl_category As c ON c.id = j.jobCategory
	        LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
	        LEFT JOIN tbl_users As u ON u.id = j.posterId
	        where ".$where." LIMIT ".$start_from.",".$num_rec_per_page)->affectedRows();

	$page = ceil($total_data/10);
	$return_array['content'] = $mainObj->job_data($pageNo);

	if($query<10 || ($page==$pageNo))
	{
		$return_array['btn'] = "hide";
	}
	else
	{
		$return_array['btn'] = "";
	}
	$return_array['pageno'] = $pageNo;
	echo json_encode($return_array);
	exit;
}


echo json_encode($return_array);
exit;
?>
