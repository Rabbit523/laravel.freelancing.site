<?php

	$reqAuth = true;
	$module = 'credit_plan-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."credit_plan-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'Credit Package Plan - ' . SITE_NM;
	$headTitle = 'Credit Package Plan';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new CreditPlan($module);

	if($sessUserType!='Freelancer')
	{
		redirectPage(SITE_URL);
	}
	
	
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>