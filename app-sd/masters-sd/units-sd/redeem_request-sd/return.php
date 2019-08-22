<?php
	
	$reqAuth = true;

	require_once "../../../requires-sd/config-sd.php";

	require_once DIR_ADMIN_CLASS."redeem_request-sd.lib.php";
	
//	printr($_REQUEST);exit();
	if($_REQUEST['txn_id']!="" && $_REQUEST['payment_status']=="Completed")
	{
		$userDetail = $db->pdoQuery("select u.* from tbl_redeem_request As r 
			LEFT JOIN tbl_users As u ON u.id = r.userId
			where r.id=?",array($_REQUEST['custom']))->result();

		$db->insert("tbl_wallet",array("userType"=>strtolower($userDetail['userType']),"entity_type"=>'r',"entity_id"=>$_REQUEST['custom'],"userId"=>$userDetail['id'],"amount"=>$_REQUEST['payment_gross'],"transactionId"=>$_REQUEST['txn_id'],"paymentStatus"=>'c',"transactionType"=>"redeemRequest","status"=>'completed',"createdDate"=>date('Y-m-d H:i:s')));

		$db->update("tbl_redeem_request",array("paymentStatus"=>'paid'),array("id"=>$_REQUEST['custom']));
		$arrayCont = array("AMOUNT"=>CURRENCY_SYMBOL.$data['amount'],"USER_APYAPAL"=>$userDetail['paypal_email']);
		$array = generateEmailTemplate('admin_pay_redeem_amount_to_user',$arrayCont);
	    sendEmailAddress(trim($userDetail['email']),$array['subject'],$array['message']);
		$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>"Amount transferred successfully into creator's account"));
	}
	elseif ($_REQUEST['payment_status']=="Pending") 
	{
		$_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>"There seems to be an issue transferring amount, Please try again later"));
	}	
	redirectPage(SITE_ADM_MOD."redeem_request-sd/"); 
	
?>