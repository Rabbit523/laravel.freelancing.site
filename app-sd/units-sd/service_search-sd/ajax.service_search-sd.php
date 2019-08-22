<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."service_search-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'service_search-sd';
$mainObj = new serviceSearch($module);

$search_array = array();
if($action  == "load_seach_data")
{
	$search_array['sorting'] = (isset($_POST['sorting']) ? $_POST['sorting'] : '');
	$search_array['category'] = (isset($_POST['category']) ? $_POST['category'] : ((isset($_POST['Rcategory'])) ? $_POST['Rcategory'] : ''));
	$search_array['subcategory'] = (isset($_POST['subcategory']) ? $_POST['subcategory'] : ((isset($_POST['Rsubcategory'])) ? $_POST['Rsubcategory'] : ''));

	$search_array['exp_lvl'] = (isset($_POST['exp_lvl']) ?  $_POST['exp_lvl'] : (isset($_POST['Rexp_lvl']) ? $_POST['Rexp_lvl'] : ''));

	$search_array['deliveryTime'] = (isset($_POST['deliveryTime']) ? $_POST['deliveryTime'] : (isset($_POST['RdeliveryTime']) ? $_POST['RdeliveryTime'] : ''));

	$search_array['start_amount'] = (isset($_POST['start_amount']) ? $_POST['start_amount'] : '');
	$search_array['end_amount'] = (isset($_POST['end_amount']) ? $_POST['end_amount'] : '');

	$search_array['Rstart_amount'] = (isset($_POST['Rstart_amount']) ? $_POST['Rstart_amount'] : '');
	$search_array['Rend_amount'] = (isset($_POST['Rend_amount']) ? $_POST['Rend_amount'] : '');

	$search_array['keyword'] = (isset($_POST['searchKeyword']) ? $_POST['searchKeyword'] : '');
	$search_array['serviceType'] = (isset($_POST['serviceType']) ? $_POST['serviceType'] : (isset($_POST['RserviceType']) ? $_POST['RserviceType'] : ''));
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

    $total_data = $db->pdoQuery("SELECT (select count(id) from tbl_services_order where tbl_services_order.servicesId = s.id and paymentStatus='c' and serviceStatus='c' group by tbl_services_order.servicesId) As soldServices,s.*,AVG(startratings) AS freelancerrate,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,f.firstName,f.lastName,f.location FROM tbl_services AS s
            LEFT JOIN tbl_category AS c ON c.id = s.servicesCategory
            LEFT JOIN tbl_subcategory AS sub ON sub.id = s.servicesSubCategory
            LEFT JOIN tbl_reviews AS r ON r.freelancerid = s.freelanserid
            LEFT JOIN tbl_users AS f ON f.id = s.freelanserid
            where s.isActive ='y' and s.isApproved='a' and s.isDelete = 'n'
            $where
            group by s.id ".$sorting)->affectedRows();

    $query = $db->pdoQuery("SELECT (select count(id) from tbl_services_order where tbl_services_order.servicesId = s.id and paymentStatus='c' and serviceStatus='c' group by tbl_services_order.servicesId) As soldServices,s.*,AVG(startratings) AS freelancerrate,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,f.firstName,f.lastName,f.location FROM tbl_services AS s
            LEFT JOIN tbl_category AS c ON c.id = s.servicesCategory
            LEFT JOIN tbl_subcategory AS sub ON sub.id = s.servicesSubCategory
            LEFT JOIN tbl_reviews AS r ON r.freelancerid = s.freelanserid
            LEFT JOIN tbl_users AS f ON f.id = s.freelanserid
            where s.isActive ='y' and s.isApproved='a' and s.isDelete = 'n'
            $where
            group by s.id ".$sorting." LIMIT ".$start_from.",".$num_rec_per_page)->affectedRows();
    $load_data = load_more_data($total_data,'10',$query,$_REQUEST['page_no']);
    $page = $load_data['page'];
    $return_array['btn'] = $load_data['btn'];
	$return_array['content'] = $mainObj->serviceList($search_array,$_REQUEST['page_no']);
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

if($action == "saveServices")
{
	if(isset($sessUserId) && $sessUserId>0)
	{
		$query = $db->pdoQuery("select * from tbl_favorite_services where customerId = ? and serviceId = ? ",array($sessUserId,$_REQUEST['id']))->affectedRows();
		if($query==0)
		{
			$db->insert("tbl_favorite_services",array("customerId"=>$sessUserId,"serviceId"=>$_REQUEST['id'],"createdDate"=>date('Y-m-d H:i:s')));
			$return_array['type'] = "success";
			$return_array['msg'] = ADDED_TO_YOUR_FAVOURITE_LIST;
		}
		else
		{
			$return_array['type'] = "warning";
			$return_array['msg'] = THIS_SERVICE_HAS_ALREADY_BEEN_IN_YOUR_FAVOURITE_SERVICES_LIST;
		}
		echo json_encode($return_array);
		exit;
	}
	else
	{
		$return_array['type'] = "error";
		$return_array['msg'] = PLEASE_LOGIN_TO_SAVE_THIS_SERVICE;
		$_SESSION['last_page'] = "search/service/";
		echo json_encode($return_array);
		exit;
	}

}

echo json_encode($return_array);
exit;
?>
