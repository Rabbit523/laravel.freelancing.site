<?php

$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
include(DIR_ADMIN_CLASS."sitesetting-sd.lib.php");

$objPost = new stdClass();

$winTitle = 'Site Settings - ' . SITE_NM;
$headTitle = 'Site Settings';

$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    "author" => SITE_NM));

$module = 'sitesetting-sd';
$breadcrumb = array("Site Settings");
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;

$objSiteSetting = new SiteSetting();

if (isset($_FILES) && !empty($_FILES)) {
  $objSiteSetting->fileSubmit($_FILES);  
}
if (isset($_POST["submitSetForm"])) {
	// echo "<pre>";
	// print_r($_POST);exit;
    $objSiteSetting->settingSubmit($_POST);
}

chkPermission($module);


$pageContent = $objSiteSetting->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");

?>