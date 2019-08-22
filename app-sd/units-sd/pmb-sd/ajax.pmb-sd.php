<?php
//error_reporting(1);
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."pmb-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$return_array = array();
$module = 'pmb-sd';
$mainObj = new pmb($module);

if($action == "send_message")
{
	$content = filtering(trim($_POST['msg']));
	$receiverId = $_POST['id'];

	$senderDetail = getUser($sessUserId);
	$receiverDetail = getUser($receiverId);
	$senderNm = filtering(ucfirst($senderDetail['firstName']))." ".filtering(ucfirst($receiverDetail['lastName']));
	$receiverNm = filtering(ucfirst($receiverDetail['firstName']))." ".filtering(ucfirst($receiverDetail['lastName']));
	$msg = "You have received new message from ".$senderNm;
	$detail_link = SITE_URL."pmb/".base64_encode($sessUserId);
	$db->insert("tbl_notification",array("userId"=>$receiverId,"message"=>$msg,"detail_link"=>$detail_link,"isRead"=>'n',"notificationType"=>'f',"createdDate"=>date('Y-m-d H:i:s')));
	
	$db->insert("tbl_pmb",array("senderId"=>$sessUserId,"ReceiverId"=>$receiverId,"message"=>$content,"readStatus"=>'n',"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));
	$return_array['chat_room'] = $mainObj->message_list($receiverId);

	echo json_encode($return_array);
	exit;
}
if($action == "delte_message")
{
	if($_REQUEST['msgType'] == 'all')
	{
		$message_detail = $db->pdoQuery("select * from tbl_pmb where (senderId='".$sessUserId."' and ReceiverId='".$_REQUEST['id']."') OR (senderId='".$_REQUEST['id']."' and ReceiverId='".$sessUserId."') ")->results();
		foreach ($message_detail as $value) 
		{
			$delete_user = $_REQUEST['id'].",".$sessUserId;
			$db->update("tbl_pmb",array("deleteUser"=>$delete_user),array("id"=>$value['id']));
		}
		$return_array['last_msg'] = $mainObj->getLastMsg($_REQUEST['id']);
		$return_array['chat_room'] = $mainObj->message_list($receiverId);
	}
	else
	{
		$message_detail = $db->pdoQuery("select * from tbl_pmb where id=?",array($_REQUEST['id']))->result();
		$delete_user = ($message_detail['deleteUser'] == '') ? $sessUserId : ($message_detail['deleteUser'].",".$sessUserId);
		$db->update("tbl_pmb",array("deleteUser"=>$delete_user),array("id"=>$message_detail['id']));
		$receiverId = ($message_detail['senderId'] == $sessUserId) ? $message_detail['ReceiverId'] : $message_detail['senderId'];
		$return_array['receiverId'] = $receiverId;
		$return_array['last_msg'] = $mainObj->getLastMsg($receiverId);
		$return_array['chat_room'] = $mainObj->message_list($receiverId);
	}
	echo json_encode($return_array);
	exit;
}
if($action == "load_chat")
{
	$return_array['chat_room'] = $mainObj->message_list($_REQUEST['id']);
	$return_array['chat_user'] = $mainObj->chatUserDetail($_REQUEST['id']);
	$return_array['chat_username'] = getUserDetails('GROUP_CONCAT(firstName," ",lastName)',$_REQUEST['id']);
	echo json_encode($return_array);
	exit;
}
echo json_encode($return_array);
exit;
?>
