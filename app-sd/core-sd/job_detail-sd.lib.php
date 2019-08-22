<?php
class JobDetail extends Home {
	function __construct($module = "", $slug = 0, $token = "") {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->slug = $slug;
	}

	public function getPageContent()
	{
		global $sessUserId,$sessUserType;

		$userId = $this->sessUserId;
		$profile = new MainTemplater(DIR_TMPL . $this->module . "/".$this->module.".skd");
		$profile = $profile->compile();
		$job = $this->db->pdoQuery("select j.*,j.id as jobId,c.".l_values('category_name')." as category_name ,s.".l_values('subcategory_name')." as subcategory_name,u.*,u.id as user_id,u.createdDate AS joinDate,jb.isHired,jb.userId
                from tbl_jobs as j
                LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
                LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
                LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
                LEFT JOIN tbl_users As u ON u.id = j.posterId
                where j.jobSlug = '".$this->slug."' and j.isActive='y' and j.isDelete='n' and u.isActive='y' and u.isDeleted ='n' ")->result();
		if(empty($job)){
			$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => JOB_NOT_AVAILABLE));
			redirectPage(SITE_URL);
		}else if($job["jobType"]=="pr" && $job["isApproved"]!="a"){
			$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => JOB_NOT_ACTIVE));
			redirectPage(SITE_URL);
		}
		$walletAmount = $job['walletAmount'];

		$hired_in_status = $this->db->pdoQuery("select j.id as job_id, jb.isHired from tbl_jobs as j
			LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
			where j.jobSlug = '".$this->slug."'	and (jb.isHired='a' || jb.isHired = 'y')")->affectedRows();

		if($hired_in_status>0){
			$hired_in_status = 'y';
		}
		$skills = get_skill($job['skills']);
		$totalSpentAmount = customerSpentAmount($job['posterId']);
		$currentDate = date('Y-m-d');
		// $openJobs = $this->db->select("tbl_jobs",array("*"),array("isActive ="=>'y',"and posterId ="=>$job['posterId'],"and $currentDate"))->affectedRows();

		$openJobs = $this->db->pdoQuery("SELECT id FROM tbl_jobs WHERE isActive='y' AND posterId=? AND DATE(biddingDeadline) >= ?",array($job['posterId'],$currentDate))->affectedRows();
		
		$postedJob = $this->db->select("tbl_jobs",array("*"),array("posterId ="=>$job['posterId']))->affectedRows();
		$userImage = getUserImage($job['posterId']);
		$date = date('M, Y',strtotime($job['joinDate']));

		$remain_days = get_time_diff(date('Y-m-d H:i:s',strtotime($job['biddingDeadline'])));
		$files = $this->getFiles($job['jobId']);
		$review = (getAvgUserReview($job['posterId'],"C")*20);


		$que = ($job['addedQuestion']!='') ? $this->getQuestion($job['addedQuestion']) : '';
		$bidHide = $this->bidHide($job['jobId']);
		$credit = getCredits($this->sessUserId);
		$total_saved_jobs = 0;
		if(isset($this->sessUserId) && $this->sessUserId>0)
		{
			$query = $this->db->pdoQuery("select * from tbl_saved_job where jobId=? and userId=?",array($job['jobId'],$this->sessUserId))->affectedRows();
			$total_saved_jobs = $query;
			if($query>0)
			{
				$save_job_class = "fa fa-heart";
			}
			else
			{
				$save_job_class = "fa fa-heart-o";
			}
		}
		else
		{
			$save_job_class = "fa fa-heart-o";
		}
		$job_hide = '';
		if(isset($_SESSION["pickgeeks_userType"]))
		{
			$job_hide = ($_SESSION["pickgeeks_userType"] == "Customer") ? 'hide':'';
		}

		$udata = getUser($this->sessUserId,'id,freelancerLvl');
		$warning = '';

		if(!empty($udata['freelancerLvl']) && $job['expLevel'] == 'p'){
			if($udata['freelancerLvl'] != 'e'){
				$warning = '<p style="color:red"> Job criteria is not matching with your profile. </p> ';
			}
		}

		$uid = !empty($this->sessUserId) ? $this->sessUserId : 0;
		$applied = $this->db->pdoQuery('SELECT * FROM `tbl_job_bids` WHERE jobid = ? AND userId = ?',[$job['jobId'],$uid])->affectedRows();
		$already_applied = $applied > 0 ? 'already_applied' : '';
		
		if($_SESSION["pickgeeks_userType"]=="Freelancer"){
			$review_link = SITE_URL."customer_review_all/".$job['posterId'];		
		}else{
			$review_link = SITE_URL."c/review";
		}
		$no_of_applicant = job_applicant($job['jobId']);
		$home_array = array(
			"%JOB_HIDE%" => $job_hide,
			"%CATEGORY_NAME%" => $job['category_name'],
			"%SUBCAT_NAME%" => $job['subcategory_name'],
			"%TITLE%" => ucfirst($job['jobTitle']),
			"%BUDGET%" => $job['budget']."<span>".CURRENCY_SYMBOL."</span>",
			"%EXP_LEVEL%" => getJobExpLevel($job['expLevel']),
			"%EST_TIME%" => $job['estimatedDuration'],
			"%TOTAL_SAVED%" => $total_saved_jobs,
			"%FEATURED_HIDE%" => ($job['featured'] == 'n') ? 'hide':'',
			"%FEATURED_TAG_CLASS%" => ($job['featured'] == 'y' && $job['featuredPayment'] == 'y') ? '':'hide',
			"%DESC%"=> $job['description'],
       		"%USERNAME%" => $job['firstName'].' '.$job['lastName'],
       		"%USER_ID%" => $job['user_id'],
       		"%LOCATION%" => $job['location'],
       		"%CHAT_DIV%" => ($sessUserId==$job['user_id'])?"hide":"",
       		"%USER_IMAGE%" => $userImage,
       		"%JOINED_DATE%" => $date,
       		"%OPEN_JOBS%" => $openJobs,
       		"%REVIEW_LINK%" => $review_link,
       		"%LOGIN_CLASS%" => !empty($_SESSION["pickgeeks_userType"]) ? '' : 'not_login',
       		'%WARNING%' => $warning,
       		"%WALLET_AMOUNT%" => CURRENCY_SYMBOL.$walletAmount,	
       		"%TOTAL_AMOUNT%" => $totalSpentAmount,
       		"%JOB_STATUS%" => getJobStatus($job['jobStatus']),
       		"%POSTED_JOB%" => $postedJob,
			"%APPLICANTS%" => $no_of_applicant,
			"%SKILLS%" => $skills,
			"%COUNTRY%" => ($job['bidsFromLocation'] !='') ? get_country_list($job['bidsFromLocation']) : 'All Locations',
			"%REMAIN_DAYS%" => ($remain_days == 'Expired') ? $remain_days : $remain_days,
			"%FILES%" => $files,
			'%FILES_SHOW%' => $files == '' ? 'hide' : '',
			'%TIME_AGO%' => getTime($job['jobPostDate']),
			"%AVG_REVIEW%" => $review,
			"%SHOW_ESCROW%" => (ESCROW_MANDATORY=="yes")?"hide":"",
			"%IS_SHOW_ESCROW%" => ESCROW_MANDATORY,
			"%QUESTION%" => $que,
			"%JOB_ID%" => $job['jobId'],
			"%ALREADY_APPLIED%" => $already_applied,
			"%BID_STATUS%" => $bidHide['status'],
			"%HIRED_IN_STATUS%" => $hired_in_status,
			"%PLAN_CREDIT%" => $credit['credit'],
			"%BID_EXPIRED_STATUS%" => ($remain_days == "Expired") ? 'hide' :'',
			"%HIDE_SAVE_JOB%" => (empty($userId)) ? 'hide' : '',
			"%SAVE_JOB_CLASS%" => $save_job_class,
			"%LOGIN_HIDE%" => ($userId == 0) ? 'hide' : '',
			"%DISABLE_CLASS%" =>( $job['posterId'] == $this->sessUserId ) ? 'owner hide':'',
			"%JOB_SLUG%" => $job["jobSlug"],
			"%INVITATION%" => ($sessUserType=="Customer" && $job['jobType'] == 'pr') ? '' : 'hide',
			"%JOB_TYPE%" => $job["jobType"],
			"%BID_HIDE%" => ($sessUserType=="Customer" && $no_of_applicant > '0') ? '':'hide',
		);
       	$result = str_replace(array_keys($home_array),array_values($home_array),$profile);
		return $result;
	}

	public function getQuestion($id){

		$que_list = $this->db->pdoQuery("select * from tbl_question where id in(".$id.")")->results();

		$content = "";
		foreach ($que_list as $key => $value) {
			$files = new MainTemplater(DIR_TMPL . $this->module . "/job_que-sd.skd");
			$files = $files->compile();
			$array = array(
				"%ID%" => $key + 1,
				"%QUE%" => $value['question']
			);
			$content .= str_replace(array_keys($array), array_values($array),$files);
		}
		return $content;
	}

	public function getFiles($id)
	{
		$file_list = $this->db->select('tbl_job_files',array('*'),array('jobId'=>$id));
		$content = "";
		if($file_list->affectedRows() > 0){
			$data = $file_list->results();

			foreach ($data as $key => $value) {
				$files = new MainTemplater(DIR_TMPL . $this->module . "/job_files-sd.skd");
				$files = $files->compile();

				$array = array(
						"%ID%" => $key+1,
						"%FILE_NAME%" => $value['fileName'],
						"%FILE_LINK%" => SITE_JOB_FILES.$value['fileName'],
						'%FILE_IMAGE%' => getFileImage($value['fileName']),
				);
				$content .= str_replace(array_keys($array),array_values($array),$files);
			}
		} else {
			$content = "";
		}
		return $content;
	}

	public function submitProcedure($data){
		extract($data);
		$objPost->budget = isset($budget) ? filtering($budget) : '';
		$objPost->estDuration = isset($estDuration) ? filtering($estDuration) : '';
		$objPost->bidDesc = isset($bidDesc) ? filtering($bidDesc) : '';
		$objPost->jobId = $jobId;
		$objPost->userId = $this->sessUserId;
		$objPost->createdDate = date('Y-m-d H:i:s');
		if(ESCROW_MANDATORY=="yes"){
			$objPost->escrowRequired = "y";			
		}else{
			$objPost->escrowRequired = isset($escrowReq) ? $escrowReq : 'n';
		}		
		$answer = "";
		$cnt = count($ans);
		$seperator = "###";
		foreach ($ans as $key => $value) {
			if($cnt == $key){
				$seperator = "";
			}
			$answer .= $value.$seperator;
		}
		$objPost->answers = $answer;
		$objPostArray = (array)$objPost;
		$credit = getCredits($this->sessUserId);
		if($credit['credit'] > 0){
			$planId = $credit['planId'];

			$this->db->exec("Update tbl_user_plan set used_credit = used_credit + ".REQ_CREDIT_FOR_BID." where id = ".$planId);

			$insert = $this->db->insert('tbl_job_bids',$objPostArray);
			$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => YOUR_BID_PLACED_SUCCESSFULLY));
			$user_res  = getUser($objPost->userId,"firstName,lastName");
			$user_full_name = $user_res['firstName']." ".$user_res['lastName'];
			$job_res = $this->db->select("tbl_jobs",array("jobTitle","posterId"),array("id"=>$objPost->jobId))->result();
			$msg = $user_full_name.' has submitted proposal on your job - '.$job_res['jobTitle'];
			$link = SITE_URL.'c/my-jobs';
			notify('c',$job_res['posterId'],$msg,$link);
			$currentPage = $_SERVER['REQUEST_URI'];
			$pageArr = explode("/",$currentPage);
			$key = array_search('job',$pageArr);
			$pageNm = $pageArr[$key + 1];
			redirectPage(SITE_URL.'my-bids');
		} else {
			$msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => PLEASE_PURCHASE_PLAN));
			redirectPage(SITE_URL.'creditPlan');
		}

	}

	public function bidHide($id){
		$bided = $this->db->select('tbl_job_bids',array('*'),array('jobId'=>$id,"userId"=>$this->sessUserId))->affectedRows();

		if($bided >= 1){
			$data['status'] = "bidded";
		} else {
			$data['status'] = "";
		}
		return $data;
	}

	public function reportJob($data){

		extract($data);
		$jobData = $this->db->select('tbl_jobs',array('posterId'),array('id'=>$jobId))->result();
		$posterId = $jobData['posterId'];

        $report_check = $this->db->pdoQuery("select * from tbl_report where reportedId=? and reporterId=? and reportType=? and userId=?",array($jobId,$this->sessUserId,'Job',$posterId))->affectedRows();

        if($report_check > 0)
        {
            $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'err', 'var' => YOUR_HAVE_ALREADY_REPORTED_TO_THIS_JOB));
            redirectPage(SITE_URL."job/".$slug);
        }
        else
        {
		    $this->db->insert("tbl_report",array("reportedId"=>$jobId,"reportType"=>'Job',"userId"=>$posterId,"reporterId"=>$this->sessUserId,"reportMessage"=>$report_reason,"status"=>'Pen',"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));

            $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => YOUR_REPORT_HAS_BEEN_SENT_SUCCESSFULLY));

		$job = $this->db->pdoQuery("select j.*,j.id as jobId,c.".l_values('category_name')." as category_name ,s.".l_values('subcategory_name')." as subcategory_name,u.*,u.id as user_id,u.createdDate AS joinDate,jb.isHired,jb.userId
                from tbl_jobs as j
                LEFT JOIN tbl_category AS c ON j.jobCategory = c.id
                LEFT JOIN tbl_subcategory AS s ON j.jobSubCategory = s.id
                LEFT JOIN tbl_job_bids AS jb ON jb.jobid = j.id
                LEFT JOIN tbl_users As u ON u.id = j.posterId
                where j.jobSlug = '".$this->slug."' and j.isActive='y' and j.isDelete='n' and u.isActive='y' and u.isDeleted ='n' ")->result();
          
                        $msg =  $job['firstName']." ".$job['lastName']." ".'Your job report has been sent successfully';

                $nm =  $this->db->insert("tbl_notification",array("userId"=>0,"message"=>$msg,"isRead"=>'y',"notificationType"=>'a',"createdDate"=>date('Y-m-d H:i:s')))->showQuery();
            redirectPage(SITE_URL."job/".$this->slug);
        }
	}

	public function send_message($data)
    {
        extract($data);
        $this->db->insert("tbl_pmb",array("senderId"=>$this->sessUserId,"ReceiverId"=>$freelancerId,"message"=>$msg,"readStatus"=>'n',"createdDate"=>date('Y-m-d H:i:s'),"ipAddress"=>get_ip_address()));
        $msgType = $_SESSION["msgType"] = disMessage(array('type' => 'suc', 'var' => YOUR_MESSAGE_HAS_BEEN_SENT_SUCCESSFULLY));
        $senderDetail = getUser($this->sessUserId);
        $receiverDetail = getUser($freelancerId);
        /*site notification*/
        $senderNm = filtering(ucfirst($senderDetail['firstName']))." ".filtering(ucfirst($senderDetail['lastName']));
        $receiverNm = filtering(ucfirst($receiverDetail['firstName']))." ".filtering(ucfirst($receiverDetail['lastName']));
        $msg = "You have received new message from ".$senderNm;
        $detail_link = SITE_URL."pmb/".base64_encode($this->sessUserId);
        $this->db->insert("tbl_notification",array("userId"=>$freelancerId,"message"=>$msg,"detail_link"=>$detail_link,"isRead"=>'n',"notificationType"=>'f',"createdDate"=>date('Y-m-d H:i:s')));

        /*email notification*/
        if(notifyCheck('Notifymessage',$freelancerId)==1)
        {
              $arrayCont = array('USERNM'=>$receiverNm,'CUST_NM'=>$senderNm);
              $array = generateEmailTemplate('New_Message_From_Customer',$arrayCont);
              sendEmailAddress($receiverDetail['email'],$array['subject'],$array['message']);
        }
        redirectPage(SITE_URL."pmb/".base64_encode($freelancerId));
    }

}

?>
