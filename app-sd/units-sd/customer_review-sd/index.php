<?php
	$reqAuth = true;

	$module = 'customer_review-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."customer_review-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'Review - ' . SITE_NM;
	$headTitle = 'Review';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new CustomerReview($module);

	if($sessUserType!='Customer')
	{
		redirectPage(SITE_URL);
	}
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>