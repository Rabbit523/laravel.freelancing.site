<?php
	$reqAuth = true;
	$module = 'freelancer_myjobs-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."freelancer_myjobs-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'My Jobs - ' . SITE_NM;
	$headTitle = 'My Jobs';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new FreelancerMyJobs($module);

	if($sessUserType!='Freelancer')
	{
		redirectPage(SITE_URL);
	}
	
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	
	

	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>