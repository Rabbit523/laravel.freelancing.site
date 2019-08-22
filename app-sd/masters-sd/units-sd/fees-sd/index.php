<?php

$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
include(DIR_ADMIN_CLASS."fees-sd.lib.php");
$module = "fees-sd";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
$table = "tbl_fees";

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

$id = isset($_GET["feesId"]) ? (int) trim($_GET["feesId"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Fees';
$winTitle = $headTitle . ' - ' . SITE_NM;
$breadcrumb = array($headTitle);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = array();
    $response['status'] = false;
    
    extract($_POST);
    $id = isset($feesId) ? $feesId : 0;
    $objPost->feesType = isset($feesType) ? $feesType : '';
    $objPost->listingTypeId = isset($listingTypeId) ? $listingTypeId : ''; 
    $objPost->price = isset($price) ? $price : ''; 
    $objPost->description = isset($description) ? $description : ''; 
    $objPost->isActive = isset($isActive) ? $isActive : 'n';
    $objPost->classifiedOrauction = isset($classifiedOrauction) ? $classifiedOrauction : 'n';
    if ($type == 'edit' && $id > 0) {
        if (in_array('edit', $Permission)) {
            $objPostArray = (array) $objPost;
            $db->update($table, $objPostArray, array("feesId" => $id));

            $activity_array = array("id" => $id, "module" => $module, "activity" => 'edit');
            add_admin_activity($activity_array);

            $response['status'] = true;
            $response['success'] = "Fees updated successfully.";

            $_SESSION["toastr_message"] = disMessage(array('type' => 'suc', 'var' => 'Fees updated successfully.'));
            
            echo json_encode($response);
            exit;
        } else {
            $response['error'] = "You don't have permission to edit Fees";
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
            $response['success'] = "Fees added successfully";
            echo json_encode($response);
            exit;
        } else {
            $response['error'] = "You don't have permission to add Fees";
            echo json_encode($response);
            exit;
        }
    }
    
}
$objContent = new Fees($module);
$pageContent = $objContent->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");
