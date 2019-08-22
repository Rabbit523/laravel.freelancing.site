<?php

$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
include(DIR_ADMIN_CLASS."faq-sd.lib.php");
$module = "faq-sd";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
$table = "tbl_faq";

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

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' FAQs';
$winTitle = $headTitle . ' - ' . SITE_NM;
$breadcrumb = array($headTitle);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = array();
    $response['status'] = false;    
    extract($_POST);    
    $objPost->faqCategoryId = isset($faqCategoryId) ? $faqCategoryId : '';
    $objPost->question = isset($question) ? ($question) : '';
    $objPost->ansDesc = isset($ansDesc) ? ($ansDesc) : ''; 
   
    $objPost = setfeilds($objPost,'question');
    $objPost = setfeilds($objPost,'ansDesc');

    $objPost->isActive = isset($isActive) ? $isActive : 'n';
    
    if ($type == 'edit' && $id > 0) {

        if (in_array('edit', $Permission)) {

            $objPostArray = (array) $objPost;
            $db->update($table, $objPostArray, array("id" => $id));

            $activity_array = array("id" => $id, "module" => $module, "activity" => 'edit');
            add_admin_activity($activity_array);

            $response['status'] = true;
            $response['success'] = "FAQ updated successfully";

            $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => "FAQ updated successfully"));
            
            echo json_encode($response);
            exit;
        } else {
            $response['error'] = "You don't have permission to edit FAQ";
            echo json_encode($response);
            exit;
        }
    } else {
        if (in_array('add', $Permission)) {
            $objPost->createdDate = date("Y-m-d H:i:s");

            $objPostArray = (array) $objPost;
            $id = $db->insert($table, $objPostArray)->getLastInsertId();

            $activity_array = array("id" => $id, "module" => $module, "activity" => 'add');
            add_admin_activity($activity_array);

            $response['status'] = true;
            $response['success'] = "FAQ added successfully";
            echo json_encode($response);
            exit;
        } else {
            $response['error'] = "You don't have permission to add FAQ";
            echo json_encode($response);
            exit;
        }
    }
    
}
$objContent = new FAQ($module);
$pageContent = $objContent->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");
