<?php
	
	$reqAuth = true;
	require_once "../../../requires-sd/config-sd.php";
	require_once DIR_ADMIN_CLASS."manage_user_payments-sd.lib.php";
	if($_REQUEST['txn_id']!="" && $_REQUEST['payment_status']=="Completed"){
			$db->update('tbl_refund_payment',array('isPaid' => 'y'),array('refundId' => $_REQUEST['custom']));
		$data=$db->pdoQuery("select u.email,u.userName,w.* from tbl_users As u JOIN tbl_refund_payment As w ON w.userId = u.id where w.refundId  = '".$_REQUEST['custom'] ."'")->result();
		if($data['listingId']==0){
			$db->update('tbl_users',array('walletAmount' => 0),array('id' => $data['userId']));
			$db->insert('tbl_wallet', array("transactionId"=>$_REQUEST['txn_id'],"paymentStatus"=>'c','earnFrom' => 'paypal','status' => 'refund','userId' => $data['userId'],'projectId' => $data['listingId'],'amount' => $data['amount'],'createdDate' => date('Y-m-d H:i:s')));
		}
		else if($data['isSellerTransfer']=='n' && $data['isBuyerAccept']=='n'){
			$db->update('tbl_wallet',array('status' =>'refund'),array('userId' => $data['userId'],'projectId' =>$data['listingId'],'earnFrom' => 'fromBuyer'));
			$db->update('tbl_wallet',array('status' =>'cancel'),array('projectId' =>$data['listingId'],'earnFrom' => 'fromSeller'));
			$db->update('tbl_wallet',array('status' =>'cancel'),array('projectId' =>$data['listingId'],'earnFrom' => 'listingCommission'));
		}
		else if ($data['isSellerTransfer']=='y' && $data['isBuyerAccept']=='n') {
			$db->update('tbl_wallet',array('status' =>'adminPaid'),array('userId' => $data['userId'],'projectId' =>$data['listingId'],'earnFrom' => 'fromSeller'));
			$db->update('tbl_wallet',array('status' =>'cancel'),array('projectId' =>$data['listingId'],'earnFrom' => 'fromBuyer'));
			$db->update('tbl_wallet',array('status' =>'cancel'),array('projectId' =>$data['listingId'],'earnFrom' => 'listingCommission'));
		}
		$arrayCont = array("payer_email"=>PAYPAL_EMAIL,"greetings"=>$data['userName'],"amount"=>CURRENCY_SYMBOL.$data['amount'],'orderdate'=>date(DATE_FORMAT,strtotime(date('Y-m-d'))));
		$array = generateEmailTemplate('refund_user',$arrayCont);
	    sendEmailAddress(trim($data['email']),$array['subject'],$array['message']);
		$_SESSION["toastr_message"] = disMessage(array('type'=>'suc','var'=>"Amount transferred successfully into creator's account"));
	}
	elseif ($_REQUEST['payment_status']=="Pending") {
			$_SESSION["toastr_message"] = disMessage(array('type'=>'err','var'=>"There seems to be an issue transferring amount, Please try again later"));
		}	
	redirectPage(SITE_ADM_MOD."manage_user_payments-sd/"); 
	
?>