<?php

$reqAuth = false;
$module = 'signup-sd';
require_once "../../requires-sd/config-sd.php";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
require_once DIR_CLASS."signup-sd.lib.php";

extract($_REQUEST);
$winTitle = 'Welcome to ' . SITE_NM;

$headTitle = 'Sign Up';
$metaTag = getMetaTags(array("description" => 'vishal', "keywords" => $headTitle, "author" => AUTHOR));

$Signuptype = (isset($_REQUEST['type']) && $_REQUEST['type']!='') ? $_REQUEST['type'] : 'common';
$slug = (isset($_REQUEST['slug']) && $_REQUEST['slug']!='') ? $_REQUEST['slug'] : '';
$obj = new Signup($module, 0, issetor($token),$Signuptype);
$pageContent = $obj->getPageContent();

if(isset($_POST['action']) && $_POST['action']=="submitAddForm")
{
	
	if(CheckRepeatEntry('tbl_users','createdDate','ipAddress','id'))
	{
		
		$obj->signUpSubmit($_POST,$_FILES);
	}
	else
	{
		$_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>"Something went wrong, please try again after some time"));
		redirectPage(SITE_URL.'sign-up');
	}
}

if($slug!='')
{

	$activation = $db->pdoQuery("select * from tbl_users where (userSlug=? or userSlug=? or userSlug=?) ",array($slug,$slug.'-f',$slug.'-c'))->affectedRows();
	
	if($activation!=0)
	{
		$activation = $db->pdoQuery("select * from tbl_users where (userSlug=? or userSlug=? or userSlug=?) and isActive=? ",array($slug,$slug.'-f',$slug.'-c','n'))->affectedRows();
		if($activation!=0)
		{
			//$db->update("tbl_users",array("isActive"=>'y'),array("userSlug"=>$slug));
			$db->update("tbl_users",array("isActive"=>'y'),array("userSlug"=>$slug.'-f'));
			$db->update("tbl_users",array("isActive"=>'y'),array("userSlug"=>$slug.'-c'));
			$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => 'Your Account has been activated successfully'));
			redirectPage(SITE_URL."SignIn");
		}
		else
		{
			$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'war', 'var' => 'Your have already activated your account'));
			redirectPage(SITE_URL."SignIn");
		}

	}
}

require_once DIR_TMPL . "compiler-sd.skd";
?>