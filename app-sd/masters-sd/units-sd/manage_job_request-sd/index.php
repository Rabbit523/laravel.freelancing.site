<?php
$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");

include(DIR_ADMIN_CLASS."manage_job_request-sd.lib.php");
$module = "manage_job_request-sd";
$table = "tbl_jobs";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;

$styles = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN),
    array("bootstrap-switch/css/bootstrap-switch.min.css", SITE_ADM_PLUGIN),
	array("bootstrap-datepicker/css/datepicker.css",SITE_ADM_PLUGIN),
	array("bootstrap-select.css")
    );

$scripts = array("core/datatable.js",
    array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
    array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN),
    array("bootstrap-switch/js/bootstrap-switch.min.js", SITE_ADM_PLUGIN),
	array("bootstrap-datepicker.min.js",SITE_JS),
	array("bootstrap-select.js")
    );

chkPermission($module);
$Permission = chkModulePermission($module);

$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    'author' => AUTHOR));
$breadcrumb = array("Manage Job Request");

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Job Request';
$winTitle = $headTitle . ' - ' . SITE_NM;

$objContent = new jobRequest($module);
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $objContent->contentSubmit($_POST,$Permission); 
}

$pageContent = $objContent->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");
