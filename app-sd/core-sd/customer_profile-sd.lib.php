<?php

class CustomerProfile extends Home {
	function __construct($module = "", $id = 0,$slug="") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
		$this->slug = $slug;
	}
	public function getform()
	{
		$content = "";
		$firstName = isset($this->objPost->firstName) ? filtering($this->objPost->firstName) : '';
		$lastName = isset($this->objPost->lastName) ? filtering($this->objPost->lastName) : '';
		$profile_img = isset($this->objPost->profile_img) ? $this->objPost->profile_img : 'th2_no_user_image.png';
		$location = isset($this->objPost->location) ? $this->objPost->location : '';
		$createdDate = isset($this->objPost->createdDate) ? $this->objPost->createdDate : '';
		$aboutme = isset($this->objPost->aboutme) ? filtering($this->objPost->aboutme) : '';
		$contactNo = isset($this->objPost->contactNo) ? filtering($this->objPost->contactNo) : '';
		$email = isset($this->objPost->email) ? filtering($this->objPost->email) : '';
		$gender = isset($this->objPost->gender) ? $this->objPost->gender : 'n';
		$birthDate = isset($this->objPost->birthDate) ? $this->objPost->birthDate : '';
		$HomePageUrl = isset($this->objPost->HomePageUrl) ? filtering($this->objPost->HomePageUrl) : '';
		$gender_m = ($this->gender == 'm' ? 'checked' : '');
		$gender_f = ($this->gender == 'f' ? 'checked' : '');

		$qrySel = $this->db->select("tbl_users",array("*"),array("id"=>$this->UserId))->result();
		$fetchUser = $qrySel;

		if(isset($this->sessUserId) && $this->sessUserType == 'Customer')
		{
			echo "asdf";
			exit();
		}
		else if(isset($this->sessUserId) && $this->sessUserType == 'Freelancer')
		{
			$result = $this->freelancerProfilePage();
		}

		echo $content;
		exit();

	}

	public function getPageContent()
	{
		global $sessUserId,$sessUserType;
		$profile = new MainTemplater(DIR_TMPL . $this->module . "/".$this->module.".skd");
		$profile = $profile->compile();

		if(!empty($this->slug)){
			$resId = getTableValue("tbl_users","id",array("userSlug"=>$this->slug));
		}
		$userId = !empty($this->slug)?$resId:$sessUserId;

		$userDetail = $this->db->select("tbl_users", array("*"), array("id =" => $userId))->result();

		$jobs = $this->db->select("tbl_jobs",array("*"),array(
				"isActive ="=>'y',"and posterId ="=>$userId
				))->affectedRows();
		$currentDate = date('Y-m-j');

		$openJobs = $this->db->pdoQuery("select * from tbl_jobs where isActive = 'y' and posterId =".$userId." and biddingDeadline <= '".$currentDate."'")->affectedRows();

		$openJobs = isset($openJobs) ? $openJobs :'';
		$openJobs = $this->db->select("tbl_jobs",array("*"),array(
				"isActive ="=>'y',"and posterId ="=>$userId,"and $currentDate"
				))->affectedRows();

		$ratings = $this->db->pdoQuery("select avg(reqClarification) as reqCla,avg(onTimePayment) as onTimePymt,avg(onTimeResponse) as onTimeRes,avg(custComm) as comm from tbl_reviews where customerId = ".$userId)->result();

		$reqClarification = ceil($ratings['reqCla']);
		$onTimePayment = ceil($ratings['onTimePymt']);
		$onTimeResponse = ceil($ratings['onTimeRes']);
		$communication = ceil($ratings['comm']);
		$overview = (($reqClarification + $onTimePayment + $onTimeResponse + $communication)/4);
		$totalAmount = customerSpentAmount($userId);
		extract($userDetail);
		$date = date_create($lastLogin);
		$lastSeen = getTime($lastLogin);
		$img = ($profileImg!='') ? $profileImg : "no_user_image.png";

		$date = date('M, Y',strtotime($createdDate));
		$home_array = array(
			"%SUB_HEADER_CONTENT%" => customerSubHeaderContent("profile"),
       		"%USERNAME%" => $firstName.' '.$lastName,
       		"%FIRSTNAME%" => $firstName,
       		"%LASTNAME%" => $lastName,
       		"%LOCATION%" => $location,
       		"%JOINED_DATE%" => $date,
       		"%ABOUT_ME%" => ($aboutme == '') ? '-' : $aboutme,
       		"%JOBS%" => $jobs,
       		"%OPEN_JOBS%" => $openJobs,
       		"%IMG%" => SITE_USER_PROFILE.$img,
       		"%USER_IMAGE_VISIBILITY%" => empty($profileImg) ? ' style="display:none" ' :  '',
       		"%WALLET_AMOUNT%" => $walletAmount."<span>".CURRENCY_SYMBOL."</span>",
       		"%LAST_LOGIN%" => $lastSeen,
       		"%TOTAL_AMOUNT%" => $totalAmount,
       		"%OVERVIEW%" => (ceil($overview)*20),
       		"%ON_TIME_PAYMENT%" => ($onTimePayment*20),
       		"%REQ_CLARIFICATION%" => ($reqClarification*20),
       		"%COMMUNICATION%" => ($communication*20),
       		"%ON_TIME_RESPONSE%" => ($onTimeResponse*20),
       		"%FR_SWITCH_URL%" => $sessUserType=="Customer"?SITE_URL."switchprofile/":"javascript:void(0)",
            "%CS_SWITCH_URL%" => $sessUserType=="Freelancer"?SITE_URL."switchprofile/":"javascript:void(0)",
            "%EDIT_IMAGE%" => !empty($this->slug)?"hide":"",
            "%EDIT_DETAILS%" => !empty($this->slug)?"hide":"",
            "%EDIT_SWAP%" => !empty($this->slug)?"hide":"",
            "%VIEW_MORE_LI%" => !empty($this->slug)?"hide":"",
                    "%EMAIL%"=>$email,
       		);

       	$result = str_replace(array_keys($home_array),array_values($home_array),$profile);
		return $result;
	}

	public function paymentHistory(){
		echo $_SESSION['pickgeeks_userId'];

	}

	public function submitProcedure($data,$files)
	{
		extract($data);

		$id = $this->sessUserId;
		$objPost->firstName = isset($firstName) ? filtering($firstName) : '';
		$objPost->lastName = isset($lastName) ? filtering($lastName) : '';
		$objPost->location = isset($location) ? filtering($location) : '';
		$objPost->aboutme = isset($aboutme) ? filtering($aboutme) : '';

		if(!empty($_FILES['profile_image']['name'])){
			$file_name = uploadFile($_FILES['profile_image'], DIR_USER_PROFILE, SITE_USER_PROFILE);
            $objPost->profileImg = $file_name['file_name'];
		}
		$objPostArray = (array) $objPost;
		$email = getTableValue("tbl_users","email",array("id"=>$id));
		$this->db->update("tbl_users", $objPostArray, array("email" => $email));

		$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>YOUR_PROFILE_HAS_BEEN_UPDATED_SUCCESSFULLY));
        redirectPage(SITE_URL."profile");
	}
}
?>
