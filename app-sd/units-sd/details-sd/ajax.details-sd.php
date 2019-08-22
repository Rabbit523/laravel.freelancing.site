<?php
	require_once("../../requires-sd/config-sd.php");
	include(DIR_CLASS."listing-sd.lib.php");
	$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
	if($action=='accept'){
		$operation=isset($_GET['operation'])?$_GET['operation']:"";
		$bidId=isset($_GET['bidId'])?$_GET['bidId']:"";
		$query=$db->pdoQuery('select tbl_users.id,tbl_listing.listingId,tbl_listing.saleType,tbl_bids.biddedDate,tbl_bids.amount from tbl_users join tbl_bids on(tbl_bids.buyerId=tbl_users.id) join tbl_listing on(tbl_listing.listingId=tbl_bids.listingId) where tbl_bids.bidId= '.$bidId)->result();
		$sale=($query['saleType']=='c')?'Offer' : 'Bid';
		$response=array();
		$response['sale']=$sale;
		$response['operation']=$operation;
		$data['id']=$query['id'];
		$data['template_name']='bid_or_offer_accept';
		$data['template_name_admin']='bid_or_offer_accept_admin';
		$data['notification_pref']=getTableValue("tbl_notification_pref",'accept_reject_bid',array('user_id' => $query['id']));
		if($operation=='accept'){
			$insert_array=array('isWon' =>'y','approvedDate' => date('Y-m-d H:i:s'),'approveStatus' => 'accepted');
			$response['message']=($db->update('tbl_bids',$insert_array,array('bidId' => $bidId))->affectedRows() > 0)?'true':'false';
		    $data['email_content']=array('greetings'=>'Congratulations,'.getUserName($query['id']),'BID_OR_OFFER'=>$sale,'url' =>getlistingFullUrl($query['listingId']),'biddedDate' => date(DATE_FORMAT,strtotime(date('Y-m-d H:i:s'))),'amount' =>CURRENCY_SYMBOL.$query['amount'],'accept' => 'Accepted');
		    sendmail_updates($data);
		   
		}
		if($operation=='reject'){
			$insert_array=array('isWon' =>'n','approveStatus' => 'rejected');
			$response['message']=($db->update('tbl_bids',$insert_array,array('bidId' => $bidId))->affectedRows() > 0)?'true':'false';
		    $data['email_content']=array('greetings'=>'Hello,'.getUserName($query['id']),'BID_OR_OFFER'=>$sale,'url' =>getlistingFullUrl($query['listingId']),'biddedDate' => date(DATE_FORMAT,strtotime(date('Y-m-d H:i:s'))),'amount' =>CURRENCY_SYMBOL.$query['amount'],'accept' => 'Rejected');
		    sendmail_updates($data);			
		    
		}
		echo json_encode($response);
		exit;					
	}
?>