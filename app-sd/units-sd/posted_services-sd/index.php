<?php
$reqAuth = true;
$module = 'posted_services-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
require_once "../../requires-sd/config-sd.php";
require_once DIR_CLASS."posted_services-sd.lib.php";
extract($_REQUEST);

$winTitle = 'Posted Services - ' . SITE_NM;
$headTitle = 'Posted Services';
$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

$objPost = new stdClass();
$mainObj = new PostedServices($module);


$pageContent = $mainObj->getPageContent();
require_once DIR_TMPL . "compiler-sd.skd";	
?>