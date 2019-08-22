<?php
	
	require_once "requires-sd/config-sd.php";
	$query = $db->pdoQuery("select * from tbl_user_plan where isCurrent=?",array('y'))->results();
	
	foreach ($query as $value) 
	{

		$date = $value['subscribedDate'];
        $checkDate = date('Y-m-d', strtotime($date. ' + 30 days'));
		if(date('Y-m-d H:i:s')>$checkDate)
		{
			$db->update("tbl_user_plan",array("isCurrent"=>'n'),array("id"=>$value['id']));
		}	
	}
	
?>