<?php
	
	$reqAuth = true;

	require_once "../../requires-sd/config-sd.php";

	require_once DIR_CLASS."payment-sd.lib.php";

	$data = new stdClass();
	if($_REQUEST['txn_id']!="" && $_REQUEST['payment_status']=="Completed"){	

	/*	$data->user_id = $sessUserId;
		$data->paid_amount = $_REQUEST['mc_gross'];
		$data->transactionId = $_REQUEST['txn_id'];
		$data->payment_datetime = date('Y-m-d H:i:s');
		$data->complete_datetime = date('Y-m-d H:i:s');
		$data->payment_status = 'c';
		$data->ipaddress = get_ip_address();*/
		
		// echo "<pre>";
		// print_r($data);exit();

		//print($_REQUEST['item_number']);exit();
		$id=$_REQUEST['item_number'];
					
						$channel_id = $db->pdoQuery("SELECT ad_title,channel_id FROM tbl_manage_ad WHERE id=".$id)->result();
						$data->amount =sprintf("%4.2f", $httpParsedResponseAr['AMT']);
						$data->transaction_id = $httpParsedResponseAr['TRANSACTIONID'];	
						$mainid= $db->pdoQuery("SELECT id FROM tbl_channels WHERE id=".$channel_id['channel_id'])->result();
						$data->channel_id = $mainid['id'];
						$data->ad_title = $channel_id['ad_title'];
						$data->is_delete = 'n';
						$data->isActive = 'y';
						$data->transaction_type = 'ad_payment';
						$data->item_id = $id;
						$data->payment_status = 'success';
						$data->created_date = date('Y-m-d');
				$db->insert('tbl_payment', (array)$data);

		//$db->update('tbl_or',array('paymentStatus'=>'paid'),array('id'=>$u[1]));

		$select = $db->pdoQuery("
										SELECT concat(u.first_name,' ',u.last_name) as userFullNmae,u.email,m.ad_title
										FROM tbl_manage_ad as m
										INNER JOIN tbl_channels as c on c.id=m.channel_id
										INNER JOIN tbl_users as u on u.id=c.user_id
										WHERE m.id=$id
										")->result();
				/*print_r($select);exit();*/
					
                    $dates=date("Y-m-d h:i:s");
                    $ip = get_ip_address();
                    $user_detailss = $db->insert("tbl_notification",array("channel_id"=>$mainid['id'],"notificationtype_id"=>'6',"description"=>"Pay ". $select_posted_ad['payable_amount']  ." for ".$channel_id['ad_title'],"ipaddress"=>$ip,"created_date"=>$dates))->getlastInsertId();  

					$to=$select['email'];
			// echo "<pre>";
			// print_r($select);exit();

		// $arrayCont = array('greetings'=>$select['userFullNmae'],
		// 				   'Amount'=>$select['totalPrice']
		// 				   );


		$extra_details ="Your Ad title: <b>".$channel_id['ad_title']."</b> ";


		       	$arrayCont = array('greetings'=>$select['userFullNmae'],
		       						'AMOUNT'=>CURRENCY_SYMBOL.$select_posted_ad['payable_amount'],
		       						'EXTRA_DETAILS'=>$extra_details);

				$array = generateEmailTemplate('paypal_success',$arrayCont);		
				sendEmailAddress($to,$array['subject'],$array['message']);

$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>'Payment Received! Your product will be sent to you very soon!'));

				redirectPage(SITE_URL);
	}
	
	//$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>''));

	redirectPage(SITE_URL);
 	
	
?>