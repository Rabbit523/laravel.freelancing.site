<?php
class Signup {
	function __construct($module = "", $id = 0, $token = "",$Signuptype) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
		$this->Signuptype = $Signuptype;
	}

	public function getPageContent() 
	{
		if($this->Signuptype != 'common')
		{
			$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/signup_user-sd.skd");
	       	$sub_content = $sub_content->compile();
	       	return str_replace(array("%USRE_TYPE%"), array($this->Signuptype), $sub_content);
		}
		else
		{
	       	$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/signup-sd.skd");
	       	$sub_content = $sub_content->compile();
		}
      	return $sub_content;
  	}

  	public function signUpSubmit($data)
  	{
  		extract($data);


		  		if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response']))
		  		{
		  			$secret = RECAPTCHA_SECRETKEY;
		  			$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
		  			$responseData = json_decode($verifyResponse);
		  			
        			//if($responseData->success)
        			if(1 == 1)
        			{
				  		$first_name =isset($firstName)?filtering(ucfirst($firstName)):'';
						$last_name =isset($lastName)?filtering($lastName):'';
						$userName =isset($userName)?filtering($userName):'';
						$email =isset($email)?$email:'';
						$password =isset($pwd)? $pwd:'';
						$cPassword =isset($cpwd)?$cpwd:'';
						$location =isset($location)?$location:'';
						$userType = ($userType == 'Hire') ? 'C' : 'F';	
						$createdDate =  date('Y-m-d H:i:s'); 
						$ipAddress = get_ip_address();

						if($email != '' && $password != '')
						{
						    $isExist = $this->db->select('tbl_users',array('id'),array('email'=>$email), ' LIMIT 1');

						    	if($isExist->affectedRows() > 0)
						       	{
						          	$_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>"Email you have entered already exists."));
						            redirectPage(SITE_URL."SignIn");
						        }
						        else
						        {   

						        	//echo "called";
									//die();
						        	$slug = slug('userSlug',$userName,'tbl_users');
						        	$insertarray = 
						        		array
						        		(
							        		"firstName"=> filtering(ucfirst($firstName)),
							        		"lastName" =>  filtering(ucfirst($lastName)),
							        		"userName" =>  filtering(ucfirst($userName)),
							        		"userSlug" =>  $slug.'-'.strtolower($userType),
							        		"email" => $email,
							        		"password" => md5($password),
							        		"createdDate" => $createdDate,
							        		"ipAddress" => $ipAddress,
							        		"location" => $location,
							        		'is_default_usertype' => 'y',
							        		"userType" => $userType,		 
							        		"isActive"=>'n',
							        		"subscribe_email" => ($subscribe=="on") ? 'y' : 'n'
						        		);
						        	//die($insertarray);

						            $insert_id = $this->db->insert('tbl_users',$insertarray)->getLastInsertId();

						            $insertarray['userSlug'] = $slug.'-'.strtolower(($userType == 'F') ? 'C' : 'F');
						            $insertarray['is_default_usertype'] = 'n';
						            $insertarray['userType'] = ($userType == 'F') ? 'C' : 'F';

						            $insert_id2 = $this->db->insert('tbl_users',$insertarray)->getLastInsertId();


						        }
						           
						        $to = $email;
						        $link = SITE_URL."activationLink/".$slug;
						        $activationLink = "<a href='".$link."'>Activation Link</a>";
					            $arrayCont = array('greetings'=> $firstName,'activationLink'=>$activationLink);
					        	$array = generateEmailTemplate('user_register',$arrayCont);
					        	sendEmailAddress($to,$array['subject'],$array['message']);
					        	//Update freelancer free Credit plan
					        	if($userType=="F"){
					        		$res = $this->db->select("tbl_credit_package",array("isActive","noCredits"),array("id"=>1,"price"=>0))->result();
					        		if($res["isActive"]=="y"){
					        			$this->db->insert("tbl_user_plan",array("userId"=>$insert_id,"planId"=>"1","no_credit"=>($res['noCredits']),"used_credit"=>'0',"last_credit"=>"0","subscribedDate"=>date('Y-m-d H:i:s'),"isCurrent"=>'y'));
					        		}
					        	}

					        	if($subscribe=="on")
					        	{
					        		$this->db->insert("tbl_newsletter_subscriber",array("email"=>$email,"subscribed_on"=>date('Y-m-d H:i:s'),"isActive"=>'y',"ipAddress"=>get_ip_address()));
					        	}
					        	/*admin notification*/
					        	if($userType=='C')
					        	{
					        		$msg = filtering(ucfirst($firstName))." ".filtering(ucfirst($lastName))." has joined ".SITE_NM." as Customer.";
					        	}
					        	else
					        	{
					        		$msg = filtering(ucfirst($firstName))." ".filtering(ucfirst($lastName))." has joined ".SITE_NM." as Freelancer.";
					        	}
					        	$this->db->insert("tbl_notification",array("userId"=>'0',"message"=>$msg,'isRead'=>'n',"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')));

						        $msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOU_HAVE_SUCCESSFULLY_SIGN_UP_FOR)); 
						        redirectPage(SITE_URL."SignIn");

						}
						else
						{
						        $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUES));
						            redirectPage(SITE_URL."sign-up");
						}
        			}
        			else
        			{
        				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>ROBOT_VERIFICATION_FAILED)); 
						redirectPage(SITE_URL."SignIn");
        			}
		  		}
		  		else
		  		{
	  				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>PLEASE_CLICK_ON_THE_RECAPTCHA_BOX)); 
					redirectPage(SITE_URL."SignIn");
		  		}
	}

  	

  
    
    
	
}
 ?>