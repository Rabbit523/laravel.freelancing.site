<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."customer_account_setting-sd.lib.php");

$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$id=isset($_GET["id"]) ? $_GET["id"] : (isset($_POST["id"]) ? $_POST["id"] : '');
$affected_rows = array();
$return_array = array();
$module = 'customer_account_setting-sd';
$mainObj = new CustomerAccountSetting($module);

if($action == "varify_new_paypal_email")
{
	if(!empty($_GET['email'])){
		
		//echo $_GET['email'];
		//exit;
		if(!empty($user_deatils)){
			$data_up = array(
				"paypal_email"=>$user_deatils['paypal_email_new'],
				"isPaypalVarified"=>'y',
				"old_paypal_verified"=>'y'
			);
			$db->update("tbl_users",$data_up,array("email"=>$user_deatils['email']));
			$msgType =$_SESSION["msgType"]=disMessage(array('type' => 'suc', 'var' => PAYPAL_EMAIL_UPDATED_SUCCESSFULLY));
			redirectPage(SITE_URL.'c/account-setting','refresh');
		}
	}
	$msgType =$_SESSION["msgType"]=disMessage(array('type' => 'err', 'var' => EMAIL_IS_NOT_VALID));
	redirectPage(SITE_URL.'c/account-setting','refresh');
}

if($action == "verify_paypalId")
{

	if($_POST['paypal_email'] == $_POST['old_paypal_email']){
		$msgType =$_SESSION["msgType"]=disMessage(array('type' => 'suc', 'var' => PAYPAL_EMAIL_UPDATED_SUCCESSFULLY));
		echo "false";
		exit;
	}


	if(!empty($_POST['paypal_password'])){
		$user_deatils = $db->pdoQuery("select * from tbl_users where (email = ?) AND password = ? and is_default_usertype = 'y'",array($_SESSION['pickgeeks_email'],md5($_POST['paypal_password'])))->result();
		if(empty($user_deatils)){
			$msgType =$_SESSION["msgType"]=disMessage(array('type' => 'err', 'var' => PASSWORD_ID__IS_NOT_VALID));
			echo "false";
			exit;
		}
	}else{
		$msgType =$_SESSION["msgType"]=disMessage(array('type' => 'err', 'var' => PASSWORD_IS_NOT_VALID));
		echo "false";
		exit;
	}


	$paypal_email=isset($_POST['paypal_email'])?trim($_POST['paypal_email']):'';
	if(!empty($paypal_email)){
		$ch = curl_init();
		$ppUserID = trim(PAYPAL_USERNAME); //Take it from   sandbox dashboard for test mode or take it from paypal.com account in production mode, help: https://developer.paypal.com/docs/classic/api/apiCredentials/
		$ppPass = trim(PAYPAL_PASSWORD); //Take it from sandbox dashboard for test mode or take it from paypal.com account in production mode, help: https://developer.paypal.com/docs/classic/api/apiCredentials/
		$ppSign = trim(PAYPAL_SIGNATURE); //Take it from sandbox dashboard for test mode or take it from paypal.com account in production mode, help: https://developer.paypal.com/docs/classic/api/apiCredentials/
		$ppAppID = trim(PAYPAL_APP_ID); //if it is sandbox then app id is always: APP-80W284485P519543T
		$sandboxEmail = trim(PAYPAL_EMAIL); //comment this line if you want to use it in production mode.It is just for sandbox mode

		//parameters of requests
		$nvpStr = 'emailAddress='.$paypal_email.'&matchCriteria=NONE';
		//$nvpStr = 'emailAddress='.$emailAddress.'&firstName='.$firstName.'&lastName='.$lastName.'&matchCriteria=NAME';s

		// RequestEnvelope fields
		$detailLevel    = urlencode("ReturnAll");   // See DetailLevelCode in the WSDL for valid enumerations
		$errorLanguage  = urlencode("en_US");       // This should be the standard RFC 3066 language identification tag, e.g., en_US
		$nvpreq = "requestEnvelope.errorLanguage=$errorLanguage&requestEnvelope.detailLevel=$detailLevel";
		$nvpreq .= "&$nvpStr";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

		$headerArray = array(
			"X-PAYPAL-SECURITY-USERID:$ppUserID",
			"X-PAYPAL-SECURITY-PASSWORD:$ppPass",
			"X-PAYPAL-SECURITY-SIGNATURE:$ppSign",
			"X-PAYPAL-REQUEST-DATA-FORMAT:NV",
			"X-PAYPAL-RESPONSE-DATA-FORMAT:JSON",
			"X-PAYPAL-APPLICATION-ID:$ppAppID",
		"X-PAYPAL-SANDBOX-EMAIL-ADDRESS:$sandboxEmail" //comment this line in production mode. IT IS JUST FOR SANDBOX TEST 
	);

		$url="https://svcs.sandbox.paypal.com/AdaptiveAccounts/GetVerifiedStatus";
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
		$paypalResponse = curl_exec($ch);
		//echo $paypalResponse;   //if you want to see whole PayPal response then uncomment it.
		curl_close($ch);

		$data = json_decode($paypalResponse);

		/*print_r($data);
		exit;*/

		if($data->responseEnvelope->ack == "Success")
		{
			
			$link = SITE_URL.'AjaxCAccountSetting?action=varify_new_paypal_email&email='.md5($_POST['paypal_email']);
			$arrayCont = array("activationLink"=>'<a target="_blank" href="'.$link.'">  Verification Link </a>', "username"=> $user_deatils['userName']);
			$array = generateEmailTemplate('paypal_email_change_verification',$arrayCont);

			if(empty($user_deatils['paypal_email'])){
				$data_up = array(
					"paypal_email"=> $_POST['paypal_email'],
					"paypal_email_new"=> $_POST['paypal_email'],
					"isPaypalVarified"=>'y',
					"old_paypal_verified"=>'n'
				);
				sendEmailAddress($_POST['paypal_email'],$array['subject'],$array['message']);

			}else{
				$data_up = array(
					"paypal_email_new"=> $_POST['paypal_email'],
					"isPaypalVarified"=>'y',
					"old_paypal_verified"=>'n'
				);
				sendEmailAddress($email,$array['subject'],$array['message']);
			}

			$db->update("tbl_users",$data_up,array("email"=>$_SESSION['pickgeeks_email']));
			$msgType =$_SESSION["msgType"]=disMessage(array('type' => 'suc', 'var' => PAYPAL_EMAIL_UPDATED_SUCCESSFULLY));
			echo "true";
			exit;
		}else{
			$msgType =$_SESSION["msgType"]=disMessage(array('type' => 'err', 'var' => EMAIL_IS_NOT_VALID));
			echo "false";
			exit;
		}
		$status = ($data->responseEnvelope->ack == "Failure") ?  'false' : 'true' ;

		echo $status;
		exit;
	}
}
if($action == "change_status")
{
	$mainObj->changeStatus($_REQUEST);
	echo json_encode($return_array);
	exit;
}

echo json_encode($return_array);
exit;
?>
