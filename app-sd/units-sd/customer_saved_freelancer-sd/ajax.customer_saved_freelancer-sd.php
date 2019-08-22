<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."customer_saved_freelancer-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = "customer_saved_freelancer-sd";
$mainObj = new CustomerSavedFreelancer($module,'');
$search_array = array();

if($action == "load_search_data"){
    $num_rec_per_page = 10;
    //$start_from = load_more_pageNo($_REQUEST['page_no'],10);

    $search_array['appStatus'] = (isset($_POST['appStatus']) ? $_POST['appStatus'] :'');
    $search_array['levelStatus'] = (isset($_POST['levelStatus']) ? $_POST['levelStatus'] :'');
    $search_array['typeStatus'] = (isset($_POST['typeStatus']) ? $_POST['typeStatus'] :'');
    $search_array['status'] = (isset($_POST['status']) ? $_POST['status'] :'');
    $search_array['keyword'] = (isset($_POST['keyword']) ? $_POST['keyword'] :'');
    $where = "j.posterId = ".$sessUserId;

    if(isset($search_array['appStatus']) && $search_array['appStatus']!='')
    {
        $where .= " AND j.isApproved='".$search_array['appStatus']."' ";
    }
    if(isset($search_array['levelStatus']) && $search_array['levelStatus']!='')
    {
        $where .= " AND j.expLevel='".$search_array['levelStatus']."' ";
    }
    if(isset($search_array['typeStatus']) && $search_array['typeStatus']!='')
    {
        $where .= " AND j.jobType='".$search_array['typeStatus']."' ";
    }
    if(isset($search_array['keyword']) && $search_array['keyword']!='')
    {
        $where .= " AND j.jobTitle LIKE '%".$search_array['keyword']."%' ";
    }

    $countRecord = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,jb.isHired,jb.userId
            from tbl_jobs as j
            LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
            LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
            LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
            where ".$where)->affectedRows();

    $jobs = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,jb.isHired,jb.userId
            from tbl_jobs as j
            LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
            LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
            LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
            where ".$where." LIMIT ".$start_from.",".$num_rec_per_page)->results();

    $load_data = load_more_data($countRecord,'10',$jobs,$_REQUEST['page_no']);
    $page = $load_data['page'];
    $return_array['btn'] = $load_data['btn'];
    $return_array['content'] = $mainObj->getJobs($search_array,$_REQUEST['page_no']);
    $return_array['pageno'] = $_REQUEST['page_no'];
    echo json_encode($return_array);
    exit;

}

if($action == "load_more_data")
{
	$num_rec_per_page = 10;
    $start_from = load_more_pageNo($_REQUEST['page_no'],10);
    $search_array['appStatus'] = (isset($_POST['appStatus']) ? $_POST['appStatus'] :'');
    $search_array['levelStatus'] = (isset($_POST['levelStatus']) ? $_POST['levelStatus'] :'');
    $search_array['typeStatus'] = (isset($_POST['typeStatus']) ? $_POST['typeStatus'] :'');
    $search_array['status'] = (isset($_POST['status']) ? $_POST['status'] :'');
    
    $countRecord = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,jb.isHired,jb.userId
            from tbl_jobs as j
            LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
            LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
            LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
            where j.posterId = ".$sessUserId)->affectedRows();

    $jobs = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,jb.isHired,jb.userId
            from tbl_jobs as j
            LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
            LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
            LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
            where j.posterId = ".$sessUserId." LIMIT ".$start_from.",".$num_rec_per_page)->results();

    $load_data = load_more_data($countRecord,'10',$jobs,$_REQUEST['page_no']);
    $page = $load_data['page'];
    $return_array['btn'] = $load_data['btn'];

    $return_array['content'] = $mainObj->getFreelancers($search_array,$_REQUEST['page_no']);
    $return_array['pageno'] = $_REQUEST['page_no'];
    echo json_encode($return_array);
    exit;
}
if($action == "delete_fid"){
	$aWhere = array("id" => $_POST['fid_id']);
    $affected_rows = $db->delete('tbl_saved_freelancer',$aWhere)->affectedRows();
    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;
}
if($action == "delete_all"){
    $aWhere = array('customerId'=>$_SESSION['pickgeeks_userId']);
    $affected_rows = $db->delete('tbl_saved_freelancer',$aWhere)->affectedRows();
    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;
}


echo json_encode($return_array);
exit;
?>
