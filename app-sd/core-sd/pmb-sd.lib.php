<?php

class pmb extends Home{
	function __construct($module = "", $slug = 0, $id="",$token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->slug = $slug;
        $this->id = $id;
	}

  	public function getPageContent()
  	{
  		$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/pmb-sd.skd");
        $sub_content = $sub_content->compile();
        $firstUser = ($this->userlist('1') != '') ? $this->userlist('1') : '';
        $checkUser = ($this->id!='') ? $this->id : $firstUser;
        $firstUserName = ($checkUser!='') ? getUserDetails('GROUP_CONCAT(firstName," ",lastName)',$checkUser) : '';
        $show_input = empty($checkUser)?"hide":"";
  		return str_replace(array("%USER_LIST%","%MSG_LIST%","%CHAT_USER%","%FIRSTUSER%","%USER_ID%","%SHOW_INPUT_MSG%"), array($this->userlist('',$checkUser),$this->message_list($checkUser),$this->chatUserDetail($checkUser),$firstUserName,$checkUser,$show_input), $sub_content);
    }

    public function userlist($firstUser='',$checkUser='')
    {
        $userlist = $this->getUsers();
        $userlist = !empty($userlist) ? $userlist : 0;
        if($firstUser!='')
        {
        	$user_detail = $this->db->pdoQuery("select * from tbl_users where id IN(".$userlist.") ")->result();
        	$data = $user_detail['id'];
        }
        else
        {
	        if($userlist!='')
	        {
		   		$user_detail = $this->db->pdoQuery("select * from tbl_users where id IN(".$userlist.") ")->results();
		   		$data = '';
		   		$i=1;
		   		foreach ($user_detail as $value) {
			    	$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/userlist-sd.skd");
			        $sub_content = $sub_content->compile();
			        $last_msg = $this->getLastMsg($value['id']);
			        $last_msg_date = "";
			        if(!empty($last_msg['createdDate']) && $last_msg['createdDate']!="0000-00-00 00:00:00"){
			        	$last_msg_date = (date('Y-m-d H:i') == date('Y-m-d H:i',strtotime($last_msg['createdDate']))) ? date('H:i',strtotime($last_msg['createdDate'])) : date('d-m-Y H:i',strtotime($last_msg['createdDate']));
			        }
		   			$arary = array(
		   				"%USER_ID%" => $value['id'],
		   				"%ACTIVE_CLASS%"=> ($checkUser!='') ? (($value['id'] == $checkUser) ? 'msg-active' : '') : ($i==1) ? 'msg-active' : '',
		   				"%USER_IMG%"=> getUserImage($value['id']),
		   				"%USER_NM%"=> filtering(ucfirst($value['firstName']))." ".filtering(ucfirst($value['lastName'])),
		   				"%MSG%"=> $this->message_list($value['id'],1),
		   				"%TIME%"=> $last_msg_date
		   				);
		   			$data .= str_replace(array_keys($arary), array_values($arary), $sub_content);
		   			$i++;
		   		}
	        }
	        else
	        {
	        	$data .= '';
	        }
        }
   		return $data;
    }

    public function getLastMsg($userId){
    	$order = " ORDER by id DESC limit 1";
    	$message = $this->db->pdoQuery("select * from tbl_pmb where ((senderId='".$this->sessUserId."' and ReceiverId='".$userId."') OR (senderId='".$userId."' and ReceiverId='".$this->sessUserId."')) and (NOT FIND_IN_SET(".$this->sessUserId.",deleteUser)) ".$order)->result();
    	if(!empty($message['createdDate'])){
    		$message['createdDate'] = date('Y-m-d H:i',strtotime($message['createdDate']));
    	}
    	return $message;
    }

    public function chatUserDetail($userId)
    {
    	$userDetail = getUser($userId);

    	$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/chatUser-sd.skd");
	    $sub_content = $sub_content->compile();

	    $array = array(
	    	"%ID%" => $userId,
	    	"%NAME%" => filtering(ucfirst($userDetail['firstName']))." ".filtering(ucfirst($userDetail['lastName'])),
	    	"%IMG%" => getUserImage($userDetail['id']),
	    	"%SHOW_IMAGE%" => !empty(getUserImage($userDetail['id']))?"":"hide",
	    	"%PRO_TITLE%" => filtering($userDetail['professionalTitle']),
	    	"%LOCATION%" => filtering($userDetail['location'])
    	);
	    return str_replace(array_keys($array), array_values($array), $sub_content);
    }
    public function getUsers()
    {
    	$userlist = $this->db->pdoQuery("SELECT res.ids
    			FROM (
    				SELECT senderId as ids FROM  `tbl_pmb` where ReceiverId=?
    				UNION ALL
    				SELECT ReceiverId as ids FROM  `tbl_pmb` where senderId=?
    			) as res GROUP BY ids", array($this->sessUserId,$this->sessUserId))->results();
    	$users = "";
    	if(!empty($userlist)){
    		foreach ($userlist as $value) {
    			$users .= $value["ids"].",";
    		}
    		$user_list = rtrim($users,",");
    	}
    	/*if($this->sessUserType == 'Customer')
    	{
    	 	$userlist = $this->db->pdoQuery("select GROUP_CONCAT(distinct(ReceiverId)) As users from tbl_pmb where senderId=? order by id ASC",array($this->sessUserId))->result();
    	}
    	else
    	{
    		$userlist = $this->db->pdoQuery("select GROUP_CONCAT(distinct(senderId)) As users from tbl_pmb where ReceiverId=? order by id ASC",array($this->sessUserId))->result();
    	}
    	return $userlist['users'];*/
    	return $user_list;
    }

    public function message_list($userId,$orderType='')
    {
    	$order = '';
    	if($orderType=='1')
    	{
    		$order = " ORDER by id DESC limit 1";
    	}
		$message = $this->db->pdoQuery("select * from tbl_pmb where ((senderId='".$this->sessUserId."' and ReceiverId='".$userId."') OR (senderId='".$userId."' and ReceiverId='".$this->sessUserId."')) and (NOT FIND_IN_SET(".$this->sessUserId.",deleteUser)) ".$order);
		
		$data = '';
		if($orderType=='1')
		{
			$messages = $message->result();
			$data .= filtering($messages['message']);
	}
		else
		{
			$messages = $message->results();
			if(count($messages)>0)
			{
				foreach ($messages as $value)
				{
					$sub_content = new MainTemplater(DIR_TMPL . $this->module . "/message-sd.skd");
			        $sub_content = $sub_content->compile();

					$array = array(
						"%MSG%" => filtering($value['message']),
						"%MSG_ID%" => filtering($value['id']),
						"%MSG_CLASS%" => ($value['senderId'] == $this->sessUserId) ? 'me' : 'other',
						"%TIME%"=> (date('Y-m-d H:i') == date('Y-m-d H:i:s',strtotime($value['createdDate']))) ? date('H:i',strtotime($value['createdDate'])) : date('d-M-Y H:i',strtotime($value['createdDate']))
						);

					$data .= str_replace(array_keys($array),array_replace($array),$sub_content);
				}
			}
			else
			{
				$data .= "<span class='no-records'><i class='fa fa-exclamation-triangle'></i>".NO_MESSAGE_FOUND."</span>";
			}
		}
		return $data;
    }
}
?>


