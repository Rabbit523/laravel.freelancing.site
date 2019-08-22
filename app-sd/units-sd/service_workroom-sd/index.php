<?php

$reqAuth = true;
$module = 'service_workroom-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
require_once "../../requires-sd/config-sd.php";
require_once DIR_CLASS."service_workroom-sd.lib.php";

extract($_REQUEST);

$winTitle = 'Service Workroom  - ' . SITE_NM;
$headTitle = 'Service Workroom ';
$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

$objPost = new stdClass();

$action = (isset($_REQUEST['action']) && $_REQUEST['action']!='') ? $_REQUEST['action'] : '';
$slug = (isset($_REQUEST['slug']) && $_REQUEST['slug']!='') ? $_REQUEST['slug'] : '';
$orderId = (isset($_REQUEST['id']) && $_REQUEST['id']!='') ? base64_decode($_REQUEST['id']) : '';

$mainObj = new serviceWorkroom($module,$slug,$orderId);

if(isset($action) && $action == "saveDisputeData")
{
	$mainObj->saveDisputeData($_REQUEST);
}
if(isset($action) && $action == "saveReviewData")
{
	$mainObj->saveReviewData($_REQUEST);
}
if(isset($action) && $action == "submitWork")
{
	$mainObj->saveSubmitWork($_REQUEST,$_FILES);
}
$pageContent = $mainObj->getPageContent();
require_once DIR_TMPL . "compiler-sd.skd";	
?>