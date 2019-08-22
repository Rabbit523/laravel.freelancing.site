<?php

	require_once "requires-sd/config-sd.php";
	$query = $db->pdoQuery("select * from tbl_wallet where transactionType=? and entity_type=?",array('featuredFees','s'))->results();
	foreach ($query as $value)
	{
		$expiration_time = date('Y-m-d H:i:s', strtotime($value['createdDate'] . ' +'.$value['featured_days'].' day'));
		if(date('Y-m-d H:i:s') >  $expiration_time)
		{

			$db->update("tbl_services",array("featured"=>'n',"featured_days"=>'',"featured_payment_status"=>''),array("id"=>$value['entity_id']));
		}
	}

	$job_f_expired = $db->pdoQuery("select * from tbl_wallet where transactionType=? and entity_type=?",array('featuredFees','j'))->results();

	foreach ($job_f_expired as $res)
	{
		$expiration_time = date('Y-m-d H:i:s', strtotime($res['createdDate'] . ' +'.$res['featured_days'].' day'));
		if(date('Y-m-d H:i:s') >  $expiration_time)
		{
			$db->update("tbl_jobs",array("featured"=>'n',"featuredDuration"=>'',"featuredDate"=>'',"featuredPayment"=>'n',"featuredPaymentDate"=>''),array("id"=>$res['entity_id']));
		}
	}

?>