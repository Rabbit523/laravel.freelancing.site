<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."service_confirmation_order-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'service_confirmation_order-sd';
$mainObj = new ConfirmServiceOrder($module,'');

$search_array = array();

if($action == "cancelOrder")
{
	$db->delete("tbl_services_order_temp",array("id"=>$_REQUEST['id']));
	$return_array['content'] = $mainObj->order_loop();
	$return_array['finalPrice'] = $mainObj->finalPrice();
	echo json_encode($return_array);
	exit;
}
if($action == "cancelAddOn")
{
	$order_detail = $db->pdoQuery("select addOn from tbl_services_order_temp where id=?",array($_REQUEST['oId']))->result();	
	$final_addOn = removeFromString($order_detail['addOn'],$_REQUEST['id']);

    if($final_addOn=='')
    {
		$db->update("tbl_services_order_temp",array("addOn"=>''),array("id"=>$_REQUEST['oId']));
    }
    else
    {
    	$db->update("tbl_services_order_temp",array("addOn"=>$final_addOn),array("id"=>$_REQUEST['oId']));
    }
    $addOnPrice = $db->pdoQuery("select * from tbl_services_addon where id=?",array($_REQUEST['id']))->result();
    $db->pdoQuery("update tbl_services_order_temp set totalPayment = totalPayment - '".$addOnPrice['addonPrice']."' where id=?",array($_REQUEST['oId']));
	$return_array['content'] = $mainObj->order_loop();
	$return_array['finalPrice'] = $mainObj->finalPrice();
	$return_array['finalDays'] = $mainObj->finaldays();
	echo json_encode($return_array);
	exit;
}
if($action == "update_qty")
{
	$qty = !empty($_POST['qty']) ? $_POST['qty'] : 1;
	if(!is_numeric($qty)){
		$qty = 1;
	}	

	$db->pdoQuery("update tbl_services_order_temp set quantity = ?  where id=?",array($qty,$_REQUEST['oId']));

	$db->pdoQuery("update tbl_services_order_temp set totalPayment = ? , totalDuration = ? where id=?",array($mainObj->finalPrice(),$mainObj->finalDays(),$_REQUEST['oId']));


	$return_array['content'] = $mainObj->order_loop();
	$return_array['finalPrice'] = $mainObj->finalPrice();

	echo json_encode($return_array);
	exit;
}
if($action == "checkForPayment")
{
	$price = $_REQUEST['price'];
	$userWalletAmount = finalWalletAmount($sessUserId);
	if($userWalletAmount > $price)
	{
		$return_array['permission'] = 'y';
	}
	else
	{
		$return_array['permission'] = 'n';
	}
}
echo json_encode($return_array);
exit;
?>
