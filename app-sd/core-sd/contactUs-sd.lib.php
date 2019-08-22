<?php
class ContactUs {
	function __construct($module = "", $id = 0, $token = "",$reffToken="") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;

		}
		$this->module = $module;
		$this->id = $id;

	}
	public function getPageContent() {
		$html = new MainTemplater(DIR_TMPL . $this->module . "/" . $this->module . ".skd");
		$html = $html->compile();

		if($this->sessUserId>0){
			$usr = $this->db->select("tbl_users",array("*"),array("id"=>$this->sessUserId))->result();
			$firstName = $usr['firstName'];
			$lastName = $usr['lastName'];
			$email = $usr['email'];
			$location = $usr['location'];
			$contactNo = $usr['contactNo'];
		}else{
			$firstName = "";
			$lastName = "";
			$email ="";
			$location = "";
			$contactNo = "";
		}
		return str_replace(array("%FIRSTNAME%","%LASTNAME%","%EMAIL%","%LOCATION%","%CONTACTNO%"),array($firstName,$lastName,$email,$location,$contactNo), $html);
	}
	public function contactUsSubmit($data){
		extract($_POST);
		if(!empty($chkpoint) && checkToken($chkpoint, 'frmContactUs'))
		{
			$email=isset($email)?$email:'';
			$message=isset($message)? nl2br(filtering($message)):'';
			$location=isset($location)?$location:'';
		    $firstName=isset($firstName)?filtering($firstName):'';
		    $lastName=isset($lastName)?filtering($lastName):'';
		    $contactNo = isset($contactNo)?filtering($contactNo):'';

			if($firstName == "" && $lastName == "" && $email == "" && $location == "" && $message == "")
			{
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=> PLEASE_FILL_ALL_VALUES));
			}
			else if($firstName == "")
			{
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=> PLEASE_ENTER_FIRST_NAME));
			}
			else if($lastName == "")
			{
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>PLEASE_ENTER_YOUR_LAST_NAME));
			}
			else if($email == "")
			{
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>PLEASE_ENTER_EMAIL_ADDRESS));
			}
			else if($location == "")
			{
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>PLEASE_ENTER_LOCATION));
			}
			else if($message == "")
			{
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>PLEASE_ENTER_VALID_MESSAGE));
			}
			else
			{
				$date =  date('Y-m-d H:i:s');
				$ip = get_ip_address();
				if($email != '' && $message != '' && $location != '' && $firstName != '' && $lastName != '')
				{
					if(strlen($email) >50 || strlen($firstName) > 50 || strlen($lastName) > 50 || strlen($message) > 500){
						$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>'Maximum character limit reached'));
					}else
					{
						$insertarray=array("email"=>$email,"message"=>$message,"location"=>$location,"firstName"=>$firstName,"lastName"=>$lastName,"createdDate"=>$date,"ipAddress"=>$ip);
			        	$insert_id=$this->db->insert('tbl_contact_us',$insertarray)->getLastInsertId();
			        	$link = SITE_ADM_MOD.'contactus-sd';
			        	$msg = $firstName." ".$lastName." has contacted you for an inquiry. Waiting for your reply";
			        	$this->db->insert("tbl_notification",array("userId"=>'0',"message"=>$msg,"detail_link"=>$link,"isRead"=>'n',"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')));

			        	$msgType = $_SESSION["msgType"] = ($insert_id > 0) ? disMessage(array('type'=>'suc','var'=>YOUR_INQUIRY_SENT_SUCCESSFULLY.SITE_NM.' - '.ADMIN_LABEL)) : disMessage(array('type'=>'err','var'=>THERE_ARE_SOME_ISSUE_TO_SUBMIT_FORM));
					}
		        }else{
		        	$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>PLEASE_FILL_ALL_VALUES));
		        }
			}
	        redirectPage($_SERVER['HTTP_REFERER']);
		}else {
				$msgType =$_SESSION["msgType"]=disMessage(array('type' => 'err', 'var' => SECURITY_TOKEN_MISMATCH));
				redirectPage($_SERVER['HTTP_REFERER'],'refresh');
		}
	}
}

?>
