<?php

$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
error_reporting(E_ALL);
include(DIR_ADMIN_CLASS."manage_subcategory-sd.lib.php");
$module = "manage_subcategory-sd";
$table = "tbl_subcategory";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;

$styles = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN),
    array("bootstrap-switch/css/bootstrap-switch.min.css", SITE_ADM_PLUGIN));

$scripts = array("core/datatable.js",
    array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
    array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN),
    array("bootstrap-switch/js/bootstrap-switch.min.js", SITE_ADM_PLUGIN));

chkPermission($module);
$Permission = chkModulePermission($module);

$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    'author' => AUTHOR));
$breadcrumb = array("Manage Sub Category");

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Sub Category';
$winTitle = $headTitle . ' - ' . SITE_NM;

$objContent = new SubCategory($module);
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $objContent->contentSubmit($_POST,$Permission);
}

$pageContent = $objContent->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");
