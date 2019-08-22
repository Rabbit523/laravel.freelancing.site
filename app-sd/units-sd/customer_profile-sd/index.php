<?php
	$reqAuth = true;
	$module = 'customer_profile-sd';

	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."customer_profile-sd.lib.php";
	$table = "tbl_users";
	extract($_REQUEST);
	
	$winTitle = 'Profile - ' . SITE_NM;
	$headTitle = 'Profile';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$slug = !empty($_REQUEST['slug'])?$_REQUEST['slug']:0;
	$mainObj = new CustomerProfile($module,0,$slug);
	if(isset($_POST['submitFrmCust'])){
		$mainObj->submitProcedure($_POST,$_FILES);
	}
	
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>