<?php
	require_once("../../requires-sd/config-sd.php");
	$forgot_email=((isset($_REQUEST['forgot_email'])) && (!empty($_REQUEST['forgot_email'])))?$_REQUEST['forgot_email']:'';
	$user_deatils = $db->select('tbl_users', array('id', 'userName'), array('email'=>$forgot_email, 'OR LOWER(email)='=>strtolower($forgot_email)))->result();
	echo (!empty($user_deatils))?'true':'false';
	exit;
?>