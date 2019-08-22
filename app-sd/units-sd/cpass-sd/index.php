<?php

$reqAuth = false;
$module = 'cpass-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##login-sd';
require_once "../../requires-sd/config-sd.php";
require_once DIR_CLASS."cpass-sd.lib.php";

extract($_REQUEST);

$winTitle = 'Reset Password - ' . SITE_NM;
$headTitle = 'Reset Password - '. SITE_NM;
$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle, "author" => AUTHOR));
/*$id = $_GET['id'];*/
$obj = new cPass($module);
if(isset($_POST['submitdata']) && $_POST['submitdata']=="submitdata")
{
	$obj->cPassSubmit($_POST);
}


$pageContent = $obj->getPageContent();
require_once DIR_TMPL . "compiler-sd.skd";
?>