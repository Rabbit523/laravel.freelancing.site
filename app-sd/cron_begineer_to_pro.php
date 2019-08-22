<?php

	require_once "requires-sd/config-sd.php";
	$users = $db->pdoQuery("select * from tbl_users where userType=? and freelancerLvl=?",array('F','f'))->results();
	foreach ($users as $value)
	{
		$query = $db->pdoQuery("select count(b.id) As totalCompltJobs from tbl_job_bids As b
			LEFT JOIN tbl_jobs As j ON j.id = b.jobId
			where b.userId=? and b.isHired=? and (j.jobStatus=? OR j.jobStatus=?)",array($value['id'],'y','co','dsCo'))->result();
		if($query['totalCompltJobs']>=10 && $query['totalCompltJobs']<50 )
		{
			$db->update("tbl_users",array('freelancerLvl'=>'i'),array('id'=>$value['id']));
			$msg = "Congratulations!! As upon completion of 10 successful jobs, You are promoted to next Intermediate level.";
			$db->insert("tbl_notification",array("userId"=>$value['id'],"message"=>$msg,"detail_link"=>"","isRead"=>'n',"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')));
		}
		if($query['totalCompltJobs']>=50)
		{
			$db->update("tbl_users",array('freelancerLvl'=>'e'),array('id'=>$value['id']));
			$msg = "Congratulations!! As upon completion of 50 successful jobs, You are promoted to ultimate PRO level.";
			$db->insert("tbl_notification",array("userId"=>$value['id'],"message"=>$msg,"detail_link"=>"","isRead"=>'n',"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')));
		}
	}

?>