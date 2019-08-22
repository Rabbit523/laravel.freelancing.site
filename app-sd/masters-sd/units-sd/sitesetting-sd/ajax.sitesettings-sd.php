<?php
require_once("../../../requires-sd/config-sd.php");
if ($adminUserId == 0) {
    die('Invalid request');
}
include(DIR_ADMIN_CLASS."sitesetting-sd.lib.php");

$module = 'sitesetting-sd';
chkPermission($module);
$Permission = chkModulePermission($module);
$table = 'tbl_site_settings';
$action = isset($_GET["action"]) ? trim($_GET["action"]) : (isset($_POST["action"]) ? trim($_POST["action"]) : 'datagrid');
$id = isset($_GET["id"]) ? trim($_GET["id"]) : (isset($_POST["id"]) ? trim($_POST["id"]) : 0);
$constant = isset($_REQUEST["constant"]) ? trim($_REQUEST["constant"]) : '';
$page_no = isset($_POST['iDisplayStart']) ? intval($_POST['iDisplayStart']) : 0;
$rows = isset($_POST['iDisplayLength']) ? intval($_POST['iDisplayLength']) : 25;
$sort = isset($_POST["iSortTitle_0"]) ? $_POST["iSortTitle_0"] : NULL;
$order = isset($_POST["sSortDir_0"]) ? $_POST["sSortDir_0"] : NULL;
$chr = isset($_POST["sSearch"]) ? $_POST["sSearch"] : NULL;
$sEcho = isset($_POST['sEcho']) ? $_POST['sEcho'] : 1;

extract($_GET);
$searchArray = array("page" => $page_no, "rows" => $rows, "sort" => $sort, "order" => $order, "offset" => $page_no, "chr" => $chr, 'sEcho' => $sEcho);
if ($action=="delete_image"){
    if(!empty($constant)){
        $image = $db->select($table,array("value"),array("constant" => $constant));
        $db->update($table,array("value"=>""),array("constant" => $constant));        
        unlink(DIR_IMG . $setrow["value"]);
        $array["status"] = true;
        echo json_encode($array);
        exit;
    }
} 

$mainObject = new FAQ($module, $id, NULL, $searchArray, $action);
extract($mainObject->data);
echo ($content);
exit;
