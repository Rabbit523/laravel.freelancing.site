<?php

$reqAuth = false;
$module = 'contactus-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
require_once "../../requires-sd/config-sd.php";
require_once DIR_CLASS."contactUs-sd.lib.php";

extract($_REQUEST);
$winTitle = 'Contact Us - ' . SITE_NM;

$headTitle = 'Contact Us';
$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));
$obj = new ContactUs($module, 0, issetor($token));
  
if(isset($_POST['submitCnt']))
{ 
	if(CheckRepeatEntry('tbl_contact_us','createdDate','ipAddress')){
		$obj->contactUsSubmit($_POST);
	}
	else{
		$_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>SOMETHING_WENT_WRONG));
		redirectPage(SITE_URL.'contact-us/');
	}
} 
$pageContent = $obj->getPageContent();

require_once DIR_TMPL . "compiler-sd.skd";
?>