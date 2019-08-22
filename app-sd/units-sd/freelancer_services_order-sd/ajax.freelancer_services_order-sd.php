<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_services_order-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_services_order-sd';
$mainObj = new FreelancerServiceOrder($module, '');

$search_array = array();
if($action  == "load_seach_data")
{
	$search_array['status'] = (isset($_POST['status']) ? $_POST['status'] : '');
	
    	$pageNo = $_REQUEST['page_no']; 
        $num_rec_per_page=10;
        $start_from = ($pageNo-1) * $num_rec_per_page;
        $where = "so.freelanserId ='".$sessUserId."' ";
        if(isset($search_array['status'])) 
        {
            $where .= $mainObj->condition($search_array['status']);
        }
        $query = $db->pdoQuery("select s.*,so.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName from tbl_services_order As so 
	    	JOIN tbl_services As s ON s.id = so.servicesId
	    	JOIN  tbl_category As c ON c.id = s.servicesCategory
	    	JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
	    	JOIN tbl_users As u ON u.id = so.customerId
	        where ".$where." LIMIT ".$start_from.",".$num_rec_per_page)->affectedRows();
        $total_data = $db->pdoQuery("select s.*,so.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName from tbl_services_order As so 
	    	JOIN tbl_services As s ON s.id = so.servicesId
	    	JOIN  tbl_category As c ON c.id = s.servicesCategory
	    	JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
	    	JOIN tbl_users As u ON u.id = so.customerId
	        where ".$where)->affectedRows();

        $page = ceil($total_data/10);
    	$search_array['status'] = (isset($_REQUEST['status']) ? $_REQUEST['status'] : '');

    	$return_array['content'] = $mainObj->services_order_loop($search_array,$_REQUEST['page_no']);

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

if($action  == "load_more_data")
{
	$pageNo = $_REQUEST['page_no']; 
    $num_rec_per_page=10;
    $start_from = ($pageNo-1) * $num_rec_per_page;

    $where = "so.freelanserId ='".$sessUserId."' ";
    if($_REQUEST['status']!='')
    {
    	$where .= $mainObj->condition($_REQUEST['status']);
    }

	$query = $db->pdoQuery("select s.*,so.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName from tbl_services_order As so 
	    	LEFT JOIN tbl_services As s ON s.id = so.servicesId
	    	LEFT JOIN  tbl_category As c ON c.id = s.servicesCategory
	    	LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
	    	LEFT JOIN tbl_users As u ON u.id = so.customerId
	        where ".$where." LIMIT ".$start_from.",".$num_rec_per_page)->affectedRows();
	$total_data = $db->pdoQuery("select s.*,so.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName from tbl_services_order As so 
	    	LEFT JOIN tbl_services As s ON s.id = so.servicesId
	    	LEFT JOIN  tbl_category As c ON c.id = s.servicesCategory
	    	LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
	    	LEFT JOIN tbl_users As u ON u.id = so.customerId
	        where ".$where)->affectedRows();

	$page = ceil($total_data/10);
    $search_array['status'] = (isset($_REQUEST['status']) ? $_REQUEST['status'] : '');

    $return_array['content'] = $mainObj->services_order_loop($search_array,$pageNo);

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

if($action == "deadline_load")
{
    $data = $db->pdoQuery("select * from tbl_services_order where id=?",array($_REQUEST['id']))->result();
    $return_array['id'] = $_REQUEST['id'];
    $return_array['start_date'] = ($data['work_start_date']=='0000-00-00 00:00:00') ? '' : date('d-m-Y',strtotime($data['work_start_date']));
    $return_array['end_date'] = ($data['work_end_date']=='0000-00-00 00:00:00') ? '' : date('d-m-Y',strtotime($data['work_end_date']));
    $return_array['btn'] = $mainObj->deadlineDetail($_REQUEST['id'],'button');
    echo json_encode($return_array);
    exit;
}
if($action == "get_end_date")
{
    if($_REQUEST['start_date']!='')
    {
        $data = $db->pdoQuery("select s.*,so.* from tbl_services_order As so
            LEFT JOIN tbl_services As s ON s.id = so.servicesId
            where so.id=?",array($_REQUEST['id']))->result();
        $days = $data['quantity']*$data['noDayDelivery'];
        $start_date = date('Y-m-d',strtotime($_REQUEST['start_date']));
        $end_date = date('d-m-Y', strtotime($start_date. ' + '.$days.' days'));
       // $return_array['end_date'] = $end_date;
        $return_array['note'] = AS_PER_YOUR_SERVICE_DEADLINE_END_DATE.$end_date;
    }
    else
    {
        $return_array['end_date'] = '';
    }
    echo json_encode($return_array);
    exit;
}
if($action == "deadline_detail")
{
    $return_array['content'] = $mainObj->deadlineDetail($_REQUEST['id']);
    $return_array['btn'] = $mainObj->deadlineDetail($_REQUEST['id'],'button');
    echo json_encode($return_array);
    exit;
}
if($action == "start_services")
{
    $db->update("tbl_services_order",array("serviceStatus"=>'ip',"actual_work_start_date"=>date('Y-m-d H:i:s')),array("id"=>$_REQUEST['id']));


    $serviceOrder = $db->pdoQuery("select s.serviceTitle,CONCAT(f.firstName,' ',f.lastName) As freelancerName,c.email,o.totalDuration from tbl_services_order As o 
        LEFT JOIN tbl_services As s ON s.id = o.servicesId
        LEFT JOIN tbl_users As f ON f.id = o.freelanserId
        LEFT JOIN tbl_users As c ON c.id = o.customerId
        ")->result();

    $totalDuration = $serviceOrder['totalDuration'];
    $date = date('Y-m-d');
    $delivery_date = date('d-m-Y', strtotime($date. ' + '.$totalDuration.' days'));

    $arrayCont = array('greetings'=>"There!",'FREELANCER_NM'=>$serviceOrder['freelancerName'],"SERVICE_NM"=>ucfirst($serviceOrder['serviceTitle']),"DELIVERED_DATE"=>$delivery_date);
    $array = generateEmailTemplate('freelancer_start_work_for_service_intimate_to_customer',$arrayCont);
    sendEmailAddress($serviceOrder['email'],$array['subject'],$array['message']);

    $return_array['msg'] = "success";
    echo json_encode($return_array);
    exit;
}
echo json_encode($return_array);
exit;
?>
