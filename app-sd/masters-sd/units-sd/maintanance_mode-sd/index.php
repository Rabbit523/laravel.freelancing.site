<?php
    $reqAuth = true;
    require_once("../../../requires-sd/config-sd.php");
    include(DIR_ADMIN_CLASS."maintanance_mode-sd.lib.php");
    $module = "maintanance_mode-sd";
    $reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
    $table = "tbl_variables";

    chkPermission($module);
    $Permission = chkModulePermission($module);

    $metaTag = getMetaTags(array("description" => "Admin Panel",
        "keywords" => 'Admin Panel',
        'author' => AUTHOR));
    $breadcrumb = array(" Maintanance mode");

    $id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
    $postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
    $type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

    $headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Maintanance Mode';
    $winTitle = $headTitle . ' - ' . SITE_NM;

    $objContent = new Maintanancemode($module);
    $pageContent = $objContent->getPageContent();
    require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");