<?php
	$reqAuth = true;
	$module = 'customer_wallet-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."customer_wallet-sd.lib.php";

	include_once("process.php");
	include_once("paypal.class.php");

	$paypal = new MyPayPal();
	extract($_REQUEST);

	$winTitle = 'Wallet  - ' . SITE_NM;
	$headTitle = 'Wallet ';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new CustomerWallet($module);

	if($sessUserType!='Customer')
	{
		redirectPage(SITE_URL);
	}
	
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	if($action == "SendRedeemRequest")
	{
		$mainObj->sendRedeemRequestData($_REQUEST);
	}
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>