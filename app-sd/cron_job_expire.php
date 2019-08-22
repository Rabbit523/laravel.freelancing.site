<?php
	
	require_once "requires-sd/config-sd.php";
	$jobs = $db->pdoQuery("select * from tbl_jobs where (jobStatus=? OR jobStatus=? OR jobStatus=? OR jobStatus=?) and isApproved=?",array('p','ip','ud','dsp','a'))->results();
	foreach ($jobs as $value) 
	{
		$job_bid = $db->pdoQuery("select * from tbl_job_bids where jobid='".$value['id']."' ")->affectedRows();
		if($job_bid==0)
		{
			if($value['biddingDeadline']<date('Y-m-d'))
			{
				$db->update("tbl_jobs",array("jobStatus"=>'c'),array("id"=>$value['id']));
			}
		}
		else
		{
			$job_bid_check = $db->pdoQuery("select * from tbl_job_bids where jobid='".$value['id']."' and isHired='y' ")->affectedRows();
			if($job_bid_check>0)
			{
				if($value['biddingDeadline']<date('Y-m-d'))
				{
					$db->update("tbl_jobs",array("jobStatus"=>'c'),array("id"=>$value['id']));
				}
			}
			
		}
	}
	
?>