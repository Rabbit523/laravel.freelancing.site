<?php
require_once("../../requires-sd/config-sd.php");
include(DIR_CLASS."signup-sd.lib.php");
$module = 'signup-sd';
$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : '');
$userId = isset($_GET["userId"]) ? $_GET["userId"] : (isset($_POST["userId"]) ? $_POST["userId"] : 0);
$Signuptype = (isset($_REQUEST['type']) && $_REQUEST['type']!='') ? $_REQUEST['type'] : 'common';
$return_array = array();
$mainObj = new Signup($module, $userId,'',$Signuptype);

if(isset($_GET['email']) && (!empty($_GET['email']))){
	$email=trim(strtolower($_GET['email']));

	$qry=$db->pdoQuery("SELECT id FROM tbl_users where LOWER(email) = '$email'")->affectedRows();
	echo ($qry > 0)?'false':'true';
	exit;
}

if(isset($_GET['userName']) && (!empty($_GET['userName']))){
	$userName=trim(strtolower($_GET['userName']));

	$qry=$db->pdoQuery("SELECT id FROM tbl_users where LOWER(userName) = '$userName'")->affectedRows();
	echo ($qry > 0)?'false':'true';
	exit;
}

if($action == 'facebook-record')
{
	extract($_POST);
	if(!empty($email))
	{
		if($userType!='all')
		{
			$userType = ($userType == 'Hire') ? 'C' : 'F';
			$get_user_details = $db->pdoQuery('SELECT * FROM tbl_users WHERE email = ? and userType=?', array($email,$userType))->result();
		}
		else
		{
			$userType = '';
			$get_user_details = $db->pdoQuery('SELECT * FROM tbl_users WHERE email = ?', array($email))->result();
		}

		if(!empty($get_user_details) && $get_user_details['login_type'] =="Facebook")
		{
			if($get_user_details['isActive']=='n') {
				$return_array['content'] = 'not_active';
				$return_array['type'] ="error";
				$return_array['messages'] = 'Please active your account first to login';	
				$return_array['url'] = SITE_URL."SignIn";				
			}else if ($get_user_details['status'] == "d") {
				$return_array['content'] = 'deactive';
				$return_array['type'] ="error";
				$return_array['messages'] = 'Dear User, your account is deactivated by  '. SITE_NM .', please contact to admin of  '. SITE_NM .'  to activate your account.';
				$return_array['url'] = SITE_URL."SignIn";	
			}
			else if ($get_user_details['isActive'] == 'y') 
			{

				$_SESSION["pickgeeks_userId"] = $get_user_details['id'];
				$_SESSION["pickgeeks_first_name"] = $get_user_details['firstName'];
				$_SESSION["pickgeeks_last_name"] = $get_user_details['lastName'];
				if($get_user_details['userType']!='')
				{
					$_SESSION["pickgeeks_userType"] = ($get_user_details['userType'] == 'C') ? 'Customer' : 'Freelancer';
				}else{
					$_SESSION["pickgeeks_userType"] = 'Customer';
				}
				$_SESSION["pickgeeks_userSlug"] = $get_user_details['userSlug'];
				
				$_SESSION["pickgeeks_userName"] = $get_user_details['userName'];
				$_SESSION["pickgeeks_userId"] = $get_user_details['id'];


				$return_array['userType'] = $_SESSION["pickgeeks_userType"];
				$return_array['userTypeCheck'] = ($get_user_details['userType']=='') ? 'n':'y';
				$return_array['content'] = "success login";
				$return_array['type'] ="success";
				$return_array['messages'] = YOU_ARE_SUCCESSFULLY_LOGGED_IN;
				$return_array['url'] = SITE_URL;

			}
		}
		else if(!empty($get_user_details) && $get_user_details['login_type']!="Facebook")
		{

			$_SESSION["pickgeeks_userId"] = $get_user_details['id'];
			$_SESSION["pickgeeks_first_name"] = $get_user_details['firstName'];
			$_SESSION["pickgeeks_last_name"] = $get_user_details['lastName'];
			if($get_user_details['userType']!='')
			{
				$_SESSION["pickgeeks_userType"] = ($get_user_details['userType'] == 'C') ? 'Customer' : 'Freelancer';
			}else{
				$_SESSION["pickgeeks_userType"] = 'Customer';
			}
			$_SESSION["pickgeeks_userSlug"] = $get_user_details['userSlug'];
			$_SESSION["pickgeeks_userName"] = $get_user_details['userName'];
			$_SESSION["pickgeeks_userId"] = $get_user_details['id'];

			$return_array['content'] = "user_exist";
			$return_array['userType'] = $_SESSION["pickgeeks_userType"];
			$return_array['userTypeCheck'] = ($get_user_details['userType']=='') ? 'n':'y';
			$return_array['type'] ="success";
			$return_array['messages'] =	YOU_ARE_SUCCESSFULLY_LOGGED_IN;	
			$return_array['url'] = SITE_URL;	
			echo json_encode($return_array);	
			exit;		
		}
    	//If not already signup create it
		else
		{	
			$chk=chkeckRepeatLogin();
			if($chk==2){
				$return_array['content'] = "wait";
				echo json_encode($return_array);
				exit;
			}

			$pwd = rand();
			$objPost = new stdClass();
			$objPost->firstName = $firstName;
			$objPost->lastName = $lastName;
			$objPost->userName = $firstName.'-'.$userType;
			$objPost->email = $email;
			$objPost->password = md5(rand());
			$objPost->isActive = 'y';
			$objPost->is_default_usertype = 'y';
			$objPost->userType = $userType;
			$objPost->status = 'a';
			$objPost->ipAddress = get_ip_address();
			$objPost->loginWith = $loginWith;
			$objPost->createdDate = date('Y-m-d H:i:s');
			$objPost->userSlug  = slug('userSlug',$objPost->firstName,'tbl_users').'-'.$userType;
			//printr($objPost,1);exit;
			$id = $db->insert("tbl_users",(array)$objPost)->getLastInsertId();

			$objPost2 = (array)$objPost;
			$objPost2['userSlug'] = $slug.'-'.strtolower(($userType == 'F') ? 'C' : 'F');
			$objPost2['is_default_usertype'] = 'n';
			$objPost2['userType'] = ($userType == 'F') ? 'C' : 'F';
			$id2 = $db->insert("tbl_users",$objPost2)->getLastInsertId();
			
			$_SESSION["pickgeeks_userId"] = $id;
			$_SESSION["pickgeeks_first_name"] = $objPost->firstName;
			$_SESSION["pickgeeks_last_name"] = $objPost->lastName;
			if($userType!='')
			{
				$_SESSION["pickgeeks_userType"] = ($userType == 'C') ? 'Customer' : 'Freelancer';
			}else{
				$_SESSION["pickgeeks_userType"] = 'Customer' ;
			}
			$_SESSION["pickgeeks_userSlug"] = $objPost->userSlug;
			
			$_SESSION["pickgeeks_userName"] = $objPost->userName;
			$_SESSION["pickgeeks_userId"] = $id;

			$arrayCont = array("greetings"=>ucfirst($objPost->firstName), "USERID"=>$email,'PASSWORD'=>$pwd);

			$array = generateEmailTemplate('social_signup',$arrayCont);
			sendEmailAddress($email,$array['subject'],$array['message']);
			//sendEmailAddress($email, 'user_register', $arrayCont);

			$return_array['user_id'] = $id;
			$return_array['userType'] = $userType;
			$return_array['is_register'] = '1';
			$return_array['type'] = 'success';
			$return_array['content'] = "success signup";
			$return_array['userTypeCheck'] = ($userType=='') ? 'n' : 'y';
			$return_array['message'] = YOU_ARE_SUCCESSFULLY_REGISTERED;
			$return_array['url'] = SITE_URL;
		}
	}else{
		$msg = YOUR_FACEBOOK_ACCOUNT_IS_NOT_ASSOCIATED;
		$return_array['type'] = 'error';
		$return_array['message'] = $msg;
		$return_array['url'] = SITE_URL;
		$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => $msg));
	}
	echo json_encode($return_array);
	exit;
}


function chkeckRepeatLogin()
{
	if(CheckRepeatEntry('tbl_users','createdDate','ipAddress','id')){
		return '1';
	}else{
		return '2';
	}
}


if($action == "google_login")
{
	extract($_POST);

	if(!empty($email))
	{
		if($userType!='all')
		{
			$userType = ($userType=='Hire') ? 'C' : 'F';
			$user_avail = $db->pdoQuery('SELECT id, firstName, lastName,userName, isActive,status
				FROM tbl_users WHERE LOWER(email) = ? and userType=? ', array($email,$userType))->affectedRows();

			$user_details = $db->pdoQuery('SELECT id, firstName, lastName,userName, isActive,status
				FROM tbl_users WHERE LOWER(email) = ? and loginWith=?', array($email,'Google'))->result();
		}
		else
		{
			$userType = '';
			$user_avail = $db->pdoQuery('SELECT id, firstName, lastName,userName, isActive,status
				FROM tbl_users WHERE LOWER(email) = ? ', array($email))->affectedRows();
			$user_details = $db->pdoQuery('SELECT id, firstName, lastName,userName, isActive,status
				FROM tbl_users WHERE LOWER(email) = ? and userType=? and loginWith=?', array($email,$userType,'Google'))->result();
		}


			if(!empty($user_details)) 
			{
				if($user_details['isActive']=='n') {
					$return_array['content'] = 'not_active';
					$return_array['type'] ="error";
					$return_array['messages'] = PLEASE_ACTIVE_YOUR_ACCOUNT_FIRST_TO_LOGIN;	
					$return_array['url'] = SITE_URL."SignIn";						
				}else if ($user_details['status'] == "d") {
					$return_array['content'] = 'deactive';
					$return_array['type'] ="error";
					$return_array['messages'] = TO_ACTIVATE_YOUR_ACCOUNT;
					$return_array['url'] = SITE_URL."SignIn";
				}
				else if ($user_details['isActive'] == 'y') 
				{

					$_SESSION["pickgeeks_userId"] = $user_details["id"];
					$id =  $user_details["id"];
					$_SESSION["pickgeeks_first_name"] = $user_details["firstName"];
					$_SESSION["pickgeeks_last_name"] = $user_details["lastName"];
					if($get_user_details['userType']!='')
					{
						$_SESSION["pickgeeks_userType"] = ($userType == 'C') ? 'Customer' : 'Freelancer';
					}else{
						$_SESSION["pickgeeks_userType"] = 'Customer';
					}
					$_SESSION["pickgeeks_userSlug"] = $user_details["userSlug"];
					$_SESSION["pickgeeks_userName"] = $user_details['userName'];
					$_SESSION["pickgeeks_userId"] = $id;


					$db->update('tbl_users',array('lastLogin' => date('Y-m-d H:i:s')),array('email' => $_SESSION['pickgeeks_email']));
					$ip = get_ip_address();
					$db->insert("tbl_login_history",array("userId"=>$_SESSION['pickgeeks_userId'],"ip"=>$ip,"createdDate"=>date('Y-m-d H:i:s')));

					$return_array['userType'] = $_SESSION["pickgeeks_userType"];
					$return_array['userTypeCheck'] = ($get_user_details['userType']=='') ? 'n':'y';
					$return_array['content'] = SUCCESS_LOGIN;
					$return_array['type'] ="success";
					$return_array['messages'] = YOU_ARE_SUCCESSFULLY_LOGGED_IN;
					$return_array['url'] = SITE_URL;
				} 
			}else {
				$objPost = new stdClass();
				$up_password = mt_rand(100000, 999999);

				$objPost->firstName = $first_name;
				$objPost->lastName =  $last_name;
				$objPost->userName = $first_name;
				$objPost->email = $email;
				$objPost->password = md5($up_password);
				$objPost->status = 'a';
				$objPost->isActive = 'y';
				$objPost->is_default_usertype = 'y';
				$objPost->userType = $userType;
				$objPost->loginWith = "Google";
				$objPost->ipAddress = get_ip_address();
				$objPost->createdDate = date('Y-m-d H:i:s');
				$objPost->userSlug  = slug('userSlug',$objPost->firstName,'tbl_users');

				$id = $db->insert('tbl_users', (array)$objPost)->lastInsertId();

				$objPost2 = (array)$objPost;
				$objPost2['userSlug'] = $slug.'-'.strtolower(($userType == 'F') ? 'C' : 'F');
				$objPost2['is_default_usertype'] = 'n';
				$objPost2['userType'] = ($userType == 'F') ? 'C' : 'F';
				$id2 = $db->insert("tbl_users",$objPost2)->getLastInsertId();


				$uid = base64_encode($id);

				$db->update('tbl_users',array('token'=>$uid),array('id'=>$id)); 
				$password_link = "<a href='".SITE_URL."reset_password/".$uid."/'>Click here to Reset Password</a>";
				$arrayCont = array("greetings"=>ucfirst($first_name), "USERID"=>$email,"PASSWORD"=>$up_password);
				$to = $email;
				$array = generateEmailTemplate('social_signup',$arrayCont);

				sendEmailAddress($to,$array['subject'],$array['message']);

				$_SESSION["pickgeeks_userId"] = $user_details["id"];
				$_SESSION["pickgeeks_first_name"] = $objPost->firstName;
				$_SESSION["pickgeeks_last_name"] = $objPost->lastName;
				$_SESSION["pickgeeks_userType"] = ($userType == 'C') ? 'Customer' : 'Freelancer';
				$_SESSION["pickgeeks_userSlug"] = $objPost->userSlug;

				$_SESSION["pickgeeks_userName"] = $objPost->firstName;
				$_SESSION["pickgeeks_userId"] = $id;

				$db->update('tbl_users',array('lastLogin' => date('Y-m-d H:i:s')),array('email' => $_SESSION['pickgeeks_email']));
				$ip = get_ip_address();
				$db->insert("tbl_login_history",array("userId"=>$_SESSION['pickgeeks_userId'],"ip"=>$ip,"createdDate"=>date('Y-m-d H:i:s')));
				$return_array['user_id'] = $id;
				$return_array['userType'] = $userType;
				$return_array['is_register'] = '1';
				$return_array['type'] = 'success';
				$return_array['content'] = "success signup";
				$return_array['userTypeCheck'] = ($userType=='') ? 'n' : 'y';
				$return_array['message'] = YOU_ARE_SUCCESSFULLY_REGISTERED;
				$return_array['url'] = SITE_URL;
			}
		//}
		
	}else{
		$msg = YOUR_GOOGLE_ACCOUNT_IS_NOT_ASSOCIATED;
		$return_array['type'] = 'error';
		$return_array['message'] = $msg;
		$return_array['url'] = SITE_URL;
		$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => $msg));
	}
	echo json_encode($return_array);
	exit;

}

?>