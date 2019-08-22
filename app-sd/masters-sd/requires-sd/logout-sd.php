<?php
require_once("../../requires-sd/config-sd.php");	
if(isset($_SESSION['pickgeeks_adminUserId']) && $_SESSION['pickgeeks_adminUserId'] != "") {

	unset($_SESSION['pickgeeks_adminUserId']);
	unset($_SESSION["sessCataId"]);	
	$_SESSION["sessCataId"] = $_SESSION['pickgeeks_adminUserId'] = '';
	$toastr_message = array('from'=>'admin','type'=>'suc','var'=>'succLogout');
	/*$qry = "UPDATE tbl_admin SET where uName = ?";
	$db->pdoQuery(array('admin'));	*/
	//$db->update("tbl_admin",array("sess_id "=>0),array("id"=>$_SESSION['pickgeeks_adminUserId']));
}
redirectPage(SITE_ADM_MOD.'login-sd/');
?>
