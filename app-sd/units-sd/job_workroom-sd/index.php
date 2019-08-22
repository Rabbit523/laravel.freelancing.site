<?php
	$reqAuth = true;

	$module = 'job_workroom-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."job_workroom-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'Job Workroom - ' . SITE_NM;
	$headTitle = 'Job Workroom';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$slug = (isset($_REQUEST['slug']) && $_REQUEST['slug']!='') ? $_REQUEST['slug'] : '';
	
	$objPost = new stdClass();
	$mainObj = new JobWorkroom($module,$slug);

	if(isset($_POST['submitMilestone'])) {
		$mainObj->submitContent($_POST);
	}
	if(isset($action) && $action == "report_user") {
		$mainObj->reportUser($_REQUEST);	
	} 

	if(isset($action) && $action == "submitWork")
	{
		$mainObj->saveSubmitWork($_REQUEST,$_FILES);
	}
	if(isset($action) && $action == "saveReviewData")
	{
		$mainObj->saveReviewData($_REQUEST);
	}
	
	if(isset($action) && $action == "saveDisputeData")
	{
		$mainObj->saveDisputeData($_REQUEST);
	}

	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>