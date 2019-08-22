<?php

	$reqAuth = true;
	$module = 'details-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";
	require_once DIR_CLASS."details-sd.lib.php";
	require_once "../../../vender-sd/pagination/function.php";
	$table = "tbl_bids";

	extract($_REQUEST);
	if(isset($id) && $id>0){
		$saleType=((isset($sale)) && ($sale=='c'))?"Offers":"Bids";
	$winTitle = 'Received '.$saleType.'  - ' . SITE_NM;
	$headTitle = 'Received '.$saleType;
	$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

	$objPost = new stdClass();
	$mainObj = new Details($module);
	$pageContent = $mainObj->getPageContent();
	require_once DIR_TMPL . "compiler-sd.skd";
	}
	else{
		redirectPage(SITE_URL."listings",'refresh');
	}
?>