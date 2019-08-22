<?php
	$reqAuth = false;
	$module = 'freelancer_review-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."freelancer_review-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'Review - ' . SITE_NM;
	$headTitle = 'Review';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$slug = (isset($_REQUEST['slug']) && $_REQUEST['slug']!='') ? $_REQUEST['slug'] : '';

	$userDetail = $db->pdoQuery("select * from tbl_users where userSlug=?",array($slug))->result();
	$id = ($slug!='') ? $userDetail['id'] : $sessUserId;

	$mainObj = new FreelancerReview($module,$id);	
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	
	

	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>