<?php

	$reqAuth = true;
	$module = 'freelancer_financial_dashboard-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";	
	require_once DIR_CLASS."freelancer_financial_dashboard-sd.lib.php";

	include_once("process.php");
	include_once("paypal.class.php");
	$paypal = new MyPayPal();
	
	extract($_REQUEST);
	$winTitle = 'Financial Dashboard  - ' . SITE_NM;
	$headTitle = 'Financial Dashboard ';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new FreelancerFinancialDashboard($module);

	if($sessUserType!='Freelancer')
	{
		redirectPage(SITE_URL);
	}
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	if($action == "SendRedeemRequest")
	{		
		$mainObj->sendRedeemRequestData($_REQUEST);
	}
	
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>