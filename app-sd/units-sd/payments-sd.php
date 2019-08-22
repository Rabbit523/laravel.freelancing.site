<?php
	
	$reqAuth = true;

	require_once "../requires-sd/config-sd.php";

	/*echo "<pre>";
	print_r($_REQUEST);
	exit;*/
	$data = new stdClass();

	if($_REQUEST['txn_id']!="" && $_REQUEST['payment_status']=="Completed"){
	
		$data->sellerId = $_SESSION['pickgeeks_userId'];
		$data->listingId = $_REQUEST['item_number'];
		$data->orderId = urldecode($_REQUEST['custom']);
		$data->totalAmount = $_REQUEST['mc_gross'];
		$data->transactionId = $_REQUEST['txn_id'];
		$data->createdDate = date('Y-m-d H:i:s');
		$data->complete_datetime = date('Y-m-d H:i:s');
		$data->orderStatus = 'c';
		$data->ipaddress = get_ip_address();

		$db->insert('tbl_payment_history', (array)$data);

		$db->update('tbl_manage_order',array('paymentStatus'=>'completed'),array('orderId'=>urldecode($_REQUEST['custom'])));

		$db->update('tbl_listing',array('isActive'=>'y'),array('listingId'=>$_REQUEST['item_number']));

		$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>THANK_YOU_FOR_YOUR_PAYMENT));
	}
	else
	{
		$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>SOMETHING_WENT_WRONG));
	}
	redirectPage(SITE_URL);
?>