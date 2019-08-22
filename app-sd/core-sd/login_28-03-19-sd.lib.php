<?php
class Login {
	function __construct($module = "",$action="", $slug = 0, $token = "",$reffToken="") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;

		}
		$this->module = $module;
		$this->slug = $slug;
		$this->action = $action;
		
	}
	public function getPageContent() {

		if(isset($this->action) && $this->action=='forgetPass')
		{
			$html = new MainTemplater(DIR_TMPL . $this->module . "/forgetPassword-sd.skd");
        	$html = $html->compile();
        	return $html;
		}
		else if(isset($this->action) && $this->action=='change_password')
		{
			$selQuery = $this->db->pdoQuery("select ChangepasswordRequest from tbl_users where userSlug=?",array($_GET['slug']))->result();
			if($selQuery['ChangepasswordRequest']=='D')
			{
				$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => INVALID_LINK));
				redirectPage(SITE_URL);	
			}
			else
			{
				$html = new MainTemplater(DIR_TMPL . $this->module . "/resetPassword-sd.skd");
	        	$html = $html->compile();
	        	return str_replace(array("%SLUG%"), array($_GET['slug']), $html);
			}
		}
		else
		{
			$html = new MainTemplater(DIR_TMPL . $this->module . "/" . $this->module . ".skd");
	        $html = $html->compile();
	        $email_cookie = ((!empty($_COOKIE['sd_b_email'])) ? trim($_COOKIE['sd_b_email']) : '');
	        $password_cookie = ((!empty($_COOKIE['sd_b_password'])) ? trim($_COOKIE['sd_b_password']) : '');
	        $remember_cookie = ((!empty($_COOKIE['sd_b_rememberme']) && $_COOKIE['sd_b_rememberme']=="y") ? 'checked="checked"' : '');
	        
	        return str_replace(array('%EAMIL_COOKIE%','%PASSWORD_COOKIE%','%COOKIE_CHECKED%'), array($email_cookie,$password_cookie,$remember_cookie), $html);
		}
        
	}
	public function loginSubmit($data)
	{
	//printr($data);exit;
		extract($data);
		$last_page = $_SESSION['last_page'];
		if (isset($email) && isset($password)) {

			$selQuery = $this->db->pdoQuery("select * from tbl_users where (email = ? or userName = ?) AND password = ? and is_default_usertype = 'y'",array($email,$email,md5($password)));
			
			if ($selQuery->affectedRows() >= 1) {
				$result = $selQuery->result();

				if ($result != false) {
					extract($result);
					if (isset($isRemember) && $isRemember == 'on') 
					{
						setcookie('sd_b_email', $email, time() + 3600 * 24 * 30, '/');
						setcookie('sd_b_password', $_REQUEST["password"], time() + 3600 * 24 * 30, '/');
						setcookie('sd_b_rememberme', 'y', time() + 3600 * 24 * 30, '/');
					} else {
						setcookie('sd_b_email', '', time() - 3600, '/');
						setcookie('sd_b_password', '', time() - 3600, '/');
						setcookie('sd_b_rememberme', '', time() - 3600, '/');
					}
					
					if ($isActive == "n") {
						$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => PLEASE_CHECK_YOUR_MAIL_TO_ACTIVATE_YOUR_ACCOUNT));
					} else if ($isActive == "d") {
						$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => PLEASE_CONTACT_TO_ADMIN));
					} else {
						$_SESSION["pickgeeks_userId"] = $id;
						$_SESSION["pickgeeks_first_name"] = $firstName;
						$_SESSION["pickgeeks_last_name"] = $lastName;
						$_SESSION["pickgeeks_userType"] = ($userType=='F') ? 'Freelancer' : 'Customer';
						$_SESSION["pickgeeks_userSlug"] = $userSlug;
						$_SESSION["pickgeeks_email"] = $email;
						
						$_SESSION["pickgeeks_userName"] = $userName;
						$_SESSION["pickgeeks_userClass"] = $userType =='F'?"user_freelancer" : "user_customer";
						$_SESSION["userId"] = $id;
                        $objPost = new stdClass();
                        
                        $objPost->createdDate=date('Y-m-d H:i:s');
                        $objPost->ip=get_ip_address();
                        $objPost->userId=$id;

                   		$objPostArray = (array) $objPost;
                    	$id = $this->db->insert('tbl_login_history',$objPostArray)->getLastInsertId();  

						$currentDate = date('Y-m-d h:i:s'); 
						$setVal = array('lastLogin' => $currentDate);


						//echo $_SESSION["pickgeeks_email"];die;
    					//$this->db->update('tbl_users', $setVal, array("id" => $_SESSION["userId"]));
    					$this->db->update('tbl_users', $setVal, array("email" => $_SESSION["pickgeeks_email"]));
						
						//$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => 'Dear User, You have successfully signed in'));
						
						

						if(isset($last_page) && $last_page!=""){
							redirectPage($last_page);	
						}else{
							redirectPage(SITE_URL);
						}

					}
				} else {
					$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => PASSWORD_DID_NOT_MATCH));
				}
			} else {
				$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => PASSWORD_DID_NOT_MATCH));
			}
		} else {
			$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => SOMETHING_WENT_WRONG));
		}
		if(isset($last_page) && $last_page!=""){
				redirectPage($last_page);	
		}else{
				redirectPage(SITE_URL."SignIn");
		}
	}
	public function forgetPassSubmit($data)
	{
		extract($data);
		$forgot_email = $_POST['reset_pwd_email'];
			$user_deatils = $this->db->select('tbl_users', array('id','userSlug','firstName'), array('email'=>$forgot_email, 'OR LOWER(email)='=>strtolower($forgot_email)))->result();
			if(!empty($user_deatils)) 
			{
				$user_data = $this->db->select('tbl_users', array('id','userSlug','firstName','isActive'), array('email'=>$forgot_email, 'OR LOWER(email)='=>strtolower($forgot_email)))->result();
				if($user_data['isActive']=="y")
				{			
					$objPost = new stdClass();					
					$slug = base64_encode($user_data['userSlug']);
					$password_link = "<a href='".SITE_URL."reset_password/".$slug."'>Click here</a>";
					$arrayCont = array('greetings'=>$user_deatils['first_name'],'PASSWORDLINK'=>$password_link);

					$this->db->update("tbl_users",array("ChangepasswordRequest"=>'A'),array("userSlug"=>base64_decode($slug)));
				    //$this->db->pdoQuery("update tbl_users set ChangepasswordRequest='A' where =?", array($slug));            

				    $array = generateEmailTemplate('forgot_password',$arrayCont);
				    sendEmailAddress($forgot_email, $array['subject'], $array['message']);

				    $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => PLEASE_CHECK_YOUR_MAIL_TO_RESET_PASSWORD_LINK));
				}
				else
				{
					$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => PLEASE_ACTIVATE_YOUR_ACCOUNT_FIRST));
					redirectPage(SITE_URL.'forgetPassword');
				}

			} else {
				$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => INVALID_EMAIL_ADDRESS));
				redirectPage(SITE_URL.'forgetPassword');
			}
			redirectPage(SITE_URL);
	}
	public function changePassword($data)
	{
		extract($data);

		$this->db->pdoQuery("update tbl_users set ChangepasswordRequest=? where userSlug=?", array('D',base64_decode($data['slug']))); 
		$user_data = $this->db->select("tbl_users","*",array("userSlug"=>base64_decode($data['slug'])))->result();

		$old_pwd = $user_data['password'];
		$new_pwd = md5($Cpassword);
		if ($old_pwd == $new_pwd) 
		{
			$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>SAME_AS_OLD_PASSWORD));
			redirectPage(SITE_URL."reset_password/".base64_encode($user_data['userSlug']));
		} 
		else 
		{		 
			if ($password == $Cpassword) 
			{
					/*$cnt = $this->db->update("tbl_users",array('password'=>md5($password)),array("id"=>$id))->showQuery();*/
					$cnt = $this->db->pdoQuery("update tbl_users set password=? where email=?", array(md5($password), $user_data['email']))->affectedRows();
					if ($cnt > 0) {

						$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOUR_PASSWORD_HAS_BEEN_RESET_SUCCESSFULLY));
                        redirectPage(SITE_URL);
						
					}
					else
					{
						$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>SOMETHING_WENT_WRONG));
                        redirectPage(SITE_URL."reset_password/".base64_encode($user_data['userSlug']));
					}
			} 
			else {
				
					$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>CONFIRMED_PASSWORD_DOESNT_MATCH));
                        redirectPage(SITE_URL."reset_password/".base64_encode($user_data['userSlug']));				
                  }
			
		}
	}

}

?>
