<?php
	$reqAuth = false;
	$module = 'freelancer_detailPage-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."freelancer_detailPage-sd.lib.php";
	extract($_REQUEST);

	$userDetail = $db->pdoQuery("select * from tbl_users where userSlug=?",array($_REQUEST['slug']))->result();

	$winTitle = 'Profile - '.filtering(ucfirst($userDetail['userName'])) .' | '.SITE_NM;
	$headTitle = 'Profile - '.filtering(ucfirst($userDetail['userName'])).' | '.SITE_NM;
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));
	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';

	$objPost = new stdClass();
	$mainObj = new FreelancerDetailPage($module,$userDetail['id'],'',$_REQUEST['slug']);

	if(freelancerSlugCheck($_REQUEST['slug'])==0)
	{
		$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'info', 'var' => SOMETHING_WENT_WRONG));
		redirectPage(SITE_URL);
	}
	if($action == "report_user")
	{
		$mainObj->reportService($_REQUEST);
	}
	if($action == "invite_user")
	{
		$mainObj->inviteUser($_REQUEST);
	}

	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>