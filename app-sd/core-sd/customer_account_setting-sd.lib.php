<?php

class CustomerAccountSetting {
	function __construct($module = "", $id = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}
	
  	public function getPageContent() 
  	{
  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/customer_account_setting-sd.skd");
        $sub_content = $sub_content->compile();

        $user_detail = $this->db->pdoQuery("select * from tbl_users where id=?",array($this->sessUserId))->result();

        $array = array(
        	"%PAYPAL_EMAIL%" =>  $user_detail['paypal_email'],
        	"%NEWSLETTER%" =>  ($user_detail['subscribe_email']=='y') ? 'checked' : '',
        	"%JOB_STATUS%" =>  ($user_detail['NotifyJobAcceptReject']=='y') ? 'checked' : '',
        	"%BID_STATUS%" =>  ($user_detail['NotifyFreelancerBidOnJob']=='y') ? 'checked' : '',
        	"%REVIEW_STATUS%" =>  ($user_detail['NotifyFreelancerReview']=='y') ? 'checked' : '',
        	"%MILESTONE_STATUS%" =>  ($user_detail['NotifyMilestoneAcceptReject']=='y') ? 'checked' : '',
        	"%WORKROOM_STATUS%" =>  ($user_detail['NotifyWorkRRoomMsg']=='y') ? 'checked' : '',
        	"%PAYPAL_CONTENT%" => ($user_detail['old_paypal_verified'] =='y') ? "<span class='green'>Verified</span> 
        	<span><i class='fa fa-check-circle'></i></span>" : "<span class='error'>Not Verified yet. Please check ".$user_detail['paypal_email']."</span>");

  		return str_replace(array_keys($array), array_replace($array), $sub_content);
    }
  
  	public function changePassword($data)
  	{
  		extract($data);
		if ($currentPassword == $password) {
			$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>SAME_AS_OLD_PASSWORD));
			redirectPage(SITE_URL."c/account-setting");
			
		} 
		else {
				$password_detail = $this->db->pdoQuery("select * from tbl_users where id=? and password=?",array($this->sessUserId,md5($currentPassword)))->affectedRows();
				if($password_detail==0)
				{
					$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'err','var'=>PLEASE_ENTER_CORRECT_CURRENT_PASSWORD));
					redirectPage(SITE_URL."c/account-setting");
				}
				else
				{
					$cnt = $this->db->pdoQuery("update tbl_users set password=? where id=?", array(md5($password), $this->sessUserId))->affectedRows();
					if ($cnt > 0) 
					{
						$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>PASSWORD_HAS_BEEN_UPDATED_SUCCESSFULLY));
					}
					redirectPage(SITE_URL."c/account-setting");
				}
			
		}
  	}
  	public function changeStatus($data)
  	{
  		extract($data);
  		$this->db->update("tbl_users",array($type => $status),array("id" => $this->sessUserId));
  	}
  
}
 ?>


