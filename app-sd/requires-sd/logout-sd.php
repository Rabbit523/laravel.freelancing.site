
<?php
require_once("../requires-sd/config.php");	
if(isset($_SESSION['pickgeeks_sessUserId']) && $_SESSION['pickgeeks_sessUserId'] > 0) {
	unset($_SESSION['pickgeeks_adminUserId']);
	unset($_SESSION['pickgeeks_sessUserId']);
	unset($_SESSION['pickgeeks_sesspUserId']);
	//unset($_SESSION["sessUserType"]);
	//unset($_SESSION["portalType"]);
	if(isset($_SESSION['pickgeeks_logout']) && $_SESSION['pickgeeks_logout']!="")
	{
	  redirectPage($_SESSION['pickgeeks_logout']);		
	}
	//session_destroy();
	$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>'You signed out successfully from {SITE_NM}'));
	redirectPage(SITE_URL);
	
}
redirectPage(SITE_URL);
?>
