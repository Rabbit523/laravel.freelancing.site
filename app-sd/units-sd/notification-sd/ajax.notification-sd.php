<?php

require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."notification-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$return_array='';
if($action == 'delete')
{
	$id=isset($_REQUEST['id'])?$_REQUEST['id']:0;
	$qry=$db->delete('tbl_notification',array('id'=>$id))->affectedRows();
	$return_array= ($qry > 0)?'true':'false';
}
if($action == 'view')
{
	$id=isset($_REQUEST['id'])?$_REQUEST['id']:0;
	$qry =$db->update("tbl_notification",array("isRead"=>'y'),array("id"=>$id))->affectedRows();
	$return_array= ($qry > 0)?'true':'false';
}

echo json_encode($return_array);
exit;
?>