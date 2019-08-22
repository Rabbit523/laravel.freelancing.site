<?php
	$reqAuth = true;

	$module = 'service_confirmation_order-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."service_confirmation_order-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'Services Confirm Order - ' . SITE_NM;
	$headTitle = 'Services Confirm Order';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	
	
	$mainObj = new ConfirmServiceOrder($module,'','',$_REQUEST);

	
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';

	//printr($_REQUEST,1);exit;
	
	if($action == "payforOrder")
	{
		$mainObj->payForOrder($_REQUEST);
	}
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>