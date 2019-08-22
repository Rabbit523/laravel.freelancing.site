<?php
	$reqAuth = true;

	$module = 'received_job-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."received_job-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'Job Bids - ' . SITE_NM;
	$headTitle = 'Job Bids';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));
	$slug = (isset($_REQUEST['slug']) && $_REQUEST['slug']!='') ? $_REQUEST['slug'] : '';

	$objPost = new stdClass();
	$mainObj = new ReceivedJob($module,$slug);

	if($sessUserType!='Customer')
	{
		redirectPage(SITE_URL);
	}
	
	if(isset($_POST['saveJob'])){
		$mainObj->submitContent($_POST);
	}
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>