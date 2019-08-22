<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_savedJobs-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_savedJobs-sd';
$mainObj = new FreelancerSavedJobs($module, '');

$search_array = array();

if($action == "remove_record")
{
    $db->delete("tbl_saved_job",array("userId"=>$sessUserId,"jobId"=>$_REQUEST['jobId']));
}
if($action  == "load_seach_data")
{
	$search_array['status'] = (isset($_POST['status']) ? $_POST['status'] : '');
	if(isset($search_array['status']))
    {
        $num_rec_per_page = 5;
        $start_from = load_more_pageNo($_REQUEST['page_no'],5);
        $where = "sj.userId ='".$sessUserId."' ";
        $where .= $mainObj->condition($search_array['status']);
        $total_data = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location,jb.isHired,jb.userId As bidder from tbl_saved_job As sj
            LEFT JOIN tbl_jobs As j ON j.id = sj.jobId
            LEFT JOIN tbl_category As c ON c.id = j.jobCategory
            LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
            LEFT JOIN tbl_users As u ON u.id = j.posterId
            LEFT JOIN tbl_job_bids As jb ON jb.jobid = j.id
            where ".$where)->affectedRows();
        $query = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location,jb.isHired,jb.userId As bidder from tbl_saved_job As sj
            LEFT JOIN tbl_jobs As j ON j.id = sj.jobId
            LEFT JOIN tbl_category As c ON c.id = j.jobCategory
            LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
            LEFT JOIN tbl_users As u ON u.id = j.posterId
            LEFT JOIN tbl_job_bids As jb ON jb.jobid = j.id
            where ".$where." LIMIT ".$start_from.",".$num_rec_per_page)->affectedRows();


        $load_data = load_more_data($total_data,'5',$query,$_REQUEST['page_no']); 
        $page = $load_data['page'];
        $return_array['btn'] = $load_data['btn'];
    	$return_array['content'] = $mainObj->job_data($search_array,$_REQUEST['page_no']);
        $return_array['pageno'] = $_REQUEST['page_no'];
        echo json_encode($return_array);
        exit;
    }	
}

if($action  == "load_more_data")
{
    $num_rec_per_page = 5;
    $start_from = load_more_pageNo($_REQUEST['page_no'],5);

    $where = "sj.userId ='".$sessUserId."' ";
    if($_REQUEST['status']!='')
    {
        $where .= $mainObj->condition($_REQUEST['status']);
    }
    $total_data = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location,jb.isHired,jb.userId As bidder from tbl_saved_job As sj
            LEFT JOIN tbl_jobs As j ON j.id = sj.jobId
            LEFT JOIN tbl_category As c ON c.id = j.jobCategory
            LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
            LEFT JOIN tbl_users As u ON u.id = j.posterId
            LEFT JOIN tbl_job_bids As jb ON jb.jobid = j.id
            where ".$where)->affectedRows();
    
    $query = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location,jb.isHired,jb.userId As bidder from tbl_saved_job As sj
            LEFT JOIN tbl_jobs As j ON j.id = sj.jobId
            LEFT JOIN tbl_category As c ON c.id = j.jobCategory
            LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
            LEFT JOIN tbl_users As u ON u.id = j.posterId
            LEFT JOIN tbl_job_bids As jb ON jb.jobid = j.id
            where ".$where." LIMIT ".$start_from.",".$num_rec_per_page)->affectedRows();

    $search_array['status'] = (isset($_REQUEST['status']) ? $_REQUEST['status'] : '');
    $load_data = load_more_data($total_data,'5',$query,$_REQUEST['page_no']);
    $page = $load_data['page'];
    $return_array['btn'] = $load_data['btn'];
    $return_array['content'] = $mainObj->job_data($search_array,$_REQUEST['page_no']);
    $return_array['pageno'] = $_REQUEST['page_no'];
    echo json_encode($return_array);
    exit;
}


echo json_encode($return_array);
exit;
?>
