<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."service_workroom-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$return_array = array();
$module = 'service_workroom-sd';
$mainObj = new serviceWorkroom($module);

if($action == "send_message")
{
	$content = $_POST['msg'];

	$msg = $content[0]['value'];
	$orderId = $content[1]['value'];

	$service_detail = $db->pdoQuery("SELECT * FROM tbl_services as s 
		JOIN tbl_services_order as so ON(so.servicesId=s.id)
		WHERE so.id=? ",array($orderId))->result();

	$freelanserId = $service_detail['freelanserId'];
	$customerId = $service_detail['customerId'];

	if($sessUserId == $freelanserId)
	{
		$receiverId = $customerId;
	}
	else
	{
		$receiverId = $freelanserId;
	}

	$db->insert("tbl_messages",array("entityId"=>$orderId,"senderId"=>$sessUserId,"receiverId"=>$receiverId,"entityType"=>'S',"message"=>$msg,"readStatus"=>'UR',"createdDate"=>date('Y-m-d H:i:s'),"messageType"=>'text',"ipAddress"=>get_ip_address()));
	$res = $db->pdoQuery("SELECT id FROM tbl_messages WHERE DATE(createdDate) = CURRENT_DATE() AND entityId=? AND senderId=? AND entityType=?",array($orderId,$sessUserId,"S"))->affectedRows();
	
	if($res==1){
        $msg = NEW_MESSAGE_ON_SERVICE_WORKROOM;
        $type = ($sessUserType=="Freelancer"?"c":"f");
		$detail_link = SITE_URL."service/workroom/".base64_encode($orderId)."/".$service_detail['servicesSlug'];
        $db->insert("tbl_notification",array("userId"=>$receiverId,"message"=>$msg,"detail_link"=>$detail_link,"isRead"=>'n',"notificationType"=>$type,"createdDate"=>date('Y-m-d H:i:s')));
    }
	$return_array['content'] = $mainObj->messages_list($orderId);
	echo json_encode($return_array);
	exit;
}
if($action == "delte_message")
{
	$serviceId = $db->pdoQuery("SELECT servicesId FROM tbl_services_order WHERE id = ?",array($_POST['slug']))->result();
	$userDetail = $db->pdoQuery("select deleteUser,senderId,receiverId from tbl_messages where entityId=?",array($_POST['slug']))->result();

	$msg = $db->pdoQuery("select * from tbl_messages where (senderId='".$userDetail['receiverId']."' or receiverId='".$userDetail['receiverId']."') and  (senderId='".$sessUserId."' or receiverId='".$sessUserId."')  AND (deleteUser IS NULL OR deleteUser = '' OR NOT FIND_IN_SET(?, deleteUser))",array($sessUserId))->results();
    foreach ($msg as $msg_status) {
        $status = ((empty($msg_status['deleteUser'])) ? $sessUserId : $msg_status['deleteUser'].",".$sessUserId);
        
        $db->pdoQuery("UPDATE tbl_messages SET deleteUser = ? WHERE (senderId = ? OR receiverId = ?) AND (senderId = ? OR receiverId = ?) ",array($status,$userDetail['receiverId'],$userDetail['receiverId'],$sessUserId,$sessUserId));
    }

	$return_array['content'] = $mainObj->messages_list($_POST['slug']);
	echo json_encode($return_array);
	exit;
}
if($action == "delete_single_msg")
{
	$db->update("tbl_messages",array("deleteUser"=>$sessUserId),array("id"=>$_REQUEST['msg_id']));
	$return_array['content'] = $mainObj->messages_list($_REQUEST['oId']);
	echo json_encode($return_array);
	exit;
}
if($action == "load_review")
{
	$return_array['content'] = $mainObj->reviewRating($_REQUEST['id'],$_REQUEST['type']);
	echo json_encode($return_array);
	exit;
}
if($action=="move_file")
{
	if(empty($_FILES['file']['error']))
	{

		$file_name = uploadFile($_FILES['file'], DIR_WORKROOM,SITE_WORKROOM);

		$service_detail = $db->pdoQuery("select * from tbl_services_order where id=?",array($_REQUEST['id']))->result();

		if($sessUserId == $service_detail['freelanserId'])
		{
			$receiverId = $service_detail['customerId'];
		}
		else
		{
			$receiverId = $service_detail['freelanserId'];
		}
		$db->insert("tbl_messages",array("entityId"=>$_REQUEST['id'],"senderId"=>$sessUserId,"receiverId"=>$receiverId,"entityType"=>'S',"fileName"=>$file_name['file_name'],"readStatus"=>'UR',"createdDate"=>date('Y-m-d H:i:s'),"messageType"=>'file',"ipAddress"=>get_ip_address()));

		$return_array['content'] = $mainObj->messages_list($_REQUEST['id']);
		echo json_encode($return_array);
		exit;
	}
}
if($action == "start_work")
{
	$db->update("tbl_services_order",array("actual_work_start_date"=>date('Y-m-d H:i:s'),"serviceStatus"=>'ip'),array("id"=>$_REQUEST['id']));
	$service_detail = $db->pdoQuery("select s.serviceTitle,s.servicesSlug,u.firstName,u.lastName,u.userSlug,o.totalDuration,o.orderDate,c.email from tbl_services_order As o
		LEFT JOIN tbl_services As s ON s.id = o.servicesId
		LEFT JOIN tbl_users As u ON u.id = o.freelanserId
		LEFT JOIN tbl_users As c ON c.id = o.customerId
		where o.id=?",array($_REQUEST['id']))->result();
	
	$orderDate = $service_detail['orderDate'];
	$totalDuration = $service_detail['totalDuration'];
	$date = date('Y-m-d');
	$delivery_date = date('dS F,Y', strtotime($date. ' + '.$totalDuration.' days'));

	$user_link = SITE_URL."f/profile/".$service_detail['userSlug'];
    $service_link = SITE_URL."service/".$service_detail['servicesSlug'];
    $freelancerName = "<a href='".$user_link."'>".filtering(ucfirst($service_detail['firstName']))." ".filtering(ucfirst($service_detail['lastName']))."</a>";

    $service_title = "<a href='".$service_link."'>".$service_detail['serviceTitle']."</a>";

	$arrayCont = array('greetings'=>"There!",'FREELANCER_NM'=>$freelancerName,"SERVICE_NM"=>$service_title,"DELIVERED_DATE"=>$delivery_date);
	$array = generateEmailTemplate('freelancer_start_work_for_service_intimate_to_customer',$arrayCont);
	sendEmailAddress($service_detail['email'],$array['subject'],$array['message']);
	echo json_encode($return_array);
	exit;
}
if($action == "ask_payment")
{
	$db->update("tbl_services_order",array("serviceStatus"=>'p'),array("id"=>$_REQUEST['id']));

	$service_detail = $db->pdoQuery("select s.serviceTitle,s.servicesSlug,o.totalPayment,u.firstName,u.lastName,u.userSlug,o.totalDuration,o.orderDate,c.id as cId,c.email from tbl_services_order As o
		LEFT JOIN tbl_services As s ON s.id = o.servicesId
		LEFT JOIN tbl_users As u ON u.id = o.freelanserId
		LEFT JOIN tbl_users As c ON c.id = o.customerId
		where o.id=?",array($_REQUEST['id']))->result();

	$user_link = SITE_URL."f/profile/".$service_detail['userSlug'];
    $service_link = SITE_URL."service/".$service_detail['servicesSlug'];
    $freelancerName = "<a href='".$user_link."'>".filtering(ucfirst($service_detail['firstName']))." ".filtering(ucfirst($service_detail['lastName']))."</a>";
    $service_title = "<a href='".$service_link."'>".$service_detail['serviceTitle']."</a>";
    $payment_amount = CURRENCY_SYMBOL.$service_detail['totalPayment'];
    $login_link = "<a href='".SITE_URL.'SignIn'."'>Login</a>";

    $noti_msg= $service_detail['firstName']." has asked for the payment.";
    $sid = base64_encode($_REQUEST['id']);
    $noti_link=SITE_URL."service/workroom/".$sid."/".$service_detail['servicesSlug'];
    notify('c',$service_detail['cId'],$noti_msg,$noti_link);


	$arrayCont = array('greetings'=>"There!",'FREELANCER_NM'=>$freelancerName,"SERVICE_NM"=>$service_title,"LOGIN_LINK"=>$login_link,"PAYMENT"=>$payment_amount);
	$array = generateEmailTemplate('freelancer_payment_request',$arrayCont);
	sendEmailAddress($service_detail['email'],$array['subject'],$array['message']);
	echo json_encode($return_array);
	exit;
}

if($action == "ask_refund")
{
	$db->update("tbl_services_order",array("serviceStatus"=>'ar'),array("id"=>$_REQUEST['id']));
	$service_detail = $db->pdoQuery("select s.serviceTitle,s.servicesSlug,o.totalPayment,c.firstName,c.lastName,c.userSlug,o.totalDuration,o.orderDate,c.email from tbl_services_order As o
		LEFT JOIN tbl_services As s ON s.id = o.servicesId
		LEFT JOIN tbl_users As c ON c.id = o.customerId
		where o.id=?",array($_REQUEST['id']))->result();

	$service_link = SITE_URL."service/".$service_detail['servicesSlug'];
	$service_title = "<a href='".$service_link."'>".$service_detail['serviceTitle']."</a>";
	$user_link = SITE_URL."c/profile/".$service_detail['userSlug'];
	$customerName = "<a href='".$user_link."'>".filtering(ucfirst($service_detail['firstName']))." ".filtering(ucfirst($service_detail['lastName']))."</a>";
	$login_link = "<a href='".SITE_ADMIN_URL.'manage_services_order-sd'."'>Order Detail</a>";

	$arrayCont = array('greetings'=>"There!",'CUSTOMER_NM'=>$customerName,"SERVICE_NM"=>$service_title,"LINK"=>$login_link);
	$array = generateEmailTemplate('customer_refund_request',$arrayCont);
	sendEmailAddress(ADMIN_EMAIL,$array['subject'],$array['message']);
	echo json_encode($return_array);
	exit;

}
if($action == "payToFreelancer")
{
	$id = $_REQUEST['id'];
	$db->update("tbl_wallet",array("paymentStatus"=>'c',"status"=>"completed","transactionType"=>"payToFreelancer"),array("entity_id"=> $id,"userType"=>'c',"entity_type"=>'s',"userId"=>$sessUserId,"transactionType"=>"escrow"));
	$service_charger = $db->pdoQuery("select * from tbl_services_order where id=?",array($id))->result();
	$commission = getCommision($service_charger['totalPayment'],'S');
	$db->insert("tbl_admin_commision",array("entityId"=>$id,"entityType"=>'s',"amount"=>$commission,"createdDate"=>date('Y-m-d H:i:s')));

	$final_amount = $service_charger['totalPayment'] - $commission;
	$db->insert("tbl_wallet",array("userType"=>'c',"entity_id"=>$id,"entity_type"=>'s',"userId"=>$service_charger['freelanserId'],"amount"=>$final_amount,"paymentStatus"=>'c',"status"=>'completed',"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));
	$getUserEmail = $db->pdoQuery("SELECT email FROM tbl_users WHERE id = ? ",array($service_charger['freelanserId']))->result();
    $db->pdoQuery("update tbl_users set walletAmount=walletAmount+'".$final_amount."' where email=?",array($getUserEmail['email']));

    // $db->pdoQuery("update tbl_users set walletAmount=walletAmount+'".$final_amount."' where email=?",array($_SESSION['pickgeeks_email']));
    
    $db->update("tbl_services_order",array("paymentStatus"=>'c',"serviceStatus"=>'c'),array("id"=>$id));


	$freelanserDetail = getUser($service_charger['freelanserId']);
	$customerDetail = getUser($service_charger['customerId']);
	$freelanserName = filtering(ucfirst($freelanserDetail['firstName']))." ".filtering(ucfirst($freelanserDetail['lastName']));
	$customerName = filtering(ucfirst($customerDetail['firstName']))." ".filtering(ucfirst($customerDetail['lastName']));
	$link = SITE_URL."f/profile/".$freelanserName['userSlug'];
	$serviceDetail = $db->pdoQuery("select serviceTitle,servicesSlug from tbl_services where id=?",array($service_charger['servicesId']))->result();

	$freelancerLink = "<a href='".$link."' >".$freelanserName."</a>";
	$link1 = SITE_URL."SignIn";
	$loginLink = "<a href='".$link1."'>Login</a>";
	$link2 = SITE_URL."service/".$serviceDetail['servicesSlug'];
	$serviceLink = "<a href='".$link2."'>".$serviceDetail['serviceTitle']."</a>";

	$arrayCont = array('greetings'=>"There!","FREELANCER_NM"=>$freelancerLink,"SERVICE_TITLE"=>$serviceLink,"LOGIN_LINK"=>$link1);
    $array = generateEmailTemplate('service_order_payment_for_customer',$arrayCont);
    sendEmailAddress(ADMIN_EMAIL,$array['subject'],$array['message']);

    $link3 = SITE_URL."c/profile/".$customerDetail['userSlug'];
    $customerLink = "<a href='".$link."' >".$customerName."</a>";
    $arrayCont = array('greetings'=>"There!","CUSTOMER_NM"=>$customerLink,"SERVICE_TITLE"=>$serviceLink,"LOGIN_LINK"=>$link1,"AMOUNT"=>CURRENCY_SYMBOL.$final_amount);
    $array = generateEmailTemplate('service_order_payment_for_customer',$arrayCont);
    sendEmailAddress(ADMIN_EMAIL,$array['subject'],$array['message']);

    $return_array['type'] = "success";
    echo json_encode($return_array);
	exit;
}
if($action == "message_read"){
	$orderId = $_REQUEST['order_id'];
	$userid = $sessUserId;
    $aWhere = array('entityType'=>'S','entityId'=>$orderId,'receiverId'=>$userid);
    $update = array('readStatus' => 'R');
    $db->update('tbl_messages',$update,$aWhere);
    $return_array['status'] = 'true';
    echo json_encode($return_array);
    exit;
}

echo json_encode($return_array);
exit;
?>
