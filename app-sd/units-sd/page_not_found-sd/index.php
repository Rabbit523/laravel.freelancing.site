<?php

$reqAuth = false;
$module = 'page_not_found-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;

require_once "../../requires-sd/config-sd.php";

require_once DIR_CLASS."page_not_found-sd.lib.php";

$winTitle = '404  - ' . SITE_NM;
$headTitle = '404';
$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));

$mainObj = new Pagenotfound($module);

$pageContent = $mainObj->getPageContent();
require_once DIR_TMPL . "compiler-sd.skd";
?>

