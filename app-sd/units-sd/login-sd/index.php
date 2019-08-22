<?php

$reqAuth = false;
$module = 'login-sd';
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
require_once "../../requires-sd/config-sd.php";
require_once DIR_CLASS."login-sd.lib.php";


if(!empty($_GET['action']) && $_GET['action'] == 'switchprofile' && !empty($_SESSION["pickgeeks_userId"])){

	$cuser = getUser($_SESSION["pickgeeks_userId"]);
	$suser = $db->pdoQuery("select * from tbl_users where email = ? and id != ?",array($cuser['email'],$_SESSION["pickgeeks_userId"]))->result();

	$_SESSION["pickgeeks_userId"] = $suser['id'];
	$_SESSION["pickgeeks_first_name"] = $suser['firstName'];
	$_SESSION["pickgeeks_last_name"] = $suser['lastName'];
	$_SESSION["pickgeeks_userType"] = ($suser['userType'] =='F') ? 'Freelancer' : 'Customer';
	$_SESSION["pickgeeks_userSlug"] = $suser['userSlug'];
	$_SESSION["pickgeeks_email"] = $suser['email'];
	$_SESSION["pickgeeks_userName"] = $suser['userName'];
	$_SESSION["pickgeeks_userClass"] = $suser['userType'] =='F'?"user_freelancer" : "user_customer";
	$_SESSION["userId"] = $suser['id'];
	$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => YOU_HAVE_SUCCESSFULLY_SWITCHED_AS.' '.$_SESSION["pickgeeks_userType"]));
	if($suser['userType'] == 'F'){
            redirectPage(SITE_URL."f/profile");
        }else {
     redirectPage(SITE_URL."profile");
 }
      redirectPage(SITE_URL."profile");

}
//if($suser['userType'] == 'F'){
//    redirectPage(SITE_URL."f/profile/");
//}
// else {
//    redirectPage(SITE_URL."profile/");
//}

if(isset($sessUserId) && $sessUserId>0)
{
	redirectPage(SITE_URL);
}

//$slug = isset($_GET["slug"]) ? base64_decode($_GET["slug"]) : '';

$slug = isset($_GET["slug"]) ? $_GET["slug"] : '';
$action = isset($_GET["action"]) ? $_GET["action"] : '';
$last_page = isset($_GET["last_page"]) ? str_replace('-','/',$_GET["last_page"]) : '';

if($slug!='')
{
	$slug = !empty($_REQUEST['action']) && $_REQUEST['action'] == 'change_password' ? base64_decode($_GET['slug']) : $_GET['slug'];
	//printr($_REQUEST,1);
	slug_avail_check('tbl_users','userslug',$slug);
}


$objPost = new stdClass();
$mainObj = new Login($module,$action,$slug);

$winTitle = "Login ".' - ' . SITE_NM;
$headTitle = "Login ";
$canonicalUrl = SITE_URL."content".$slug."/";
$metaTag = getMetaTags(array("description" => $winTitle, "keywords" => $headTitle,"canonicalUrl"=>$canonicalUrl, "author" => AUTHOR));
if ($_POST && isset($_REQUEST["action"])) 
{
	extract($_REQUEST);

	if ($action == "login") {
		$mainObj->loginSubmit($_REQUEST);
	}  
	else if($action == "forgetPass")
	{
		redirectPage(SITE_URL."forgetPassword");
	}
	else if($action=="forgetPassProcess") 
	{
		$mainObj->forgetPassSubmit($_REQUEST);		
	}
	else if($action == "changePassword")
	{

		$mainObj->changePassword($_REQUEST);		
	}
}

$pageContent = $mainObj->getPageContent();
require_once DIR_TMPL . "compiler-sd.skd";	
?>