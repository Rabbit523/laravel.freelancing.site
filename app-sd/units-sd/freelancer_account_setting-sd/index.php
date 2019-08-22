<?php

	$reqAuth = true;
	$module = 'freelancer_account_setting-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."freelancer_account_setting-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'Account Setting - ' . SITE_NM;
	$headTitle = 'Account Setting';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new FreelancerAccountSetting($module);

	if($sessUserType!='Freelancer')
	{
		redirectPage(SITE_URL);
	}
	
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	if($action == "changePwd")
	{
		$mainObj->changePassword($_REQUEST);
	}
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>