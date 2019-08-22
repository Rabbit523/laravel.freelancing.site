<?php
	require_once("../../requires-sd/config-sd.php");
	//include(DIR_CLASS."login-sd.lib.php");

	$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');

	if(isset($_REQUEST['userName']) && (!empty($_REQUEST['userName']))){
		$userName=trim(strtolower($_REQUEST['userName']));
		$qry=$db->pdoQuery("SELECT id FROM tbl_users where LOWER(userName) = '$userName'")->affectedRows();
		echo ($qry > 0)?'false':'true';
		exit;
	}
	if(isset($_REQUEST['signup_email']) && (!empty($_REQUEST['signup_email']))){
		$signup_email=trim(strtolower($_REQUEST['signup_email']));
		$qry=$db->pdoQuery("SELECT id FROM tbl_users where LOWER(email) = '$signup_email'")->affectedRows();
		echo ($qry > 0)?'false':'true';
		exit;
	}

	if(isset($_REQUEST['action']) && $_REQUEST['action']=="change_language")
	{
		$languages = $db->pdoQuery("select * from tbl_language where isActive = 'y' ")->results();
		$l_ids = array_column($languages, 'id');
		$id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 'default';
		$id = in_array($id, $l_ids) ? $id : 'default';		
		$is_rtl = $id!="default"?getTableValue("tbl_language","is_rtl",array("id"=>$id)):'n';		
		if($id == 'default'){
			$_SESSION['lang_key'] = '';
		}else{
			$_SESSION['lang_key'] = $id;
		}
		$_SESSION['is_rtl'] = $is_rtl;
		echo 'true';
		die;
	}

	if(isset($_REQUEST['action']) && $_REQUEST['action']=="cancelLogin")
	{
		session_destroy();
	}
?>