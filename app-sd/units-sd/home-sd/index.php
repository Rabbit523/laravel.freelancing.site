<?php

$reqAuth = false;
$module = 'home-sd';
require_once "../../requires-sd/config-sd.php";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
require_once DIR_CLASS."home-sd.lib.php";

extract($_REQUEST);
$winTitle = 'Welcome to ' . SITE_NM;

$headTitle = 'Home';
$metaTag = getMetaTags(array("description" => 'vishal', "keywords" => $headTitle, "author" => AUTHOR));
$obj = new Home($module, 0, issetor($token));

$pageContent = $obj->getPageContent();


/*if(isset($action))
{ 
	if($action=='signin'){
		$chkpoint = $_REQUEST['chkpoint'];

		if(!empty($chkpoint) && checkToken($chkpoint, 'loginForm')) {

			$content=$obj->loginSubmit($_POST);
			 if($content=="success"){
			 	$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => 'Dear User, You have successfully signed in ' . SITE_NM));
			 	if(isset($_REQUEST['redirectPageUrl']) && $_REQUEST['redirectPageUrl']!='')
			 	{
			 		if($_REQUEST['redirectPageUrl'] == 'home')
						redirectPage(SITE_URL);
			 		else
			 			redirectPage(SITE_URL.$_REQUEST['redirectPageUrl']);
			 	}
			 	else
			 	{
			 		($_SERVER['HTTP_REFERER']==SITE_URL)?redirectPage(SITE_URL.'dashboard'):redirectPage($_SERVER['HTTP_REFERER']);
			 	}
			 }
			 else if($content=="invalid"){
				$pageContent .= '<script>$(function(){
					$("#loginModal").modal("show");
					$("#alert_login").removeAttr("style");
					$("#alert_message_login").html("Oops, your sign in credentials are incorrect");
				});</script>';
			 }else if($content=="deactivate"){
			 	$pageContent .= '<script>$(function(){
					$("#loginModal").modal("show");
					$("#alert_login").removeAttr("style");
					$("#alert_message_login").html("Dear User, Please check your email account to confirm");
				});</script>';
			 }else if($content=="status_deactivate"){
			 	$pageContent .= '<script>$(function(){
					$("#loginModal").modal("show");
					$("#alert_login").removeAttr("style");
					$("#alert_message_login").html("Dear User, your account is deactivated by  '. SITE_NM .' , please contact admin of  '. SITE_NM .'  to activate your account.");
				});</script>';
			 }
		}
		else
		{
			$pageContent .= '<script>$(function(){
				$("#loginModal").modal("show");
				$("#alert_login").removeAttr("style");
				$("#alert_message_login").html("Security token mismatch!");
					});</script>';
		}
	}
	else if($action=='register'){
		$chkpoint = $_REQUEST['chkpoint'];
		
		if(!empty($chkpoint) && checkToken($chkpoint, 'frmSignUp')) {
			if(CheckRepeatEntry('tbl_users','createdDate','ipAddress')){
				$content=$obj->signupSubmit($_POST);
				if($content=='success'){
					$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>'You have successfully sign-up in '.SITE_NM.', Please check your mail for activate your account'));
			       redirectPage(SITE_URL);
			   	}else if($content=='empty'){
			        $pageContent .= '<script>$(function(){
					$("#signupModal").modal("show");
					$("#alert_signup").removeAttr("style");
					$("#alert_message_signup").html("Please fill all the values");
						});</script>';
			    }
			}
			else{
				$pageContent .= '<script>$(function(){
					$("#signupModal").modal("show");
					$("#alert_signup").removeAttr("style");
					$("#alert_message_signup").html("Something went wrong, please try again after some time");
						});</script>';
			}
		}
		else
		{
			$pageContent .= '<script>$(function(){
				$("#signupModal").modal("show");
				$("#alert_signup").removeAttr("style");
				$("#alert_message_signup").html("Security token mismatch!");
					});</script>';
		}
	}
	else if($action=="forgot_password"){
		$chkpoint = $_REQUEST['chkpoint'];
		
		if(!empty($chkpoint) && checkToken($chkpoint, 'forgotForm')) {
			$content=$obj->forgotSubmit($_POST);
			if($content=="success"){
				$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => 'Password updated successfully,  Please check your mail for more information'));
			}
			else if($content=="invalid"){
				$pageContent .= '<script>$(function(){
					$("#forgotModal").modal("show");
					$("#alert_forgot").removeAttr("style");
					$("#alert_message_forgot").html("Your account is not active, Please activate your account first");
				});</script>';
			}
		}
		else
		{
			$pageContent .= '<script>$(function(){
				$("#forgotModal").modal("show");
				$("#alert_forgot").removeAttr("style");
				$("#alert_message_forgot").html("Security token mismatch!");
					});</script>';
		}
	} 	else if ($action == "change_password") {
		$id= base64_decode($_REQUEST['id']);
		if(isset($_SESSION['pickgeeks_userId']) && !empty(getTableValue('tbl_users','token',array('id'=>$id))))
		{
			redirectPage(SITE_URL.'account_settings/cpass');
			exit;	
		} else {
			if(!empty(getTableValue('tbl_users','token',array('id'=>$id)))){
				$winTitle = 'Reset Password - ' . SITE_NM;		
				$pageContent .= '
				<script>$(function(){
					$("#change_password").modal("show");
				});</script>
				';
			}else{
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>'Your reset password link has been expired'));
				redirectPage(SITE_URL);
				exit;		
			}
		}		
	}
	 
}
*/
if(isset($action) && $action == "subscribeUser")
{
	$obj->subscriberContentSubmit($_POST);
}

if(isset($action) && $action == "userTypeSubmit")
{
	$obj->userTypeSubmit($_POST);
}
require_once DIR_TMPL . "compiler-sd.skd";
?>