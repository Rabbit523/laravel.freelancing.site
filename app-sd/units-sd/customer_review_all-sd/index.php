<?php
	$reqAuth = true;
	$module = 'customer_review_all-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."customer_review_all-sd.lib.php";
	

	extract($_REQUEST);

	$winTitle = 'Review - ' . SITE_NM;
	$headTitle = 'Review';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new CustomerReviewAll($module);
	
	if(empty($id))
	{
		redirectPage(SITE_URL);
	}
	
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>