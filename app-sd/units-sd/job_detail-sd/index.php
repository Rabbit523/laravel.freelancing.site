<?php
	$reqAuth = false;
	$module = 'job_detail-sd';

	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."job_detail-sd.lib.php";
	$table = "tbl_jobs";
	extract($_REQUEST);
	$winTitle = 'Job - ' . SITE_NM;
	$headTitle = 'Job';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	$slug = (isset($_REQUEST['slug']) && $_REQUEST['slug']!='') ? $_REQUEST['slug'] : '';
	
	$objPost = new stdClass();
	$mainObj = new JobDetail($module,$slug);
	if(isset($_POST['sbmJobDetail'])) {
		$mainObj->submitProcedure($_POST);
	}

	if($action == "report_job")
	{

		if(isset($sessUserId) && $sessUserId > 0)
		{
			$mainObj->reportJob($_REQUEST);
		}
		else
		{
			$_SESSION['last_page'] = "job/".$slug;
			$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'info', 'var' => PLEASE_LOGIN_FOR_REPORT_THIS_JOB));
			redirectPage(SITE_URL."SignIn");
		}
	}

	if($action == "send_msg")
	{
		$mainObj->send_message($_POST);
	}

	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>