<?php

	$reqAuth = true;
	$module = 'pmb-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."pmb-sd.lib.php";

	extract($_REQUEST);

	$winTitle = 'Message  - ' . SITE_NM;
	$headTitle = 'Message ';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	$userId = (isset($_REQUEST['id']) && $_REQUEST['id']!='') ? base64_decode($_REQUEST['id']) : '';

	if($userId!='')
	{
		$userCheck = $db->pdoQuery("select * from tbl_pmb where (senderId='".$sessUserId."' and ReceiverId='".$userId."') OR (senderId='".$userId."' and ReceiverId='".$sessUserId."')")->affectedRows();
		if($userCheck==0)
		{
			$_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>SOMETHING_WENT_WRONG));
			if(isset($_SERVER['HTTP_REFERER']))
			{
				redirectPage($_SERVER['HTTP_REFERER']);
			}
			else
			{
				redirectPage(SITE_URL."pmb");
			}
		}
	}
	$mainObj = new pmb($module,'' ,$userId);
	
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>