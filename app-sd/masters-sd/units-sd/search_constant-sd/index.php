<?php
$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
include(DIR_ADMIN_CLASS."search_constant-sd.lib.php");
$module = "search_constant-sd";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
$table = "tbl_language_constant";

$styles = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN));

$scripts = array("core/datatable.js",
    array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
    array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN));

chkPermission($module);
$Permission = chkModulePermission($module);

$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    'author' => AUTHOR));
$breadcrumb = array("Manage Freelancer Constant");

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Language Main Page';
$winTitle = $headTitle . ' - ' . SITE_NM;


if ($_SERVER["REQUEST_METHOD"] == "POST" ) 
{

    $response = array();
    $response['status'] = false;
    
    extract($_POST);
    
    $objPost->value = isset($value) ? $value : '';
        
    if ($type == 'edit' && $id > 0) {

        if (in_array('edit', $Permission)) {

            $objPostArray = (array) $objPost;
            $db->update($table, $objPostArray, array("id" => $id));

            $activity_array = array("id" => $id, "module" => $module, "activity" => 'edit');
            add_admin_activity($activity_array);

            $response['status'] = true;
            $response['tab_id'] = $tab_name;
            $response['success'] = "Content has been updated successfully";
            echo json_encode($response);
            exit;
        } else {
            $response['error'] = "You don't have permission.";
            echo json_encode($response);
            exit;
        }
    } else {
        if (in_array('add', $Permission)) {
            //$objPost->createdDate = date("Y-m-d H:i:s");

            $objPostArray = (array) $objPost;
         
            $id = $db->insert($table, $objPostArray)->getLastInsertId();

            $activity_array = array("id" => $id, "module" => $module, "activity" => 'add');
            add_admin_activity($activity_array);

            $response['status'] = true;
            $response['success'] = "Content has been added successfully";
            echo json_encode($response);
            exit;
        } else {
            $response['error'] = "You don't have permission.";
            echo json_encode($response);
            exit;
        }
    }
    
}

$objContent = new SearchConstant($module);
$pageContent = $objContent->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");

