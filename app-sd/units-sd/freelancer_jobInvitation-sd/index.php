<?php
	$reqAuth = true;
	$module = 'freelancer_jobInvitation-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."freelancer_jobInvitation-sd.lib.php";
	extract($_REQUEST);
	
	$winTitle = 'Invitation - ' . SITE_NM;
	$headTitle = 'Invitation';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new FreelancerJobInvitation($module);

	if($sessUserType!='Freelancer')
	{
		redirectPage(SITE_URL);
	}
	
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	
	

	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>