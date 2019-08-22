<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_search-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_search-sd';
$mainObj = new freelancerSearch($module);

$search_array = array();

if($action  == "get_typehead"){
	$final = [];

	$query = !empty($_GET['query']) ? $_GET['query'] : '';
	$type = !empty($_GET['type']) ? $_GET['type'] : 'home';

	if($type == 'home'){
		$joblist = $db->pdoQuery("SELECT * FROM tbl_jobs as j INNER JOIN tbl_users as u ON u.id = j.posterId WHERE j.jobTitle LIKE '%".$query."%' and j.jobType != 'pr' and j.isActive = 'y' and j.isDelete = 'n' and j.jobStatus = 'p' and j.isApproved = 'a' and u.isActive='y' and u.isDeleted = 'n' limit 5")->results();
		if(!empty($joblist)){
			array_push($final, ['name' => 'Jobs','slug' => 'search/jobs/','type' => 'title'] );
			foreach ($joblist as $key => $value) {
				array_push($final,['name' => $value['jobTitle'],'slug' => 'job/'.$value['jobSlug'],'type' => 'record']);
			}
		}else{
			array_push($final, ['name' => 'Jobs','slug' => 'search/jobs/','type' => 'title'] );
			array_push($final, ['name' => $query ,'slug' => 'search/jobs/?keyword='.$query,'type' => 'record'] );
		}
	}


	$service_list = $db->pdoQuery("SELECT * FROM `tbl_services` as s INNER JOIN tbl_users as u ON u.id = s.freelanserId WHERE s.serviceTitle LIKE '%".$query."%' and s.isActive = 'y' and s.isDelete = 'n' and s.isApproved = 'a' and u.isActive='y' and u.isDeleted = 'n' limit 5")->results();

	if(!empty($service_list)){
		array_push($final, ['name' => 'Service','slug' => 'search/service/','type' => 'title'] );
		foreach ($service_list as $key => $value) {
			array_push($final,['name' => $value['serviceTitle'],'slug' => 'service/'.$value['servicesSlug'],'type' => 'record']);
		}
	}

	if($type != 'home' && empty($service_list)){
		array_push($final, ['name' => 'Service','slug' => 'search/jobs/','type' => 'title'] );
		array_push($final, ['name' => $query ,'slug' => 'search/service/?keyword='.$query,'type' => 'record'] );
	}


	$email = !empty($_SESSION['pickgeeks_email']) ? "and email !='".$_SESSION['pickgeeks_email']."'" : '';

	$freelancer_list = $db->pdoQuery("SELECT * FROM `tbl_users` WHERE (userName LIKE '%".$query."%') and userType='F' ".$email." and isActive = 'y' and isDeleted = 'n' limit 5")->results();

	if(!empty($freelancer_list)){
		array_push($final, ['name' => 'Freelancer','slug' => 'search/freelancer/','type' => 'title'] );
		array_push($final, ['name' => '<i class="fa fa-search" aria-hidden="true"></i> Search username containing "'.$query.'"' ,'slug' => 'search/freelancer/?keyword='.$query,'type' => 'subtitle'] );
		foreach ($freelancer_list as $key => $value) {
			array_push($final,['name' => $value['userName'],'slug' => 'f/profile/'.$value['userSlug'],'type' => 'record']);
		}
	}


	echo json_encode($final);
	exit;

}

if($action  == "load_seach_data")
{

	$search_array['sorting'] = (isset($_POST['sorting']) ? $_POST['sorting'] : '');
	$search_array['category'] = (isset($_POST['category']) ? $_POST['category'] : ((isset($_POST['Rcategory'])) ? $_POST['Rcategory'] : ''));
	$search_array['subcategory'] = (isset($_POST['subcategory']) ? $_POST['subcategory'] : ((isset($_POST['Rsubcategory'])) ? $_POST['Rsubcategory'] : ''));
	$search_array['skills'] = (isset($_POST['skills']) ? $_POST['skills'] : (isset($_POST['Rskills']) ? $_POST['Rskills'] : ''));

	$search_array['exp_lvl'] = (isset($_POST['exp_lvl']) ?  $_POST['exp_lvl'] : (isset($_POST['Rexp_lvl']) ? $_POST['Rexp_lvl'] : ''));

	$search_array['location'] = (isset($_POST['location']) ? $_POST['location'] : (isset($_POST['Rlocation']) ? $_POST['Rlocation'] : ''));
	$search_array['avg_rate'] = (isset($_POST['avg_rate']) ? $_POST['avg_rate'] : (isset($_POST['Ravg_rate']) ? $_POST['Ravg_rate'] : '0'));
	$search_array['last_activity'] = (isset($_POST['last_activity']) ? $_POST['last_activity'] : (isset($_POST['Rlast_activity']) ? $_POST['Rlast_activity'] : ''));
	$search_array['eng_lvl'] = (isset($_POST['eng_lvl']) ? $_POST['eng_lvl'] : (isset($_POST['Reng_lvl']) ? $_POST['Reng_lvl'] : ''));
	$search_array['keyword'] = (isset($_POST['searchKeyword']) ? $_POST['searchKeyword'] : '');

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

	$total_data = $db->pdoQuery("select f.*,AVG(r.startratings) As starRate from tbl_users As f
		LEFT JOIN tbl_subcategory As s ON s.id = f.subCategoryList
		LEFT JOIN tbl_reviews As r on r.freelancerId = f.id
		LEFT JOIN tbl_user_language As l ON l.userId = f.id
		$where group by f.id ".$sorting)->affectedRows();

	$query = $db->pdoQuery("select f.*,AVG(r.startratings) As starRate from tbl_users As f
		LEFT JOIN tbl_subcategory As s ON s.id = f.subCategoryList
		LEFT JOIN tbl_reviews As r on r.freelancerId = f.id
		LEFT JOIN tbl_user_language As l ON l.userId = f.id
		$where group by f.id ".$sorting." LIMIT ".$start_from.",".$num_rec_per_page)->affectedRows();

	$load_data = load_more_data($total_data,'10',$query,$_REQUEST['page_no']);
	$page = $load_data['page'];
	$return_array['btn'] = $load_data['btn'];
	$return_array['content'] = $mainObj->freelancerList($search_array,$_REQUEST['page_no']);
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

if($action == "addToSave")
{
	if(isset($sessUserId) && $sessUserId>0)
	{
		$query = $db->pdoQuery("select * from tbl_saved_freelancer where freelancerId = ? and customerId = ? ",array($_REQUEST['id'],$sessUserId))->affectedRows();
		if($query==0)
		{
			$db->insert("tbl_saved_freelancer",array("freelancerId"=>$_REQUEST['id'],"customerId"=>$sessUserId,"createdDate"=>date('Y-m-d H:i:s')));
			$return_array['type'] = "success";
			$return_array['msg'] = SAVED_SUCCESSFULLY;
		}
		else
		{
			$return_array['type'] = "warning";
			$return_array['msg'] = YOU_HAVE_ALREADY_SAVE_THIS_FREELANCER;

		}
		echo json_encode($return_array);
		exit;
	}
	else
	{
		$return_array['type'] = "info";
		$return_array['msg'] = PLEASE_LOGIN_FOR_SAVE_THIS_FREELANCER;
		$_SESSION['last_page'] ="search/freelancer/";
		echo json_encode($return_array);
		exit;
	}

}

echo json_encode($return_array);
exit;
?>
