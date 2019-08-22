<?php

$reqAuth = true;
$module = 'notification-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
require_once "../../requires-sd/config-sd.php";
require_once DIR_CLASS."notification-sd.lib.php";

extract($_REQUEST);
if(isset($sessUserId) && $sessUserId>0){
	$winTitle = 'Notifications - ' . SITE_NM;
    $db->update('tbl_notification', array('isRead'=>'y'), array("userId"=>$sessUserId));

$headTitle = 'Notifications';
$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));



$obj = new Notification($module);
$pageContent = $obj->getPageContent();

require_once DIR_TMPL . "compiler-sd.skd";
}else {
	$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => PLEASE_SIGN_IN_TO_CONTINUE."!"));
	redirectPage(SITE_URL);
}


?>