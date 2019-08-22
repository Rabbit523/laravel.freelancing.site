<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."customer_financial_dashboard-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'customer_financial_dashboard-sd';
$mainObj = new CustomerFinancialDashboard($module);

$return_array = array();
if($action=="updateChart"){
	$realUserId = $_REQUEST["realUserId"];
	$oppositeUserId = $_REQUEST["oppositeUserId"];
	$type = !empty($_REQUEST["type"])?$_REQUEST["type"]:"month";
	if(!empty($realUserId) && !empty($oppositeUserId) && !empty($type)){
		$data = $mainObj->getChartData($realUserId,$oppositeUserId,$type,'y');
		$return_array["status"]="success";		
		$return_array["content"]=$data;		
	}else{
		$return_array["status"]="fail";		
	}
}

echo json_encode($return_array);
exit;
?>
