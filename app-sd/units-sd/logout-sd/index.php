<?php
$module = 'logout-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
require_once "../../requires-sd/config-sd.php";

if ($sessUserId > 0) {

	unset($_SESSION["pickgeeks_email"]);
    unset($_SESSION["pickgeeks_userId"]);
	unset($_SESSION["pickgeeks_first_name"]);
	unset($_SESSION["pickgeeks_last_name"]);
	unset($_SESSION["pickgeeks_userType"]);
	unset($_SESSION["pickgeeks_userSlug"]);
	unset($_SESSION["pickgeeks_userName"]);
	unset($_SESSION["userId"]);
	unset($_SESSION['last_page']);
	//success("succLogout");
}

//$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>'Dear user, You have successfully signed out from ' . SITE_NM.'. Please sign in to continue.'));
redirectPage(SITE_URL);
?>
