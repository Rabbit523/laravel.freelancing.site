<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."job_search-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'job_search-sd';
$mainObj = new jobSearch($module);

$search_array = array();
if($action  == "load_seach_data")
{
	$search_array['sorting'] = (isset($_POST['sorting']) ? $_POST['sorting'] : '');
	$search_array['category'] = (isset($_POST['category']) ? $_POST['category'] : ((isset($_POST['Rcategory'])) ? $_POST['Rcategory'] : ''));
	$search_array['subcategory'] = (isset($_POST['subcategory']) ? $_POST['subcategory'] : ((isset($_POST['Rsubcategory'])) ? $_POST['Rsubcategory'] : ''));
	$search_array['skills'] = (isset($_POST['skills']) ? $_POST['skills'] : (isset($_POST['Rskills']) ? $_POST['Rskills'] : ''));

	$search_array['exp_lvl'] = (isset($_POST['exp_lvl']) ?  $_POST['exp_lvl'] : (isset($_POST['Rexp_lvl']) ? $_POST['Rexp_lvl'] : ''));
	$search_array['no_applicants'] = (isset($_POST['no_applicants']) ? $_POST['no_applicants'] : (isset($_POST['Rno_applicants']) ? $_POST['Rno_applicants'] : ''));
	$search_array['start_amount'] = (isset($_POST['start_amount']) ? $_POST['start_amount'] : (isset($_POST['Rstart_amount']) ? $_POST['Rstart_amount'] : ''));
	$search_array['end_amount'] = (isset($_POST['end_amount']) ? $_POST['end_amount'] : (isset($_POST['Rend_amount']) ? $_POST['Rend_amount'] : ''));
	$search_array['keyword'] = (isset($_POST['searchKeyword']) ? $_POST['searchKeyword'] : '');
	$search_array['startdate'] = (isset($_POST['startdate']) ? $_POST['startdate'] : (isset($_POST['Rstartdate']) ? $_POST['Rstartdate'] : ''));
	$search_array['enddate'] = (isset($_POST['enddate']) ? $_POST['enddate'] : (isset($_POST['Renddate']) ? $_POST['Renddate'] : ''));
	$search_array['jobType'] = (isset($_POST['jobType']) ? $_POST['jobType'] : (isset($_POST['RjobType']) ? $_POST['RjobType'] : ''));	
	$search_array['location'] = (isset($_POST['location']) ? $_POST['location'] : (isset($_POST['Rlocation']) ? $_POST['Rlocation'] : ''));

	$num_rec_per_page = 10;
    $start_from = load_more_pageNo($_REQUEST['page_no'],10);

    $sorting = $where = '';
	if(isset($search_array['sorting']))
	{
		$sorting .= $mainObj->dataSort($search_array['sorting']);
	}
    if($search_array!='')
    {
        $where .= $mainObj->conditionWhere($search_array);
    }
    if(isset($sessUserId) && $sessUserId>0)
    {
        $where .= $mainObj->loginUserCondition();
    }

    $total_data = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location,j.jobStatus from tbl_jobs As j 
    		LEFT JOIN tbl_category As c ON c.id = j.jobCategory
	        LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
	        LEFT JOIN tbl_users As u ON u.id = j.posterId
	        LEFT JOIN tbl_reviews As r ON r.customerId = j.posterId
    		where j.jobType='pu' and j.isApproved='a' and j.isActive='y' and j.isDelete='n' and j.biddingDeadline >= '".date('Y-m-d H:i:s')."' $where group by j.id ".$sorting." ")->affectedRows();

    $query = $db->pdoQuery("select j.*,c.".l_values('category_name')." as category_name,s.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName,u.location,j.jobStatus from tbl_jobs As j 
    		LEFT JOIN tbl_category As c ON c.id = j.jobCategory
	        LEFT JOIN tbl_subcategory As s ON s.id = j.jobSubCategory
	        LEFT JOIN tbl_users As u ON u.id = j.posterId
	        LEFT JOIN tbl_reviews As r ON r.customerId = j.posterId
    		where j.jobType='pu' and j.isApproved='a' and j.isActive='y' and j.isDelete='n' and j.biddingDeadline >= '".date('Y-m-d H:i:s')."' $where group by j.id ".$sorting." LIMIT ".$start_from.",".$num_rec_per_page)->affectedRows();
    $load_data = load_more_data($total_data,'10',$query,$_REQUEST['page_no']); 
    $page = $load_data['page'];
    $return_array['btn'] = $load_data['btn'];
	$return_array['content'] = $mainObj->jobList($search_array,$_REQUEST['page_no']);
    $return_array['pageno'] = $_REQUEST['page_no'];

	echo json_encode($return_array);
	exit;
}
if($action == "subcateLoad")
{
	$category_id = $db->pdoQuery("select * from tbl_category where ".l_values('category_name')." LIKE '%".$_REQUEST['cat']."%' ")->result();
	$return_array['content'] = $mainObj->getSubcategory($category_id['id']);
	echo json_encode($return_array);
	exit;
}

if($action == "addToFav")
{
	if(isset($sessUserId) && $sessUserId>0)
	{
		$query = $db->pdoQuery("select * from tbl_saved_job where userId = ? and jobId = ? ",array($sessUserId,$_REQUEST['jobId']))->affectedRows();
		if($query==0)
		{
			$db->insert("tbl_saved_job",array("userId"=>$sessUserId,"jobId"=>$_REQUEST['jobId'],"createdDate"=>date('Y-m-d H:i:s')));
			$return_array['type'] = "success";
			$return_array['msg'] = JOB_SAVED_SUCCESS;
		}
		else
		{
			$return_array['type'] = "warning";
			$return_array['msg'] = YOU_HAVE_ALREADY_ADD_THIS_JOB_INTO_FAVOURITE_LIST;
		}
		echo json_encode($return_array);
		exit;
	}
	else
	{
		$return_array['type'] = "error";
		$return_array['msg'] = PLEASE_LOGIN_FOR_ADD_THIS_INTO_FAVOURITE_LIST;
		$_SESSION['last_page'] ="search/jobs/";
		echo json_encode($return_array);
		exit;
	}
	
}

echo json_encode($return_array);
exit;
?>
