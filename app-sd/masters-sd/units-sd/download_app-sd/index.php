<?php
	$reqAuth = true;
	require_once("../../../requires-sd/config-sd.php");
	include(DIR_ADMIN_CLASS."download_app-sd.lib.php");
	$module = "download_app-sd";
	$table = "tbl_download_app";
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	$scripts = array("core/datatable.js",
    array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
    array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN),
    array("bootstrap-switch/js/bootstrap-switch.min.js", SITE_ADM_PLUGIN),
    array("cropper.js", SITE_ADM_PLUGIN)
    );
	chkPermission($module);
	$Permission = chkModulePermission($module);
	$metaTag = getMetaTags(array("description" => "Admin Panel",
	    "keywords" => 'Admin Panel',
	    'author' => AUTHOR));
	$breadcrumb = array("Manage Download App Section");

	$headTitle = 'Manage Download App Section';
	$winTitle = $headTitle . ' - ' . SITE_NM;
	$objContent = new DownloadApp($module);
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	    $objContent->contentSubmit($_POST, $_FILES, $Permission);
	}
	$pageContent = $objContent->getPageContent();
	require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");

?>	