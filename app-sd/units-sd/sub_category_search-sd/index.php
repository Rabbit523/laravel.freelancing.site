<?php

	$reqAuth = false;
	$module = 'sub_category_search-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."sub_category_search-sd.lib.php";
	extract($_REQUEST);

	$winTitle = 'All Sub Categories - ' . SITE_NM;
	$headTitle = 'All Sub Categories';
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
	$cat_id = (isset($_REQUEST['id']) && $_REQUEST['id']!='') ? $_REQUEST['id'] : '';

	$mainObj = new subCategorySearch($module,$cat_id);
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";	
?>