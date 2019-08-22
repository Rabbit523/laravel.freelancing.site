<?php
require_once "../../requires-sd/config-sd.php";
$table = "tbl_users";
$id = $_GET['id'];
$count=$db->select($table,array('id'),array("isActive"=>"n","emailVeriStatus"=>'n','id' => $id))->affectedRows();
if($count > 0){
	$db->update($table, array("isActive"=>"y","emailVeriStatus"=>'y'), array("id" => $id));
	$db->insert("tbl_notification_pref",array('user_id' => $id));
	$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOUR_ACCOUNT_ACTIVATED_SUCCESSFULLY)); 	
}
else{
	$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>YOUR_ACCOUNT_IS_ALREADY_ACTIVATED));
}
redirectPage(SITE_URL);
?>