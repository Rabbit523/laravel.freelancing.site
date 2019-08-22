<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."freelancer_payment_history-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'freelancer_payment_history-sd';
$mainObj = new FreelancerPaymentHistory($module, '');

$search_array = array();
echo json_encode($return_array);
exit;
?>
