<?php
	$reqAuth = true;

	$module = 'customer_my_jobs-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."customer_my_jobs-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'My Jobs - ' . SITE_NM;
	$headTitle = 'My Jobs';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new CustomerMyJobs($module);

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