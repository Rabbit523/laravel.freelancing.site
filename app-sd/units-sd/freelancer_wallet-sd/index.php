<?php

	$reqAuth = true;
	$module = 'freelancer_wallet-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."freelancer_wallet-sd.lib.php";

	include_once("process.php");
	include_once("paypal.class.php");

	$paypal = new MyPayPal();
	extract($_REQUEST);

	$winTitle = 'Wallet  - ' . SITE_NM;
	$headTitle = 'Wallet ';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new FreelancerWallet($module);

	if($sessUserType!='Freelancer')
	{
		redirectPage(SITE_URL);
	}
	redirectPage(SITE_URL."financial-dashboard");
	
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	if($action == "SendRedeemRequest")
	{
		
		$mainObj->sendRedeemRequestData($_REQUEST);
	}
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>