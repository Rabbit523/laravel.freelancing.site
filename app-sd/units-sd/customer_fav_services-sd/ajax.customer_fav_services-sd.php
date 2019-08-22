<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."customer_fav_services-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = "customer_fav_services-sd";

if($action == "delete_service"){
	$aWhere = array("id" => $_POST['service_id']);
    $affected_rows = $db->delete('tbl_favorite_services',$aWhere)->affectedRows();
    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;
}
if($action == "delete_all"){
    $aWhere = array('customerId'=>$_SESSION['pickgeeks_userId']);
    $affected_rows = $db->delete('tbl_favorite_services',$aWhere)->affectedRows();
    $return_array['type'] = "true";
    echo json_encode($return_array);
    exit;
}


echo json_encode($return_array);
exit;
?>
