<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."customer_service_order-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'customer_service_order-sd';
$mainObj = new CustomerServiceOrder($module,'');
// pre_print($_REQUEST);
$search_array = array();
if($action  == "load_seach_data")
{
	$search_array['status'] = (isset($_POST['status']) ? $_POST['status'] : '');
    $search_array['keyword'] = (isset($_POST['keyword']) ? $_POST['keyword'] : '');

    $pageNo = $_REQUEST['page_no'];
    $num_rec_per_page=10;
    $start_from = ($pageNo-1) * $num_rec_per_page;

    $where = "so.customerId ='".$sessUserId."' ";
    if(isset($search_array['status']))
    {
        $where .= $mainObj->condition($search_array['status']);
    }
    if(isset($search_array['keyword']))
    {
        $where .= " AND s.serviceTitle LIKE '%".$search_array['keyword']."%' ";
    }

    $query = $db->pdoQuery("select s.*,so.*,c.".l_values('category_name')." as category_name,sub.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName from tbl_services_order As so
      LEFT JOIN tbl_services As s ON s.id = so.servicesId
      LEFT JOIN  tbl_category As c ON c.id = s.servicesCategory
      LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
      LEFT JOIN tbl_users As u ON u.id = so.customerId
      where ".$where." LIMIT ".$start_from.",".$num_rec_per_page)->affectedRows();
    $total_data = $db->pdoQuery("select s.*,so.*,c.".l_values('category_name')." as category_name ,sub.".l_values('subcategory_name')." as subcategory_name,u.firstName,u.lastName from tbl_services_order As so
      LEFT JOIN tbl_services As s ON s.id = so.servicesId
      LEFT JOIN  tbl_category As c ON c.id = s.servicesCategory
      LEFT JOIN tbl_subcategory As sub ON sub.id = s.servicesSubCategory
      LEFT JOIN tbl_users As u ON u.id = so.customerId
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

    $where = "so.customerId ='".$sessUserId."' ";
    if($_REQUEST['status']==""){
        $_REQUEST['status']='no';
    }


    if($_REQUEST['status']!='')
    {
    	$where .= $mainObj->condition($_REQUEST['status']);
    }
    if(isset($_REQUEST['keyword']) && $_REQUEST['keyword']!='')
    {
        $where .= " AND s.serviceTitle LIKE '%".$_REQUEST['keyword']."%' ";
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

    if($query < 10 || ($page==$pageNo))
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
    $rdata = $db->pdoQuery("select reason from tbl_reject_reason where entityId=".$_REQUEST['id']." order by id desc limit 1")->result();

    $return_array['startDate'] = ($data['work_start_date']=='0000-00-00 00:00:00') ? '' : date('d-m-Y',strtotime($data['work_start_date']));
    $return_array['endDate'] = ($data['work_end_date']=='0000-00-00 00:00:00') ? '' : date('d-m-Y',strtotime($data['work_end_date']));
    $accStatus = '';
    if($data['deadline_accept_status'] == 'a'){
        $accStatus = "Accepted";
        $return_array['btnAcc'] = '1';
    } else if($data['deadline_accept_status'] == 'p') {
        $accStatus = "Pending";
        $return_array['btnAcc'] = '2';
    }else if($data['deadline_accept_status'] == ''){
        $accStatus = "no";
        $return_array['btnAcc'] = '4';
    } else {
        $return_array['btnAcc'] = '3';
        $return_array['rejectReason'] = $rdata['reason'];
        $accStatus = "Rejected";
    }
    $return_array['accStatus'] = $accStatus;
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
        $days = $data['noDayDelivery'];
        $start_date = date('Y-m-d',strtotime($_REQUEST['start_date']));
        $end_date = date('d-m-Y', strtotime($start_date. ' + '.$days.' days'));
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
if($action == "ask_refund")
{
    $rid = $_POST['f_id'];
    $objPost->serviceOrderId = $rid;
    $objPost->requestDate = date('Y-m-d H:i:s');
    $objPost->acceptStatus = 'p';
    $objPostArray = (array)$objPost;
    $db->insert('tbl_refund_request',$objPostArray);
    $up_arr = array('serviceStatus' =>'ar');
    $id_arr = array('id' => $rid);
    $db->update('tbl_services_order',$up_arr,$id_arr);


    $sdata = $db->pdoQuery("SELECT ord.*, cus.id, cus.firstName, cus.userName FROM `tbl_services_order` as ord LEFT JOIN tbl_users as cus on ord.customerId = cus.id where ord.id = ? ",[$rid])->result();
    $msg = $sdata['userName'].' '.HAS_ASK_FOR_REFUND_FOR_SERVICE_ORDER;
    $link = SITE_URL.'f/services-order';
    notify('f',$sdata['freelanserId'],$msg,$link);

    $return_array['status'] = 'true';
    echo json_encode($return_array);
    exit();
}
if($action == "refund_detail"){
    $id = $_POST['id'];
    $data = $db->select('tbl_refund_request',array('*'),array('serviceOrderId'=>$id))->result();
    $data = $db->pdoQuery("SELECT rr.*,s.totalPayment FROM tbl_refund_request as rr JOIN tbl_services_order as s ON(s.id=rr.serviceOrderId) WHERE serviceOrderId=?",array($id))->result();
    $return_array['content'] = "";
    if($data['acceptStatus'] == 'p'){
        $return_array['content'] .= "<label>".ACCEPTANCE_STATUS."</label> : ".PENDING_LABEL." <br />";
        $return_array['content'] .= "<label>".FPH_AMOUNT."</label> : ".$data["totalPayment"].CURRENCY_SYMBOL." <br />";
        $return_array['content'] .= "<label>".DATE."</label> : ".DATE(DATE_FORMAT,strtotime($data["requestDate"]));
        $return_array['btn'] = "1";
    } else if($data['acceptStatus'] == 'r'){
        $return_array['content'] .= "<label>".ACCEPTANCE_STATUS."</label> : ".REJECTED_LABEL." <br><br><label>".REJECT_REASON."</label> : ".$data['reason']." <br />";
        $return_array['content'] .= "<label>".FPH_AMOUNT."</label> : ".$data["totalPayment"].CURRENCY_SYMBOL." <br />";
        $return_array['content'] .= "<label>".DATE."</label> : ".DATE(DATE_FORMAT,strtotime($data["requestDate"]));
    } else if($data['acceptStatus'] == 'a'){
        $return_array['content'] .= "<label>".ACCEPTANCE_STATUS."</label> : ".ACCEPTED_LABEL." <br />";
        $return_array['content'] .= "<label>".FPH_AMOUNT."</label> : ".$data["totalPayment"].CURRENCY_SYMBOL." <br />";
        $return_array['content'] .= "<label>".DATE."</label> : ".DATE(DATE_FORMAT,strtotime($data["requestDate"]));
    }
    echo json_encode($return_array);
    exit();
}   
if($action == "reject_reason"){
    $id = $_POST['id'];
    $reason = trim($_REQUEST['reason']);

    if(!empty($reason)){
        $up_arr = array('deadline_accept_status' =>'r');
        $id_arr = array('id' => $id);
        $db->update('tbl_services_order',$up_arr,$id_arr);

        $objPost->rejectorId = $sessUserId;
        $objPost->type = 's';
        $objPost->reason = $_POST['reason'];
        $objPost->entityId = $_POST['id'];
        $objPost->createdDate = date('Y-m-d H:i:s');
        $objPostArray = (array)$objPost;
        $db->insert('tbl_reject_reason',$objPostArray);

        $objMsg = new stdClass();
        $objMsg->senderId = $sessUserId;
        $objMsg->receiverId = $_POST['sownerid'];
        $objMsg->message = $_POST['reason'];
        $objMsg->entityId = $_POST['id'];
        $objMsg->entityType = "S";
        $objMsg->messageType = "text";
        $objMsg->readStatus = "UR";
        $objMsg->createdDate= date('Y-m-d H:i:s');
        $objMsgArray = (array)$objMsg;
        $db->insert('tbl_messages',$objMsgArray);
        $return_array['status'] = '1';

        $data = $db->pdoQuery("select reason from tbl_reject_reason where rejectorId=".$id." order by id desc limit 1")->result();
        $return_array['rejectReason'] = $data['reason'];

        $sdata = $db->pdoQuery("SELECT ord.*, cus.id, cus.firstName, cus.userName FROM `tbl_services_order` as ord LEFT JOIN tbl_users as cus on ord.customerId = cus.id where ord.id = ? ",[$id])->result();

        $msg = $sdata['userName'].' '.HAS_REJECTED_DEADLINE;
        $link = SITE_URL.'f/services-order';
        notify('f',$sdata['freelanserId'],$msg,$link);

    } else {
        $return_array['status'] = '0';
    }
    echo json_encode($return_array);
    exit();
}
if($action == "serviceAccept"){
    
   $id = $_REQUEST['id'];
   $up_arr = array('deadline_accept_status' =>'a');
   $id_arr = array('id' => $id);
   $db->update('tbl_services_order',$up_arr,$id_arr);

   $sdata = $db->pdoQuery("SELECT ord.*, cus.id, CONCAT(cus.firstName,' ',cus.lastName) as name, cus.userName FROM `tbl_services_order` as ord LEFT JOIN tbl_users as cus on ord.customerId = cus.id where ord.id = ? ",[$id])->result();
    $service_name = getTableValue("tbl_services","serviceTitle",array('id'=>$sdata["servicesId"]));
    
   $msg = $sdata['name'].' '.HAS_ACCEPTED_DEADLINE." for your service - ".$service_name;
   $link = SITE_URL.'f/services-order';
   notify('f',$sdata['freelanserId'],$msg,$link);
}

echo json_encode($return_array);
exit;
?>
