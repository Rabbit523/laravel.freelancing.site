<?php

class PostJob extends Home {
	function __construct($module = "", $id = 0,$token = "",$slug= array()) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
		$this->slug = $slug;
                $this->expLevel = $expLevel;
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
			// echo "asdf";
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

		$deadline_res = getTableValue("tbl_site_settings","value",array("constant"=>"JOB_MAXIMUM_DEADLINE_DAYS"));		
		if($this->slug != '') {
			$jobDetail = $this->db->select("tbl_jobs",array('*'),array('jobSlug'=>$this->slug))->result();

			$profile = new MainTemplater(DIR_TMPL . $this->module . "/post_job_edit-sd.skd");
			$profile = $profile->compile();

			$users = '';
			if($jobDetail['jobType'] == 'pr'){
				$users = $this->db->pdoQuery("select group_concat(id) as id from tbl_job_invitation where jobId =".$jobDetail['id'])->result();
				$users = $users['id'];
			}

			$home_array = array(
				"%JOB_TITLE%" => $jobDetail['jobTitle'],
				"%BUDGET%" => $jobDetail['budget'],
				"%JOB_DESC%" => $jobDetail['description'],
				"%CATEGORY%" => getCategory($jobDetail['jobCategory']),
				"%SUB_CATEGORY%" => getSubcategory($jobDetail['jobCategory'],$jobDetail['jobSubCategory']),
				"%PU_SELECT%" => ($jobDetail['jobType'] == 'pu') ? 'selected' : '',
				"%PR_SELECT%" => ($jobDetail['jobType'] == 'pr') ? 'selected' : '',
				"%SKILLS%" => ($jobDetail['skills']!='') ? $this->skills($jobDetail['skills']) : $this->getSkills(),
				"%EST_OPTIONS%" => $this->estDuration($jobDetail['estimatedDuration']),
	       		"%QUESTION%" => $this->getQuestions($jobDetail['addedQuestion']),
	       		"%SEL_QUESTIONS%" => $this->getSelectedQuestions($jobDetail['addedQuestion']),
	       		"%COUNTRY%" => $this->getCountries($jobDetail['bidsFromLocation']),
	       		"%RANDOM%" => genrateRandom(),
	       		"%USERS%" => $this->getInvitationSuggestion($jobDetail['expLevel'],$users),
	       		"%BIDDING_DATE%" => date('d-m-Y',strtotime($jobDetail['biddingDeadline'])),
	       		"%FEATURED%" => ($jobDetail['featured'] == 'y') ? 'checked' : '',
	       		"%HIDE_SEARCH%" => ($jobDetail['hideFrmSearch'] == 'y') ? 'checked' : '',
	       		"%FILES%" => $this->getJobFiles($jobDetail['id']),
	       		"%JOB_ID%" => $jobDetail['id'],
	       		"%FEATURE_HIDE%" => ($jobDetail['featured']=='y') ? 'hide' :'',
	       		"%SEL_VAL_LEVEL%" => $jobDetail['expLevel'],
	       	);

       		$result = str_replace(array_keys($home_array),array_values($home_array),$profile);
		} else {
			$profile = new MainTemplater(DIR_TMPL . $this->module . "/".$this->module.".skd");
			$profile = $profile->compile();
			$home_array = array(
	       		"%CATEGORY%" => getCategory(),
	       		"%SKILLS%" => $this->getSkills(),
	       		"%QUESTION%" => $this->getQuestions(),
	       		"%COUNTRY%" => $this->getCountries(),
	       		"%RANDOM%" => genrateRandom(),
	       		"%USERS%" => $this->getInvitationSuggestion("b"),
	       		"%MAX_DEADLINE_DAYS%" => $deadline_res,
	       		"%MIN_DATE%" => DATE("d-m-Y"),
	       		"%MAX_DATE%" => DATE("d-m-Y",strtotime("+30 days")),
	       	);
       		$result = str_replace(array_keys($home_array),array_values($home_array),$profile);
		}
		return $result;
	}

	public function getInvitationSuggestion($expLevel,$list=""){
        
		if($expLevel=="b"){
			$userExpLevel = "f";
		}else if($expLevel=="i"){
			$userExpLevel = "i";
		}else if($expLevel=="p"){
			$userExpLevel = "e";
		}
		$users = $this->db->pdoQuery('SELECT email,userName,id FROM tbl_users WHERE userType="F" and freelancerLvl=?  and isActive=?',array($userExpLevel,"y"))->results();
		$content = '';
		if(!empty($users)){
			foreach ($users as $key => $value) {                            
				$string = $list; $selected ='';
	            $what_to_find = $value['user_id'];
	            if (preg_match('/\b' . $what_to_find . '\b/', $string)) {
	                $selected = "selected";
	            }
				$content .="<option value='".$value['user_id']."' ".$selected." >".$value['userName']."</option>";
			}
		}
		return $content;
	}


	public function getSkills(){
		$skills = $this->db->select('tbl_skills',array('id','skill_name'),array('isActive'=>'y','isDelete'=>'n'))->results();
		$skill_names = '';

		foreach ($skills as $key => $value) {
			$skill_names .= "<option value='".$value['id']."'>".$value['skill_name']."</option>";
		}
		return $skill_names;
	}

	public function getQuestions($editData=''){
		$que_data = new MainTemplater(DIR_TMPL . $this->module . "/questions-sd.skd");
		$que_data = $que_data->compile();

		$questions = $this->db->select('tbl_question',array('id','question'),array('isActive'=>'y'))->results();
		$result = '';
		$findQuestion = ($editData!='') ? explode(",",$editData) : array('0');

		foreach ($questions as $key => $value) {
			$data_array = array(
       			"%ID%" => $value['id'],
       			"%QUESTIONS%" => $value['question'],
       			"%QUE_NO%" => $key + 1,
       			"%CHECK_STATUS%" => (in_array($value['id'],$findQuestion)) ? 'checked' :''
       		);
       		$result .= str_replace(array_keys($data_array),array_values($data_array),$que_data);
		}
		return $result;
	}

	public function submitJobContent($data)
	{
		extract($data);

		$objPost = new stdClass();
		$id = $this->sessUserId;
		$objPost->jobTitle = isset($jobTitle) ? filtering($jobTitle) : '';
		$objPost->jobCategory = isset($jobCategory) ? filtering($jobCategory) : '';
		$objPost->jobSubCategory = isset($jobSubCategory) ? filtering($jobSubCategory) : '0';
		$objPost->budget = isset($budget) ? filtering($budget) : '';
		$objPost->expLevel = isset($expLevel) ? filtering($expLevel) : '';
		$objPost->description = isset($description) ? filtering($description) : '';
		$objPost->estimatedDuration = isset($estimatedDuration) ? filtering($estimatedDuration) : '';
		$objPost->jobType = isset($jobType) ? filtering($jobType) : '';
		$objPost->bidsFromLocation = isset($bidsFromLocation) ? implode(",",$bidsFromLocation) : '';
		$objPost->biddingDeadline = isset($biddingDeadline) ? date('Y-m-d H:i:s',strtotime($biddingDeadline)) : '';
		$objPost->jobStatus = 'p';
		$objPost->isApproved = (JOB_APP_REQ == 'no') ? 'a' : 'p';
		$objPost->isActive =   (JOB_APP_REQ == 'no') ? 'y' : 'n';
		$objPost->featured = isset($featured) ? 'y' : 'n';
		$objPost->hideFrmSearch = isset($hideFrmSearch) ? 'y' : 'n';
		$objPost->posterId = $this->sessUserId;
		$jobSlug = '';
		if($objPost->featured=='y' && $featuredDuration>0){
			$objPost->featuredDuration = $featuredDuration;
			$objPost->featuredDate = date("Y-m-d H:i:s");
		}

		if(isset($skill))
		{
			$objPost->skills = isset($skill) ? implode(",",$skill) : '';
		}

		if(isset($questions)){
			$objPost->addedQuestion = isset($questions) ? implode(",",$questions) : '';
			$objPost->posterId = $this->sessUserId;
		}

		if(isset($type) && $type == 'edit'){
			$jobDetail = $this->db->select('tbl_jobs',array('*'),array('id'=>$jobId))->result();

			$searchHideStatus = isset($hideFrmSearch) ? '1':'0';

			if(!empty($jobDetail['hideFrmSearch'] == 'y') && $searchHideStatus == '0') {
				$fpath = __DIR__."/../robots.txt";
				$file_contents = file_get_contents($fpath);
				$rpString = "";
				$string = "\nDisallow: /units-sd/job_workroom-sd/index.php?slug=".$jobDetail['jobSlug'];
				$file_contents = str_replace($string,$rpString,$file_contents);
				file_put_contents($fpath,$file_contents);
			}

			$objPost->hideFrmSearch = 'n';
			$jobSlug = $jobDetail['jobSlug'];
		} else{
			$jobSlug = Slug('jobSlug',$jobTitle,'tbl_jobs');
			$objPost->jobSlug = $jobSlug;
			$objPost->jobPostDate = date('Y-m-d H:i:s');
		}

		if(isset($hideFrmSearch)){

			/* Hide from search engine */
			$fpath = __DIR__."/../robots.txt";
			$f = fopen($fpath, "a+") or die("Unable to open file!");
			$txt = "\nDisallow: /units-sd/job_workroom-sd/index.php?slug=".$jobSlug;
			fwrite($f, $txt);
			fclose($f);
			$objPost->hideFrmSearch = 'y';
			/* End code */
		}
		$customerDetail = getUser($this->sessUserId);
		$objPostArray = (array)$objPost;

		if(isset($type) && $type == 'edit') {
			$jobId = $jobDetail['id'];
			$jobSlug = $jobDetail['jobSlug'];
			$this->db->update('tbl_jobs',$objPostArray,array('id'=>$jobId));
			$msgUpdate = "updated";
		} else {
			$jobId = $this->db->insert('tbl_jobs',$objPostArray)->getLastInsertId();
			if(JOB_APP_REQ == 'no'){
			$link = SITE_ADM_MOD."manage_jobs-sd/";
				$msg = filtering(ucfirst($customerDetail['firstName']))." ".filtering(ucfirst($customerDetail['lastName']))." has added a new job. ";
				$this->db->insert("tbl_notification",array("userId"=>'0',"message"=>$msg,"detail_link"=>$link,"isRead"=>'n',"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')));
			}
			else{
				$link = SITE_ADM_MOD."manage_job_request-sd/";
				$msg = filtering(ucfirst($customerDetail['firstName']))." ".filtering(ucfirst($customerDetail['lastName']))." has added a new job. Waiting for you to approve. ";
				$this->db->insert("tbl_notification",array("userId"=>'0',"message"=>$msg,"detail_link"=>$link,"isRead"=>'n',"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')));
			}
			$msgUpdate = "posted";
		}

		if(isset($frmToken))
		{
			$files = $this->db->select('tbl_temp_files',array('id','fileName'),array('token'=>$frmToken,'userId'=>$this->sessUserId))->results();
			$objFiles = new stdClass();
			$objFiles->jobId = $jobId;
			foreach ($files as $key => $value) {
				$objFiles->fileName = $value['fileName'];
				$objFiles->createdDate = date('Y-m-d H:i:s');
				$objFileArray = (array) $objFiles;
				$this->db->insert('tbl_job_files',$objFileArray);
			}
			$this->db->delete('tbl_temp_files',array('token'=>$frmToken));
		}

		/* Job Invitation */
		$objInv = new stdClass();
		if(isset($invitations)) {
			$objInv->customerId = $this->sessUserId;
			$objInv->status = 'i';
			$objInv->jobId = $jobId;

			foreach ($invitations as $key => $value) {

				$freelancer_id = $this->db->select("tbl_users",array('id'),array("id"=>$value))->result();
				$objInv->freelancerId = $freelancer_id['id'];
				$objInv->createdDate = date('Y-m-d H:i:s');
				$objFileArray = (array)$objInv;	
				$this->db->insert('tbl_job_invitation',$objFileArray);	

				/*$freelancerDetail = getUser($freelancer_id['id']);
		        $custDetail = getUser($this->sessUserId);
		        $customerName = filtering(ucfirst($custDetail['firstName']))." ".filtering(ucfirst($custDetail['lastName']));
		        $jobDetail = $this->db->pdoQuery("select * from tbl_jobs where id=?",array($jobId))->result();
		        $link = "<a href='".SITE_URL."job/".$jobDetail['jobSlug']."' target='_blank'>Job Link</a>";

		        $msg = "Received private job notification.";
		        $jlink = SITE_URL."job/".$jobDetail['jobSlug'];
		        $this->db->insert("tbl_notification",array("userId"=>$freelancer_id['id'],"message"=>$msg,"detail_link"=>$jlink,"isRead"=>'n',"notificationType"=>'c',"createdDate"=>date('Y-m-d H:i:s')));	

		        $arrayCont = array('HEADING'=>"Job Invitation",'USER'=>$freelancerDetail['userName'],"CUSTOMER_NAME"=>$customerName,"LINK"=>$link);
		        $array = generateEmailTemplate('invite_freelancer_for_job',$arrayCont);
		        sendEmailAddress($freelancerDetail['email'],$array['subject'],$array['message']);*/
			}
		}
		/* End code */
		$adm_approval = "";
		if($msgUpdate=="updated"){
			$adm_approval = "";
		}else{
			$adm_approval = "Your job would be there in Admin's approval";	
		}
		if(JOB_APP_REQ=='no'){
			$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>"Your job ".$msgUpdate." successfully"));
		}else{
			if($objPost->featured=="y"){
				$featured_fees_lbl = "Complete payment procedure from my jobs section to make this job featured.";
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>"Your job ".$msgUpdate." successfully. ".$adm_approval." ".$featured_fees_lbl));
			}
			else{
				$msgType = $_SESSION["msgType"] = disMessage(array('type'=>'suc','var'=>"Your job ".$msgUpdate." successfully. ".$adm_approval));
			}
		}


  		redirectPage(SITE_URL.'c/my-jobs');
	}

	public function getCountries($list='')
	{
		$country = $this->db->select('tbl_country',array('id','country_name'))->results();
		$countries = '';

		foreach ($country as $key => $value) {
			$string = $list; $selected ='';
            $what_to_find = $value['id'];
            if (preg_match('/\b' . $what_to_find . '\b/', $string)) {
                $selected = "selected";
            }

			$countries .= "<option value='".$value['id']."' ".$selected." >".$value['country_name']."</option>";
		}
		return $countries;
	}

	public function getSelectedQuestions($data,$type='',$id=0)
	{
		$que_data = new MainTemplater(DIR_TMPL . $this->module . "/sel_question-sd.skd");
		$que_data = $que_data->compile();
		$result = '';

		// if($type == 'edit'){

		// } else {
		if(!empty($data)){

			$que_content = $this->db->pdoQuery('select id,question from tbl_question where id in('.$data.')')->results();
			$result = "";
			foreach ($que_content as $key => $value) {
				$data_array = array(
	       			"%ID%" => $value['id'],
	       			"%QUESTION%" => $value['question'],
	       			"%QUE_ID%" => $key + 1
	       		);
	       		$result .= str_replace(array_keys($data_array),array_values($data_array),$que_data);
			}
		}
		return $result;
    }

    public function getSelectedUsers($data)
    {
    	$user_data = new MainTemplater(DIR_TMPL. $this->module .'/sel_users-sd.skd');
    	$user_data = $user_data->compile();
    	$resultArr = array();
    	$navailArr = array();
    	$resultStr = "";

    	/*$users = explode(",",$data);*/
    	/*printr($data);
    	die;*/
    	$key_cnt=0;
    	foreach ($data as $key => $user_data_mail) {
    		$user_content = $this->db->pdoQuery('select id,email,userName from tbl_users where id =? and userType =?',array($user_data_mail,'F'))->result();
    		if(!empty($user_content)){
    			$data_array= array(
					"%ID%" => $user_content['id'],
					"%QUESTION%" => $user_content['userName'],
					"%QUE_ID%" => $key_cnt + 1
    			);
    			$key_cnt++;
    			$resultStr .= str_replace(array_keys($data_array),array_values($data_array),$user_data);
    		}
    		else{
    			array_push($navailArr, $user_data_mail);
    		}
    	}
    	$resultArr['pdata']=$resultStr;
    	$resultArr['ndata']=$navailArr;

    	return $resultArr;
    }

    public function getJobFiles($jobId){
    	$file_data = new MainTemplater(DIR_TMPL . $this->module . "/files-sd.skd");
		$file_data = $file_data->compile();

		$result = '';
		$files = $this->db->select('tbl_job_files',array('*'),array('jobId'=>$jobId))->results();
		foreach ($files as $key => $value) {
			$ext = explode(".",$value['fileName']);
			$ext1 = $this->getExtension($ext[1]);
			$attFileName = ATTACHMENT_IMG.$ext1;
			$fileIndex = $key+1;
			$data_array = array(
				"%ATT_SYMBOL%" => $attFileName,
				'%FILE_LINK%' => $value['fileName'],
	       		"%FILE_NAME%" => "File ".$fileIndex,
	       		"%ID%" => $value['id']
	       	);
			$result .= str_replace(array_keys($data_array),array_values($data_array),$file_data);
		}
		return $result;
    }

    public function getExtension($ext){

    	$ext_name = '';

    	switch ($ext) {
	        case 'docx':
	        	$ext_name = 'doc.png';
	        	break;
	        case 'csv':
	       	 	$ext_name = 'csv.png';
	        	break;
	        case 'CSV':
	       	 	$ext_name = 'csv.png';
	        	break;
    		case 'png':
        		$ext_name = 'png.png';
        		break;
        	case 'PNG':
        		$ext_name = 'png.png';
        		break;
	    	case 'jpg':
	        	$ext_name = 'jpg.png';
	        	break;
	        case 'JPG':
	        	$ext_name = 'jpg.png';
	        	break;
	    	case 'jpeg':
	        	$ext_name = 'jpg.png';
	        	break;
	        case 'JPEG':
	        	$ext_name = 'jpg.png';
	        	break;
	        case 'gif':
	        	$ext_name = 'jpg.png';
	        	break;
	        case 'GIF':
	        	$ext_name = 'jpg.png';
	        	break;
	        case 'pdf':
	       	 	$ext_name = 'pdf.png';
	        	break;
	        case 'PDF':
	       	 	$ext_name = 'pdf.png';
	        	break;
	        case 'doc':
	       	 	$ext_name = 'doc.png';
	        	break;
	        case 'DOC':
	       	 	$ext_name = 'doc.png';
	        	break;
		}
		return $ext_name;
    }

	public function submitFiles($data,$token,$extention){
		$objPost = new stdClass();
		$objPost->userId = $this->sessUserId;
		$objPost->token  = $token;
		$table = 'tbl_temp_files';

		$file_data = new MainTemplater(DIR_TMPL . $this->module . "/files-sd.skd");
		$file_data = $file_data->compile();

		$fileName = $_FILES['file']['name'];

		if(!empty($fileName))
		{
			$file_name = uploadFile($_FILES['file'], DIR_JOB_FILES,SITE_JOB_FILES);
            $objPost->fileName = $file_name['file_name'];
        }
		$objPost->CreatedDate = date('Y-m-d H:i:s');

		$objPostArray = (array) $objPost;
		$id = $this->db->insert('tbl_temp_files',$objPostArray)->getLastInsertId();

		$result = $ext_name = "";
		$ext_name = $this->getExtension($extention);

		$attFileName = ATTACHMENT_IMG.$ext_name;

		$data_array = array(
			"%ATT_SYMBOL%" => $attFileName,
       		"%FILE_NAME%" => $fileName,
       		"%ID%" => $id
       	);
		$result = str_replace(array_keys($data_array),array_values($data_array),$file_data);
		echo json_encode($result);
        exit;
	}


	public function skills($list,$limit='')
  	{
    	$data = $user_skill =  "";
  		$query = $this->db->pdoQuery("select * from tbl_skills where isActive='y' and isDelete='n' and isApproved='y' ")->results();
    	foreach ($query as $value)
      	{
        	$string = $list; $selected ='';
          	$what_to_find = $value['id'];
          	if (preg_match('/\b' . $what_to_find . '\b/', $string)) {
            	$selected = "selected";
          	}

        	$user_skill .= "<option value='".$value['id']."' ".$selected.">".$value['skill_name']."</option>";
        }
     	return $user_skill;
    }

  	public function estDuration($sel='')
  	{
  		$st1 = '';
  		$optArray = array('1 day or less','Less than 1 week','1 to 2 weeks','3 to 4 weeks','1 to 6 month','More than 6 month','Ongoing','Not sure');

  		foreach($optArray as $key => $value) {
			$selected = ($value==$sel) ? 'selected' : '';
			$st1 .= "<option ".$selected.">".$value."</option>";
		}

  		return $st1;
 	}

}

?>
