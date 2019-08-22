<?php
	/* Notify User to howmany messages are  unreaded */
	require_once "requires-sd/config-sd.php";

	$message = $db->pdoQuery("
			SELECT count(msg.id) as cnt,group_concat(msg.id),group_concat(msg.message) as msgs,msg.receiverId,msg.entityType,msg.entityId,us.email,us.userType
			FROM  tbl_messages as msg
			LEFT JOIN tbl_users AS us ON msg.receiverId = us.id
			WHERE msg.readStatus='UR' AND us.isActive = 'y' AND us.isDeleted='n' 
	  		GROUP BY msg.receiverId,msg.entityId ")->results();

    if(count($message) > 0){
		foreach ($message as $value)
		{
			$email = $value['email'];
			$userType = $value['userType'];
			$count = $value['cnt'];
			$entityType = $value['entityType'];
			$entityId = $value['entityId'];
			$mailNm = 'workroom_new_message';
			$entityNm = ($entityType == 'J') ? 'Job' : 'Service';
			$login_link = "<a href='".SITE_URL.'SignIn'."'>Login</a>";


			if($entityType == "J"){
				$jobs = $db->select('tbl_jobs',array('*'),array('id'=>$entityId))->result();
				$title = $jobs['jobTitle'];
				$url = SITE_URL.'job/workroom/'.$jobs['jobSlug'];
				$msg = "You have ".$count." unread message(s).";
				notify($userType,$value['receiverId'],$msg,$url);
			} else if($entityType == "S"){
				$service = $db->select('tbl_services',array('*'),array('id'=>$entityId))->result();
				$title = $service['serviceTitle'];
				$id = base64_encode($entityId);
				$url = SITE_URL.'service/workroom/'.$id.'/'.$service['servicesSlug'];
				$msg = "You have ".$count." unread message(s).";
				notify($userType,$value['receiverId'],$msg,$url);
			}

			$entityName = "<a href='".$url."'>".$title."</a>";
			$arrayCont = array(
                'COUNT' => $count,
                'ENTITY_NM' => $title,
                'ENTITY_NAME' => $entityName,
                'LOGIN_NAME' => $login_link
            );
      		$array = generateEmailTemplate($mailNm,$arrayCont);
      		sendEmailAddress($email, $array['subject'], $array['message']);
      	}
	}


?>
